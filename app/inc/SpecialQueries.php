<?php

require_once __DIR__ . "/include.php";
require_once __DIR__ . "/Connection.php";

/**
 * Parsing and executing special (mainly disposable) DB queries
 */
class SpecialQueries {


    private $conn;


    /**
     * Constructor; sets DB connection
     * @return void
     */
    public function __construct() {
        $conn = new Connection();
        $this->conn = $conn->get();
    }


    /**
     * Selects search results by specific DB column
     * @param string $operator "AND" or "OR" for select query
     * @param string $name Query for column "Lide.jmeno"
     * @param string $surname Query for column Lide."prijmeni"
     * @param mixed $company Query for column "Organizace.nazev", array or string
     * @return array
     */
    private function getMatches($operator, $name = "", $surname = "", $company) {
        if ($operator !== "AND" && $operator !== "OR") AppError::throw("Invalid operator");

        $name = "%".$name."%";
        $surname = "%".$surname."%";
        if (is_array($company)) $company = implode(" ", $company);
        if (!is_null($company)) $company = "%".$company."%";

        $query = $this->conn->prepare(
            "SELECT Lide.*, Organizace.nazev AS organizace_jmeno
            FROM Lide LEFT JOIN Organizace ON Lide.organizace = Organizace.id 
            WHERE Lide.jmeno COLLATE utf8_general_ci LIKE ?
            ${operator} Lide.prijmeni COLLATE utf8_general_ci LIKE ?"
            . ((!is_null($company)) ? " ${operator} Organizace.nazev COLLATE utf8_general_ci LIKE ?" : "")
        );

        if (!is_null($company)) $query->bind_param("sss", $name, $surname, $company);
        else $query->bind_param("ss", $name, $surname);

        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();

        $result_array = array();
        while ($row = $result->fetch_assoc()) array_push($result_array, $row);
        return $result_array;
    }


    /**
     * Returns list of search results (autocomplete posibilities)
     * @param string $value Field value (name or organization)
     * @param boolean $search Is autocomplete used in search?
     * @return array
     */
    public function autocompAdvanced($value = "", $search = false) {
        if (!$value) AppError::throw("Value param is missing");

        $values = explode(" ", trim(preg_replace("!\s+!", " ", $value)));
        $result = array();
        switch (count($values)) {
            case 1: 
                $result = array_merge($result, $this->getMatches("OR", $values[0], $values[0], $values[0])); 
                break;
            case 2: 
                $result = array_merge($result, $this->getMatches("AND", $values[0], $values[1], null));
                $result = array_merge($result, $this->getMatches("AND", null, $values[0], $values[1])); 
                $result = array_merge($result, $this->getMatches("AND", null, null, array($values[0], $values[1]))); 
                break;
            default:
                $result = array_merge($result, $this->getMatches("AND", $values[0], $values[1], array_slice($values, 2)));
                $result = array_merge($result, $this->getMatches("AND", null, $values[0], array_slice($values, 1)));
                $result = array_merge($result, $this->getMatches("AND", null, null, $values));
        }

        $result = array_unique($result, SORT_REGULAR);
        foreach ($result as $key => $row) {
            if (is_null($row["organizace"])) {
                unset($result[$key]["organizace"]);
                unset($result[$key]["organizace_jmeno"]);
            } else {
                if ($search) continue;
                unset($row["organizace"]);
                unset($row["organizace_jmeno"]);
                $result[] = $row;
            }
        }
        
        return $result;
    }


    /**
     * Returns list of event participants with their organizations
     * @param int $event_id ID of event
     * @param array $search If defined, method only returns participant lists with participants from this array
     * @return array
     */
    public function getEventParticipants($event_id = 0, $search = array()) {
        if (!$event_id || !is_int($event_id)) AppError::throw("Corrupted event_id param");
        foreach ($search as $value) if (!is_numeric($value)) AppError::throw("Corrupred member of search array");
        
        $query = $this->conn->prepare(
            "SELECT Lide.*, Interakce.organizace, Organizace.nazev as 'organizace_jmeno'
            FROM Interakce
            LEFT JOIN Lide ON Interakce.clovek = Lide.id
            LEFT JOIN Organizace ON Organizace.id = Interakce.organizace
            WHERE Interakce.akce = ?"
            . ((!empty($search)) ? " AND Lide.id IN (" . implode(",", $search) . ")" : "")
        );

        $query->bind_param("i", $event_id);

        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();

        $result_array = array();
        while ($row = $result->fetch_assoc()) array_push($result_array, $row);

        return (empty($search))
            ? $result_array
            : ((!empty($result_array))
                ? $this->getEventParticipants($event_id)
                : array());
    }


    /**
     * Sets event participants with their organizations
     * @param int $event_id ID of event
     * @param array $participants Array of arrays of event participants and their organizations
     * @return void
     */
    public function setEventParticipants($event_id = 0, $participants = array()) {
        if (!$event_id || !is_int($event_id)) AppError::throw("Corrupted event_id param");
        
        $this->deleteEventParticipants($event_id);

        foreach ($participants as $participant) {
            $query = $this->conn->prepare("INSERT into Interakce (clovek, organizace, akce) VALUES (?, ?, ?)");
            $org = $participant["organizace"] ?? NULL;

            if (!isset($participant["id"]) || !$participant["id"]) AppError::throw("Corrupted 'clovek' param");
            $query->bind_param("iii", $participant["id"], $org, $event_id);
            $query->execute();
            if ($query->error) AppError::throw("SQL query error - {$query->error}");
        }
    }


    /**
     * Removes all records of chosen event
     * @param int $event_id ID of event
     * @return void
     */
    public function deleteEventParticipants($event_id = 0) {
        if (!$event_id || !is_int($event_id)) AppError::throw("Corrupted event_id param");
        $query = $this->conn->prepare("DELETE from Interakce WHERE akce = ?");
        $query->bind_param("i", $event_id);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
    }


    /**
     * Returns history of selected person (donations, event participation)
     * @param int $id ID of person
     * @return array
     */
    public function getPersonHistory($id) {
        if (!$id || !is_int($id)) AppError::throw("Corrupted id param");

        $query = $this->conn->prepare(
            "SELECT Akce.*, AkceTyp.nazev as typ_akce, Interakce.organizace, Organizace.nazev as 'organizace_jmeno'
            FROM Interakce
            LEFT JOIN Organizace ON Organizace.id = Interakce.organizace
            LEFT JOIN Akce ON Akce.id = Interakce.akce
            JOIN AkceTyp ON AkceTyp.id = Akce.typ
            WHERE Interakce.clovek = ?
            ORDER BY Akce.datum DESC"
        );

        $query->bind_param("i", $id);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();
        $result_array_akce = array();
        while ($row = $result->fetch_assoc()) array_push($result_array_akce, $row);

        $query = $this->conn->prepare(
            "SELECT Dary.*, Organizace.nazev as 'organizace_jmeno'
            FROM Dary
            LEFT JOIN Organizace ON Organizace.id = Dary.organizace
            WHERE Dary.darce = ?
            ORDER BY Dary.datum DESC"
        );

        $query->bind_param("i", $id);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();
        $result_array_dary = array();
        while ($row = $result->fetch_assoc()) array_push($result_array_dary, $row);

        return array(
            "dary" => $result_array_dary,
            "akce" => $result_array_akce
        );
    }


    /**
     * Returns history of selected organisation (donations, event participation)
     * @param int $id ID of organisation
     * @return array
     */
    public function getOrgHistory($id) {
        if (!$id || !is_int($id)) AppError::throw("Corrupted id param");

        $query = $this->conn->prepare(
            "SELECT Akce.*, AkceTyp.nazev as typ_akce, Lide.jmeno, Lide.prijmeni
            FROM Interakce
            LEFT JOIN Lide ON Interakce.clovek = Lide.id
            LEFT JOIN Akce ON Akce.id = Interakce.akce
            JOIN AkceTyp ON AkceTyp.id = Akce.typ
            WHERE Interakce.organizace = ?
            ORDER BY Akce.datum DESC"
        );

        $query->bind_param("i", $id);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();
        $result_array_akce = array();
        while ($row = $result->fetch_assoc()) array_push($result_array_akce, $row);

        $query = $this->conn->prepare(
            "SELECT Dary.*, Lide.jmeno, Lide.prijmeni
            FROM Dary
            LEFT JOIN Lide ON Dary.darce = Lide.id
            WHERE Dary.organizace = ?
            ORDER BY Dary.datum DESC"
        );

        $query->bind_param("i", $id);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();
        $result_array_dary = array();
        while ($row = $result->fetch_assoc()) array_push($result_array_dary, $row);

        return array(
            "dary" => $result_array_dary,
            "akce" => $result_array_akce
        );
    }

}

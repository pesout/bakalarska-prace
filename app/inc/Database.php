<?php

require_once __DIR__ . "/include.php";
require_once __DIR__ . "/Connection.php";
require_once __DIR__ . "/SpecialQueries.php";

/**
 * Parsing and executing DB queries
 */
class Database {


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
     * Returns list of columns in specific table
     * @param string $table Table name
     * @return array
     */
    private function getColumns($table, $ignore_id = false) {       
        $columns = array();
        $query = $this->conn->prepare("SHOW COLUMNS FROM {$table}");
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($ignore_id && $row["Field"] === "id") continue;
            $columns[] = strtolower($row["Field"]);
        }
        return $columns;
    }


    /**
     * Checks if is table included in the list of allowed
     * @param string $table Table name
     * @return boolean
     */
    private function checkTableName($table) {
        return in_array($table, array(
            "Lide", 
            "Akce", 
            "Dary", 
            "Organizace",
            "AkceTyp"
        ));
    } 


    /**
     * Creates and executes DB select query. ($code ? " ({$code})" : "")
     * @param string $table Table name
     * @param array $params Array of columns and values to select
     * @param string $order Table column for ordering
     * @param string $o_dir Order direction {ASC|DESC} 
     * @return array
     */
    private function selectFromTable($table, $params, $order, $o_dir) {
        $columns = $this->getColumns($table);
        $values = array();
        $participants_input = array();
        if (isset($params["ucastnici"]) && $params["ucastnici"]["value"]) {
            $participants_input = json_decode($params["ucastnici"]["value"], true);
            unset($params["ucastnici"]);
        }
        $query = "SELECT * FROM {$table}" . ((empty($params)) ? "" : " WHERE ");
        $types = "";
        
        foreach ($params as $key => $item) {
            $key = strtolower($key);
    
            if (!isset($item["value"])) continue;
            $value = strtolower($item["value"]);
            
            if (!in_array($key, $columns)) {
                AppError::throw("SQL query error - corrupted param(s)");
            }

            $query .= $key . " COLLATE utf8_general_ci LIKE ? AND ";
            $types .= "s";

            $exact = $item["exact"] ?? false;
            array_push($values, ($exact == "true") ? $value : "%".$value."%");
        }       

        $query = empty($params) ? $query : substr($query, 0, -4);

        $values_refs = array();
        foreach($values as $key => $value) $values_refs[$key] = &$values[$key];

        if ($order && !in_array(strtolower($order), $columns)) {
            AppError::throw("SQL query error - corrupted \"order\" param");
        } elseif ($order != "") {
            $query .= " ORDER BY {$order} " . (($o_dir === "DESC") ? "DESC" : "ASC");
        }
        
        $query = $this->conn->prepare($query);

        array_unshift($values_refs, $types);
        if (!empty($params)) call_user_func_array(array($query, 'bind_param'), $values_refs);

        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $result = $query->get_result();

        $result_array = array();
        while ($row = $result->fetch_assoc()) {

            if (isset($row["organizace"]) && $row["organizace"]) {
                $row["organizace_jmeno"] = $this->select("Organizace", array("id" => array("value" => $row["organizace"], "exact" => true)))[0]["nazev"];
            }

            if (isset($row["typ"]) && $row["typ"]) {
                $row["typ_jmeno"] = $this->select("AkceTyp", array("id" => array("value" => $row["typ"], "exact" => true)))[0]["nazev"];
            }
            
            if (isset($row["darce"]) && $row["darce"]) {
                $row_found = $this->select("Lide", array("id" => array("value" => $row["darce"], "exact" => true)))[0];
                $row["darce_jmeno"] = "{$row_found['jmeno']} {$row_found['prijmeni']}";
            }

            if ($table === "Akce") {
                $sq = new SpecialQueries;
                $participants = $sq->getEventParticipants($row["id"], $participants_input);
                if (empty($participants_input) || !empty($participants)) $row["ucastnici"] = json_encode($participants);
                else continue;
            }
            

            array_push($result_array, $row);
        }

        return $result_array;
    }


    /**
     * Creates and executes DB insert query
     * @param string $table Table name
     * @param array $params Array of columns and values to insert
     * @return array
     */
    private function createRecord($table, $params) {
        $columns = $this->getColumns($table, true);
        $query = "INSERT INTO {$table} (";
        $types = "";
        $to_bind = " VALUES (";
        $values = array();
        $participants_input = array();
        if (isset($params["ucastnici"]) && $params["ucastnici"]) {
            $participants_input = json_decode($params["ucastnici"], true);
            unset($params["ucastnici"]);
        }

        $params["zadal"] = $_SESSION["user"];
        $params["editoval"] = $_SESSION["user"];
        $params["dat_editace"] = date('Y-m-d H:i:s');

        if (isset($params["organizace"]) && $params["organizace"]) {
            $this->select("Organizace", array("id" => array("value" => $params["organizace"], "exact" => true)))[0]["nazev"] 
                ?? AppError::throw("SQL query error - corrupted \"organizace\" param");
        }

        if (isset($params["darce"]) && $params["darce"]) {
            $this->select("Lide", array("id" => array("value" => $params["darce"], "exact" => true)))[0]["prijmeni"] 
                ?? AppError::throw("SQL query error - corrupted \"darce\" param");
        }

        foreach ($params as $key => $value) {
            $key = strtolower($key);
            if (!in_array($key, $columns)) {
                AppError::throw("SQL query error - corrupted param(s)");
            }          
            if (!$value && $key == "organizace") $value = NULL;
            if ($key == "jmeno" || $key == "prijmeni") $value = ucfirst($value);

            $query .= "{$key},";
            $types .= "s";
            $to_bind .= "?,";

            array_push($values, $value);
        }

        $query = substr($query, 0, -1) . ")" . substr($to_bind, 0, -1) . ")";
        $query = $this->conn->prepare($query);

        $values_refs = array();
        foreach($values as $key => $value) $values_refs[$key] = &$values[$key];

        array_unshift($values_refs, $types);
        call_user_func_array(array($query, 'bind_param'), $values_refs); 

        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");

        $id = $query->insert_id;

        if ($table === "Akce") {
            $sq = new SpecialQueries;
            $sq->setEventParticipants($id, $participants_input);
        }

        return $this->selectFromTable($table, array("id" => array("value" => $id)), "", "");
    }

    
    /**
     * Creates and executes DB update query
     * @param string $table Table name
     * @param array $params Array of columns and values to update
     * @param string $id ID of record to update
     * @return array
     */
    private function editRecord($table, $params, $id) {
        if (!$id) AppError::throw("SQL query error - corrupted \"id\" param");
        $columns = $this->getColumns($table, true);
        $query = "UPDATE {$table} SET ";
        $types = "";
        $values = array();
        $output = array();
        $participants_input = array();
        if (isset($params["ucastnici"]) && $params["ucastnici"]) {
            $participants_input = json_decode($params["ucastnici"], true);
            unset($params["ucastnici"]);
        }

        $params["editoval"] = $_SESSION["user"];
        $params["dat_editace"] = date('Y-m-d H:i:s');

        if (isset($params["organizace"]) && $params["organizace"] === "undefined") unset($params["organizace"]);
        if (isset($params["darce"]) && $params["darce"] === "undefined") unset($params["darce"]);
        if (isset($params["typ"]) && $params["typ"] === "undefined") unset($params["typ"]);

        if (isset($params["organizace"]) && $params["organizace"]) {
        $output["organizace_jmeno"] = 
            $this->select("Organizace", array("id" => array("value" => $params["organizace"], "exact" => true)))[0]["nazev"] 
                ?? AppError::throw("SQL query error - corrupted \"organizace\" param");
        }

        if (isset($params["darce"]) && $params["darce"]) {
            $row_found = $this->select("Lide", array("id" => array("value" => $params["darce"], "exact" => true)))[0]
                ?? AppError::throw("SQL query error - corrupted \"darce\" param");
            $output["darce_jmeno"] = "{$row_found['jmeno']} {$row_found['prijmeni']}";
        }

        if (isset($params["typ"]) && $params["typ"]) {
            $output["typ_jmeno"] = 
                $this->select("AkceTyp", array("id" => array("value" => $params["typ"], "exact" => true)))[0]["nazev"] 
                    ?? AppError::throw("SQL query error - corrupted \"typ\" param");
        }

        foreach ($params as $key => $value) {
            $key = strtolower($key);
            if (!in_array($key, $columns)) {
                AppError::throw("SQL query error - corrupted param(s)");
            }          
            if (!$value && $key == "organizace") $value = NULL;
            if ($key == "jmeno" || $key == "prijmeni") $value = ucfirst($value);
            if ($key == "zadal") AppError::throw("SQL query error - \"zadal\" cannot be modified");
 
            $query .= $key . " = ?,";
            $types .= "s";

            array_push($values, $value);
            $output[$key] = $value;
        }

        $query = substr($query, 0, -1) . " WHERE id = ?";
        $types .= "i";
        array_push($values, $id);

        $query = $this->conn->prepare($query);
        
        $values_refs = array();
        foreach($values as $key => $value) $values_refs[$key] = &$values[$key];

        array_unshift($values_refs, $types);
        call_user_func_array(array($query, 'bind_param'), $values_refs); 

        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");

        if ($table === "Akce") {
            $sq = new SpecialQueries;
            $sq->setEventParticipants((int)$id, $participants_input);
            $participants = $sq->getEventParticipants((int)$id);
            if (!empty($participants)) $output["ucastnici"] = json_encode($participants);
        }

        return $output;
    }


    /**
     * Creates and executes DB delete query
     * @param string $table Table name
     * @param string $id ID of record to delete
     * @return void
     */
    private function deleteRecord($table, $id) {
        if (!$id) AppError::throw("SQL query error - corrupted \"id\" param");
        $query = "DELETE FROM {$table} WHERE id = ?";
        $query = $this->conn->prepare($query);
        $query->bind_param("i", $id);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        
        switch ($table) {
            case "Akce":
                $sq = new SpecialQueries;
                $sq->deleteEventParticipants((int)$id);
                break;
            case "Lide":
                $query = "DELETE FROM Dary WHERE darce = ?";
                $query = $this->conn->prepare($query);
                $query->bind_param("i", $id);
                $query->execute();
                if ($query->error) AppError::throw("SQL query error - {$query->error}");

                $query = "DELETE FROM Interakce WHERE clovek = ?";
                $query = $this->conn->prepare($query);
                $query->bind_param("i", $id);
                $query->execute();
                if ($query->error) AppError::throw("SQL query error - {$query->error}");

                break;
            case "Organizace":
                $query = "DELETE FROM Dary WHERE organizace = ?";
                $query = $this->conn->prepare($query);
                $query->bind_param("i", $id);
                $query->execute();
                if ($query->error) AppError::throw("SQL query error - {$query->error}");

                $query = "DELETE FROM Interakce WHERE organizace = ?";
                $query = $this->conn->prepare($query);
                $query->bind_param("i", $id);
                $query->execute();
                if ($query->error) AppError::throw("SQL query error - {$query->error}");

                break;
        }        
    }


    /**
     * Removes specific value from selected table column
     * @param string $table Table name
     * @param string $column Column name
     * @param string $value Value to remove
     * @return void
     */
    private function removeValue($table, $column, $value) {
        if (!$value) AppError::throw("SQL query error - corrupted \"id\" param");

        $columns = $this->getColumns($table, true);
        if (!in_array(strtolower($column), $columns)) {
            AppError::throw("SQL query error - corrupted \"column\" param");
        }
        $query = "UPDATE {$table} SET {$column}=NULL WHERE {$column}=?";
        $query = $this->conn->prepare($query);
        $query->bind_param("s", $value);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
    }


    /**
     * Executes DB select query for specified table, user-callable function
     * @param string $table Table name
     * @param array $params Array of columns and values to select
     * @param string $order Table column for ordering
     * @param string $o_dir Order direction {ASC|DESC} 
     * @return array
     */
    public function select($table = "", $params = array(), $order = "", $o_dir = "ASC") {
        if (!$this->checkTableName($table)) AppError::throw("Unautorized request or table do not exist");
        return $this->selectFromTable($table, $params, $order, $o_dir);
    }


    /**
     * Executes DB insert query for specified table, user-callable function
     * @param string $table Table name
     * @param array $params Array of columns and values to insert
     * @return void
     */
    public function create($table = "", $params = array()) {
        if (!$this->checkTableName($table)) AppError::throw("Unautorized request or table do not exist");
        return $this->createRecord($table, $params);
    }


    /**
     * Executes DB update query for specified table, user-callable function
     * @param string $table Table name
     * @param array $params Array of columns and values to update
     * @param string $id ID of record to update
     * @return void
     */
    public function edit($table = "", $params = array(), $id = "") {
        if (!$this->checkTableName($table)) AppError::throw("Unautorized request or table do not exist");
        return $this->editRecord($table, $params, $id); 
    }


    /**
     * Executes DB delete query for specified table, user-callable function
     * @param string $table Table name
     * @param string $id ID of record to delete
     * @return void
     */
    public function delete($table="", $id = "") {
        if (!$this->checkTableName($table)) AppError::throw("Unautorized request or table do not exist");
        $this->deleteRecord($table, $id);
    }


    /**
     * Executse DB query to remove specific value from selected table column, user-callable function
     * @param string $table Table name
     * @param string $column Column name
     * @param string $value Value to remove
     * @return void
     */
    public function remove($table = "", $column = "", $value = "") {
        if (!$this->checkTableName($table)) AppError::throw("Unautorized request or table do not exist");
        $this->removeValue($table, $column, $value);
    }


    /**
     * Destructor; closes DB connection
     * @return void
     */
    public function __destruct() {
        $this->conn->close();
    }

} 

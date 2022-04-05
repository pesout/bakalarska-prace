<?php

require_once __DIR__ . "/include.php";
require_once __DIR__ . "/Database.php";


/**
 * Queue operations
 */
class Queue {


    private $db;
    

    /**
     * Constructor; sets Database class instance, prepares session
     * @return void
     */
    public function __construct() {
        $this->db = new Database();
        if (!isset($_SESSION["queue"])) $_SESSION["queue"] = array();
        if (!isset($_SESSION["queue_ids"])) $_SESSION["queue_ids"] = array();
    }


    /**
     * Adds item into queue, returns array containing whole record from DB
     * @param array $params : Array of records to add
     * @return array
     */
    public function add($params = array()) {
        foreach ($params as $row) {
            if (!$row[0]) AppError::throw("Corrupted \"id\" param");

            if (in_array($row[0], $_SESSION["queue_ids"])) continue;
            $_SESSION["queue_ids"][] = $row[0];
    
            $result = $this->db->select("Lide", array("id" => array("value" => $row[0])))[0] ?? false; 
            if (!$result) AppError::throw("Corrupted \"id\" param");
            $result["as_org"] = $row[1];

            $_SESSION["queue"][] = $result;
        }
        return $_SESSION["queue"];
    }


    /**
     * Removes item from queue
     * @param $id : ID of item to remove
     * @return void
     */
    public function remove($id) {
        if (!$id) AppError::throw("Corrupted \"id\" param");
        foreach ($_SESSION["queue"] as $key => $array) {
            if ($array["id"] == $id) {
                unset($_SESSION["queue"][$key]);
                $_SESSION["queue_ids"] = array_diff($_SESSION["queue_ids"], [$id]);
                return $_SESSION["queue"];
            }
        }
        AppError::throw("Not found, unable to remove");
    }


    /**
     * Returns whole queue
     * @return array
     */
    public function get() {
        return $_SESSION["queue"] ?? array();
    }


    /**
     * Clears whole queue
     * @return void
     */
    public function clear() {
        $_SESSION["queue"] = array();
        $_SESSION["queue_ids"] = array();
    }
}
<?php

require_once __DIR__ . "/CONFIG.php";

/**
 * DB connection handling
 */
class Connection {


    private $conn;

    
    /**
     * Constructor; creates DB connection
     * @return void
     */
    public function __construct() {
        $this->conn = new mysqli(DB["server"], DB["user"], DB["password"], DB["db_name"]);
        if ($this->conn->connect_error) {
            AppError::throw("Unable to reach database; {$this->conn->connect_error}");
        }
        $this->conn->set_charset("utf8");
    }


    /**
     * Returns DB connection
     * @return resource
     */
    public function get() {
        return $this->conn;
    }


    /**
     * Closes DB connection
     * @return void
     */
    public function close() {
        $this->conn->close();
    }
}

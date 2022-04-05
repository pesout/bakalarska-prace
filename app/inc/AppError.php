<?php

require_once __DIR__ . "/CONFIG.php";

@session_start();

/**
 * Error handling
 */
class AppError {


    /**
     * Throws new error
     * @param string $message Error message
     * @param integer $code Error code
     * @return void
     */
    public static function throw($message = "Unknown", $code = 400) {
        if ($code) {
            http_response_code($code);
            $_SESSION["error"] = $code;
        }
        if (DEVELOPER) echo "Error: " . $message;
        else header("Location: " . HOME . "error.php");
        die();
    }


    /**
     * Prints variable and optionally terminates script
     * @param mixed $var Variable to print
     * @param boolean $exit Defines if exit/die after print 
     * @return void
     */
    public static function debug($var = "Undefined", $exit = true) {
        echo "<pre>";
        print_r($var);
        if ($exit) die();
    }
}

<?php

require_once __DIR__ . "/CONFIG.php";
require_once __DIR__ . "/Connection.php";


/**
 * User login and registration
 */
class Login {


    /**
     * Checks user and password and sets login session, returns status
     * @param string $user username
     * @param string $password password to check
     * @return array
     */
    public static function loginUser($user = "", $password = "") {
        $conn = new Connection();
        $user = htmlspecialchars($user, ENT_QUOTES);
        $password = htmlspecialchars($password, ENT_QUOTES);
        if ($user !== preg_replace("/[^a-zA-Z0-9]/", "", $user)) return array("status" => "unallowed_username_chars");

        $query = $conn->get()->prepare("SELECT * FROM SysUsers WHERE username = ?");
        $query->bind_param("s", $user);
        $query->execute();
        if ($query->error) AppError::throw("SQL query error - {$query->error}");
        $hash = $query->get_result()->fetch_assoc()["pass_hash"] ?? false;

        if ($hash === false) return array("status" => "user_not_exist");
        if (password_verify($password, $hash)) {
            $_SESSION["user"] = $user;
            return array("status" => "ok");
        }
        else return array("status" => "incorrect_password");
    }


    /**
     * Returns logged user or false
     * @return string
     */
    public static function checkLogin() {
        return $_SESSION["user"] ?? false;
    }


    /**
     * Logouts user
     * @return string
     */
    public static function logoutUser() {
        unset($_SESSION["user"]); 
    }


    /**
     * User registration, returns status
     * @param string $user new username
     * @param string $password new password
     * @param string $code predefined code, security feature
     * @return array
     */
    public static function registerUser($user = "", $password = "", $code = "") {
        $conn = new Connection();
        $user = htmlspecialchars($user, ENT_QUOTES);
        $password = htmlspecialchars($password, ENT_QUOTES);
        if ($code != REG_CODE) return array("status" => "incorrect_code");
        if (strlen($user) < 3) return array("status" => "short_username");
        if (strlen($password) < 8) return array("status" => "short_password");
        if ($user !== preg_replace("/[^a-zA-Z0-9]/", "", $user)) return array("status" => "unallowed_username_chars");
        $pass_hash = password_hash($password, PASSWORD_DEFAULT, array("cost" => BCRYPT_COST));
        
        $query = $conn->get()->prepare("INSERT INTO SysUsers (username, pass_hash) VALUES (?,?)");
        $query->bind_param("ss", $user, $pass_hash);
        $query->execute();
        if ($query->error) {
            if (strpos(strtolower($query->error), "duplicate") !== false) return array("status" => "user_exists");
            AppError::throw("SQL query error - {$query->error}");
        }
        $_SESSION["user"] = $user;
        return array("status" => "ok");
    }
}

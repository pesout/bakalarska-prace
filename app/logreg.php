<?php

require_once __DIR__ . "/inc/CONFIG.php";
require_once __DIR__ . "/inc/Login.php";
require_once __DIR__ . "/inc/AppError.php";

$action = $_GET["action"] ?? "";

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";
$code = $_POST["code"] ?? "";

switch ($action) {
    case "login":
        if (!$username || !$password) AppError::throw("Corrupted login params");
        echo json_encode(Login::loginUser($username, $password));
        die();
    case "registration": 
        if (!$username || !$password || !$code) AppError::throw("Corrupted registration params");
        echo json_encode(Login::registerUser($username, $password, $code));
        die();
    case "logout":
        echo json_encode(Login::logoutUser());
        die();
    default: 
        AppError::throw("Invalid action");
}

AppError::throw();

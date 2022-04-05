<?php

@session_start();

require_once __DIR__ . "/CONFIG.php";
require_once __DIR__ . "/AppError.php";
require_once __DIR__ . "/Login.php";

header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$checklog = $_SESSION["checklog"] ?? true;
unset($_SESSION["checklog"]);

if ($checklog && !Login::checkLogin()) {
    header("Location: " . HOME . "?page=prihlaseni");
    die();
}




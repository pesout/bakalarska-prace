<?php

require_once __DIR__ . "/inc/include.php";
require_once __DIR__ . "/inc/SpecialQueries.php";

$table = $_GET["table"] ?? "";
$id = $_GET["id"] ?? "";

if (!is_numeric($id)) AppError::throw("Invalid ID param");

$sq = new SpecialQueries();

switch ($table) {
    case "Lide": 
        echo json_encode($sq->getPersonHistory((int)$id));
        die();
    case "Organizace": 
        echo json_encode($sq->getOrgHistory((int)$id));
        die();
    default: 
        AppError::throw("Invalid table name");
}

AppError::throw();

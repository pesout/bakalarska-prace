<?php

require_once __DIR__ . "/inc/include.php";
require_once __DIR__ . "/inc/Queue.php";

$action = $_GET["action"] ?? "";
$id = $_POST["id"] ?? "";
$params = $_POST["params"] ?? array();

$q = new Queue();

switch ($action) {
    case "add": 
        if (empty($params)) AppError::throw("Params array is not defined");
        echo json_encode($q->add(json_decode($params)));
        die();
    case "remove":
        if (!$id) AppError::throw("ID is not defined");
        echo json_encode($q->remove($id));
        die();
    case "get":
        echo json_encode($q->get());
        die();
    case "clear":
        $q->clear();
        echo json_encode(array("state" => "success"));
        die();
    default: 
        AppError::throw("Param \"action\" is invalid");
}

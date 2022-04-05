<?php

require_once __DIR__ . "/inc/include.php";
require_once __DIR__ . "/inc/Database.php";
require_once __DIR__ . "/inc/SpecialQueries.php";

$action = $_GET["action"] ?? "";
$table = $_GET["table"] ?? "";
$search = $_GET["search"] ?? false;

$input = $_POST ?? array(); 

$db = new Database();
$spq = new SpecialQueries();

switch($action) {
    case "autocomplete":
        if (empty($input) || !isset($input["value"])) AppError::throw("Value param is missing");
        echo json_encode(
            ($table == "Organizace" || $table == "AkceTyp") 
                ? $db->select($table, array("nazev" => array("value" => $input["value"], "exact" => false)))
                : $spq->autocompAdvanced($input["value"], $search)
        );
        break;

    case "select":
        if (empty($input)) AppError::throw("POST is not defined");
        $query = array();
        foreach ($input as $key => $value) {
            if (substr($key, -4) === "name") {
                $column = substr($key, 0, -5);

                $content = $input[$column."_content"] ?? "";
                $boolean = $input[$column."_boolean"] ?? "";

                if ($input[$key] === "ucastnici") {
                    if (!isset($input["ucastnici"]) || !$input["ucastnici"]) AppError::throw("Corrupred 'ucastnici' param");
                    $query["ucastnici"] = array("value" => $input["ucastnici"]);
                    continue;
                }

                $query[$input[$key]] = array(
                    "value" => (!$content) ? $boolean : $content,
                    "exact" => $input[$column."_exact"] ?? false
                );
            }
        }
        echo json_encode(
            $db->select(
                $table,
                $query, 
                $input["order"] ?? "", 
                $input["order_direction"] ?? ""
            )
        );
        break;

    case "create":
        if (empty($input)) AppError::throw("Cannot create empty row");
        echo json_encode(
            $db->create($table, $input)
        );
        break;

    case "edit":
        $row = substr($input["row"] ?? AppError::throw("ID param is missing"), 3);
        unset($input["row"]);
        if (empty($input)) AppError::throw("Cannot edit row without params");
        echo json_encode(
            $db->edit($table, $input, $row)
        );
        break;

    case "delete": 
        if (empty($input) || !isset($input["row"])) AppError::throw("ID param is missing");
        $db->delete($table, substr($input["row"], 3));
        if ($table === "Organizace") $db->remove("Lide", "organizace", substr($input["row"], 3));
        echo json_encode(
            array("state" => "success")
        );
        break;
    default:
        AppError::throw("GET params not correct");
}

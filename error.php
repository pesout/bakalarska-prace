<?php

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/app/inc/CONFIG.php";


use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . "/templates");
$twig = new Environment($loader);

@session_start();

$status = $_SESSION["error"] ?? $_SERVER["REDIRECT_STATUS"] ?? http_response_code();
if ($status == 200) $status = 403;

http_response_code($status);

echo $twig->render("error.twig", array(
    "error" => $status,
    "message" => ($status == 404) ? "Stránka nenalezena" : "Neočekávaná chyba"
));

<?php

@session_start();
$_SESSION["checklog"] = false;

require_once __DIR__ . "/app/inc/include.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/app/inc/CONFIG.php";

require_once __DIR__ . "/app/pages/Lide.php";
require_once __DIR__ . "/app/pages/Organizace.php";
require_once __DIR__ . "/app/pages/Dary.php";
require_once __DIR__ . "/app/pages/Akce.php";
require_once __DIR__ . "/app/pages/AkceTyp.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . "/templates");
$twig = new Environment($loader);

$current_page = $_GET["page"] ?? "";
$redirect = false;

if (Login::checkLogin()) {
    switch($current_page) {
        case "lide":
            $page = new Lide(); break;
        case "organizace":
            $page = new Organizace(); break;
        case "dary":
            $page = new Dary(); break;
        case "akce":
            $page = new Akce(); break;
        case "typy-akci":
            $page = new AkceTyp(); break;
        case "":
        case "registrace":
        case "prihlaseni":
            $redirect = "lide"; break;
        default:
            AppError::throw("Page not found", 404);
    }
    if (!$redirect) {
        echo $twig->render("main.twig", $page->getData());
        die();
    }

} else {
    switch($current_page) {
        case "registrace":
            $pagedata = array("page_id" => "registrace", "pagename" => "Registrace");
            break;
        case "prihlaseni":
            $pagedata = array("page_id" => "prihlaseni", "pagename" => "Přihlášení");
            break;
        case "":
        case "lide":
        case "organizace":
        case "dary":
        case "akce":
        case "typy-akci":
            $redirect = "prihlaseni"; break;
        default: AppError::throw("Page not found", 404);
    }
    if (!$redirect) {
        echo $twig->render("logreg.twig", $pagedata);
        die();
    }
}

header("Location: " . HOME . "?page=" . $redirect);

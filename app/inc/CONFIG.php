<?php

/********************************/
/*** THIS IS MAIN CONFIG FILE ***/
/********************************/

// DB credentials
define("DB", array(
    "server"   => "",
    "db_name"  => "",
    "user"     => "",
    "password" => ""
));

// Home URL
define("HOME", "");

// Registration code
define("REG_CODE", "");

// Developer mode
define("DEVELOPER", false);

// Password hash hardware cost
define("BCRYPT_COST", 12);

// Error reporting (do not change)
if (DEVELOPER) {
    ini_set("display_errors", "1");
    ini_set("display_startup_errors", "1");
    error_reporting(E_ALL);
    mysqli_report(MYSQLI_REPORT_STRICT);
} else {
    error_reporting(0);
    @ini_set("display_errors", "0");
    @ini_set("display_startup_errors", "0");
    mysqli_report(MYSQLI_REPORT_OFF);
}

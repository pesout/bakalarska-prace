<?php

require_once __DIR__ . "/../inc/include.php";


/**
 * General methods for static page content
 */
class Page {
    

    /**
     * Returns form input patterns
     * @return array
     */
    protected function getPatterns() {
        return array(
            "str" => ".*[A-Za-z].*",
            "tel" => "[0-9]{9}|[0-9]{3} [0-9]{3} [0-9]{3}",
            "addr" => ".*[A-Za-z].*[0-9]",
            "que" => ".*"
        );
    }

    /**
     * Returns menu items
     * @return array
     */
    protected function getMenu() {
        return array(
            "lide" => array("name" => "Lidé", "icon" => "fas fa-users"),
            "organizace" => array("name" => "Organizace", "icon" => "fas fa-building"),
            "dary" => array("name" => "Dary", "icon" => "fas fa-gift"),
            "akce" => array("name" => "Akce", "icon" => "far fa-calendar-alt")
        );
    }
}
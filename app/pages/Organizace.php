<?php

require_once __DIR__ . "/../inc/include.php";
require_once __DIR__ . "/Page.php";


class Organizace extends Page {


    /**
     * Returns list of columns with parameters
     * @return array
     */
    private function getColumns() {
        $p = $this->getPatterns();
        return array(
            array(
                "value" => "nazev", "name" => "Název",
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "text", "required" => true, "pattern" => $p["str"], "maxlength" => 50, "flex" => 1, "autocomp" => false
            ),
            array(
                "value" => "ulice_cislo", "name" => "Ulice a č. p.", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "text", "required" => false, "pattern" => $p["addr"], "maxlength" => 50, "flex" => 1, "autocomp" => false
            ),  
            array(
                "value" => "mesto", "name" => "Město", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "text", "required" => false, "pattern" => $p["str"], "maxlength" => 30, "flex" => 2, "autocomp" => false
            ),   
            array(
                "value" => "zadal", "name" => "Zadal", 
                "checkbox" => true, "checked" => false, "search" => true, "sort" => true, "editable" => false, 
                "type" => "", "required" => false, "pattern" => "", "maxlength" => null, "flex" => 0, "autocomp" => false
            ),   
            array(
                "value" => "editoval", "name" => "Editoval", 
                "checkbox" => true, "checked" => false, "search" => true, "sort" => true, "editable" => false, 
                "type" => "", "required" => false, "pattern" => "", "maxlength" => null, "flex" => 0, "autocomp" => false
            ),   
            array(
                "value" => "dat_editace", "name" => "Datum editace",
                "checkbox" => true, "checked" => false, "search" => false, "sort" => true, "editable" => false, 
                "type" => "", "required" => false, "pattern" => "", "maxlength" => null, "flex" => 0, "autocomp" => false
            ),
            array(
                "value" => "poznamka", "name" => "Poznámka",
                "checkbox" => false, "checked" => false, "search" => false, "sort" => false, "editable" => true, 
                "type" => "text", "required" => false, "pattern" => "", "maxlength" => null, "flex" => 0, "autocomp" => false
            )
        );
    }


     /**
     * Returns list of history columns with parameters
     * @return array
     */
    private function getHistoryColumns() {
        return array(
            "dary" => array(
                array("value" => "datum", "name" => "Datum"),
                array("value" => "castka", "name" => "Částka"),
                array("value" => "ucel", "name" => "Účel"),
                array("value" => "jmeno", "name" => "Jméno"),
                array("value" => "prijmeni", "name" => "Příjmení")
            ),
            "akce" => array(
                array("value" => "datum", "name" => "Datum"),
                array("value" => "nazev", "name" => "Název"),
                array("value" => "typ_akce", "name" => "Typ"),
                array("value" => "jmeno", "name" => "Jméno"),
                array("value" => "prijmeni", "name" => "Příjmení")
            )
        );
    }


    /**
     * Returns all page data
     * @return array
     */
    public function getData() {
        return array(
            "columns" => $this->getColumns(),
            "menu" => $this->getMenu(),
            "history_columns" => $this->getHistoryColumns(),
            "user" => Login::checkLogin(),
            "pagename" => "Organizace",
            "page_id" => "Organizace"
        );
    }
}
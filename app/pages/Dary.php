<?php

require_once __DIR__ . "/../inc/include.php";
require_once __DIR__ . "/Page.php";


class Dary extends Page {


    /**
     * Returns list of columns with parameters
     * @return array
     */
    private function getColumns() {
        $p = $this->getPatterns();
        return array( 
            array(
                "value" => "datum", "name" => "Datum",
                "checkbox" => true, "checked" => true, "search" => false, "sort" => true, "editable" => true, 
                "type" => "date", "required" => true, "pattern" => "", "maxlength" => null, "flex" => 1, "autocomp" => false
            ),   
            array(
                "value" => "castka", "name" => "Částka v Kč", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "number", "required" => true, "pattern" => "", "maxlength" => 9, "flex" => 1, "autocomp" => false
            ),
            array(
                "value" => "ucel", "name" => "Účel", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "text", "required" => false, "pattern" => $p["str"], "maxlength" => 50, "flex" => 1, "autocomp" => false
            ),
            array(
                "value" => "darce", "name" => "Dárce", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "text", "required" => true, "pattern" => $p["str"], "maxlength" => 50, "flex" => 2, "autocomp" => true
            ),
            array(
                "value" => "organizace", "name" => "Organizace", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => false, 
                "type" => "text", "required" => false, "pattern" => $p["str"], "maxlength" => 50, "flex" => 0, "autocomp" => false
            ), 
            array(
                "value" => "hmotny", "name" => "Hmotný", 
                "checkbox" => true, "checked" => true, "search" => true, "sort" => true, "editable" => true, 
                "type" => "boolean", "required" => false, "pattern" => "", "maxlength" => null, "flex" => 2, "autocomp" => false
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
     * Returns all page data
     * @return array
     */
    public function getData() {
        return array(
            "columns" => $this->getColumns(),
            "menu" => $this->getMenu(),
            "user" => Login::checkLogin(),
            "pagename" => "Dary",
            "page_id" => "Dary"
        );
    }
}
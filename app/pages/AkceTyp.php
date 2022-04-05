<?php

require_once __DIR__ . "/../inc/include.php";
require_once __DIR__ . "/Page.php";


class AkceTyp extends Page {


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
            "pagename" => "Typy akcí",
            "page_id" => "AkceTyp"
        );
    }
}
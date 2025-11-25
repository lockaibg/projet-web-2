<?php
    function convert_to_underscore($text) {
        $text_modif = str_replace(" ", "_", $text);
        return $text_modif;
    }
    function convert_to_space($text) {
        $text_modif = str_replace("_", " ", $text);
        if(substr($text_modif, -1) === ' ') {
            $text_modif = substr($text_modif, 0, -1);
        }
        return $text_modif;
    }
?>
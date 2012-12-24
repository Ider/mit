<?php

class Util {

    /**
     * Join associated array as css style: combine key and value with colon,
     * concatenate pairs with semicolon.
     * @param  dictionary  $pieces css properties and values
     * @return String      style string
     */
    public static function cssjoin($pieces) {
        if (!is_array($pieces)) return $pieces;

        $result = ''; 
        foreach ($pieces as $key => $value)
            $result .= "$key: $value; ";
        
        return $result;
    }
}
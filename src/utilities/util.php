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

class DateUtil {
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';

    public static function datetimeNow() {
        return date(self::DATETIME_FORMAT);
    }

    public static function dateNow() {
        return date(self::DATE_FORMAT);
    }

    public static function timeNow() {
        return date(self::TIME_FORMAT);
    }

    public static function formatDate($dateStr) {
        try {
            $date = new DateTime($dateStr);
        } catch (Exception $e) {
            $date = new DateTime();
        }

        return $date->format(self::DATE_FORMAT);
    }
}
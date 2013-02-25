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

class EncodingUtil {
    const UTF8 = 'UTF-8';

    /**
     * convert charset encoding to utf8
     * @param  String $content  content need to convert
     * @param  String $encoding orignal encoding
     * @return String           Converted content
     */
    public static function convertToUTF8($content, $encoding) {
        return mb_convert_encoding($content, self::UTF8, $encoding);
    }

    /**
     * Decode html entities and special characters to original chars
     * @param  String $content Content need to decode
     * @return String         Decoded content
     */
    public static function htmlDecode($content) {
        return html_entity_decode($content, ENT_QUOTES , self::UTF8); 
    }

    /**
     * Encode special html characters, no html entities encoded
     * @param  String $content Contetn need to encode
     * @return String          Encoded content
     */
    public static function htmlEncode($content) {
        return htmlspecialchars ($content);
    }

}







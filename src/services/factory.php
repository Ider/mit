<?php
include_once 'src/services/google/fetcher.php';

class FetcherFactory {

    protected static $sources 
            = array('google' => true,
                    'cinemark' => false,
                        );

    protected static $theaterFetcherClasses 
            = array('google' => 'GoogleTheatersFetcher',
                    'cinemark' => null,
                        );

    protected static $movieFetcherClasses 
            = array('google' => 'GoogleMoviesFetcher',
                    'cinemark' => null,
                        );
    public static function theaterListFetcher($zipcode, $source = 'google') {
        if (!isset(self::$sources[$source]) || !self::$sources[$source]) {
            $source = 'google';
        }

        $fetcherClass = self::$theaterFetcherClasses[$source];
        return new $fetcherClass($zipcode);
    }


    public static function movieListFetcher($tid, $date, $source = 'google') {
        if (!isset(self::$sources[$source]) || ! self::$sources[$source]) {
            $source = 'google';
        }

        $fetcherClass = self::$movieFetcherClasses[$source];
        return new $fetcherClass($tid, $date);
    }
}
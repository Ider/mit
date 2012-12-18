<?php
include_once 'src/services/google/fetcher.php';

class FetcherFactory {

    protected static $sources 
            = array('google' => true,
                    'cinemark' => false,
                        );

    protected static $theaterFetchers 
            = array('google' => 'GoogleTheatersFetcher',
                    'cinemark' => null,
                        );

    protected static $movieFetchers 
            = array('google' => 'GoogleMoviesFetcher',
                    'cinemark' => null,
                        );
    public static function theaterListFetcher($zipcode, $source = 'google') {
        if (!isset(self::$sources[$source]) || ! self::$sources[$source]) {
            $source = 'google';
        }

        $fetcher = self::$theaterFetchers[$source];
        return new $fetcher($zipcode);
    }


    public static function movieListFetcher(Theater $theater, DateTime $date, $source = 'google') {
        if (!isset(self::$sources[$source]) || ! self::$sources[$source]) {
            $source = 'google';
        }

        $fetcher = self::$movieFetchers[$source];
        return new $fetcher($theater, $date);
    }
}
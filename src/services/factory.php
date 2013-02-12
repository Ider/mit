<?php
include_once 'src/services/google/fetcher.php';
include_once 'src/utilities/util.php';

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

        //try to get data from database
        $fetcher = new DBTheaterFetcher($zipcode, $source);
        if ($fetcher->hasDataReserved()) {
            error_log('has data saved');
            return $fetcher;
        }

        //no data saved in database, fetche data from source
        $fetcherClass = self::$theaterFetcherClasses[$source];
        return new $fetcherClass($zipcode);
    }

    public static function movieListFetcher($tid, $date, $source = 'google') {
        if (!isset(self::$sources[$source]) || ! self::$sources[$source]) {
            $source = 'google';
        }
        $date = DateUtil::formatDate($date);
        //try to get data from database
        $fetcher = new DBMoviesFetcher($tid, $date, $source);
        if ($fetcher->hasDataReserved()) {
            error_log('has data saved');
            return $fetcher;
        }

        $fetcherClass = self::$movieFetcherClasses[$source];
        return new $fetcherClass($tid, $date);
    }
}
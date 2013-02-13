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
    public static function theaterListFetcher($area, $source = 'google') {
        $source = self::formatSource($source);

        //try to get data from database
        if (ENABLE_RESERVATION) {
            $fetcher = new DBTheaterFetcher($area, $source);
            if ($fetcher->hasDataReserved()) {
                error_log('has data saved');
                return $fetcher;
            }
        }

        //no data saved in database, fetche data from source
        $fetcherClass = self::$theaterFetcherClasses[$source];
        return new $fetcherClass($area);
    }

    public static function movieListFetcher($tid, $date, $source = 'google') {
        $source = self::formatSource($source);
        $date = DateUtil::formatDate($date);

        //try to get data from database
        if (ENABLE_RESERVATION) {
            $fetcher = new DBMoviesFetcher($tid, $date, $source);
            if ($fetcher->hasDataReserved()) {
                error_log('has data saved');
                return $fetcher;
            }
        }

        $fetcherClass = self::$movieFetcherClasses[$source];
        return new $fetcherClass($tid, $date);
    }

    protected static function formatSource($source) {
        $source = strtolower($source);
        if (!isset(self::$sources[$source]) || !self::$sources[$source]) {
            $source = 'google';
        }

        return $source;
    }
}

class ReserverFactory {
    protected static $reservers = array();
    
    public static function reserver() {
        $reserverClassName = 'BogusReserver';
        if (ENABLE_RESERVATION) {
            $reserverClassName = 'DBReverser';
        } 

        return self::getReserver($reserverClassName);
    }

    protected static function getReserver($className) {
        if (!isset(self::$reservers[$className])) {
            self::$reservers[$className] = new $className();
        }

        return self::$reservers[$className];
    }   

}
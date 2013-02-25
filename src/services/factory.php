<?php
include_once 'src/services/google/fetcher.php';
include_once 'src/services/reservation/fetcher.php';
include_once 'src/services/reservation/loader.php';
include_once 'src/services/reservation/reserver.php';
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
        $fetcher = new ReservationTheaterFetcher($area, $source);
        if ($fetcher->theaterListSize() > 0) {
            error_log('has data saved');
            return $fetcher;
        }

        //no data saved in database, fetche data from source
        $fetcherClass = self::$theaterFetcherClasses[$source];
        return new $fetcherClass($area);
    }

    public static function movieListFetcher($tid, $date, $source = 'google') {
        $source = self::formatSource($source);
        $date = DateUtil::formatDate($date);

        //try to get data from database
        $fetcher = new ReservationMoviesFetcher($tid, $date, $source);
        if ($fetcher->movieListSize() > 0) {
            error_log('has data saved');
            return $fetcher;
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

class ReservationFactory {
    //singleton mapper
    protected static $reservers = array();
    
    public static function reserver() {
        $reserverClassName = 'BogusReserver';
        if (ENABLE_RESERVATION) {
            $reserverClassName = 'MysqlReverser';
        } 

        return self::getReserver($reserverClassName);
    }

    protected static function getReserver($className) {
        if (!isset(self::$reservers[$className])) {
            self::$reservers[$className] = new $className();
        }

        return self::$reservers[$className];
    }

    public static function loader($source) {
        $fetcherClass = 'BogusLoader';
        if (ENABLE_RESERVATION) {
            $fetcherClass = 'MysqlLoader';
        }

        $fetcher = new $fetcherClass($source);
        return $fetcher;
    }
}


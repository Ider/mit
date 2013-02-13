<?php
include_once 'src/models/theater.php';
include_once 'src/models/movie.php';
include_once 'src/models/orm.php';
include_once 'src/services/reserver.php';

abstract class TheatersFetcher {
    protected $theaterList;

    public function __construct($zipcode = '') {
        $this->theaterList = new TheaterList();
        $this->theaterList->zipcode = $zipcode;
    }

    public function theaterList() {
        if (empty($this->theaterList->theaters)) {
            $this->theaterList->theaters = $this->fetchTheaters();

            //reserve theater data to database,
            //if do not want this happen, update $this->theaterList->theaters in __construct 
            $reserver = new DBReverser();
            $reserver->reserveTheaterList($this->theaterList);
        }

        return $this->theaterList;
    }

    /**
     * Fetch a list of theaters infomation associated with the zipcode
     */
    abstract protected function fetchTheaters();
}

abstract class MoviesFetcher {
    protected $movieList;
    protected $tid;

    public function __construct($tid = '', $date = null) {
        $list = new MovieList();
        $list->showtime_date = $date;
        $this->movieList = $list;
        $this->tid = $tid;
    }

    public function movieList() {
        if (empty($this->movieList->movies)) {
            $this->movieList->theater = $this->fetchTheater();
            $this->movieList->movies = $this->fetchTheaterMovies();
        }

        return $this->movieList;
    }

    /**
     * Fetch theater infomation associfated with tid
     * @return Theater [description]
     */
    abstract protected function fetchTheater();

    /**
     * Fetch a list of movies infomation that showing in theater in that date
     * @return MovieList 
     */
    abstract protected function fetchTheaterMovies();
}


/********************* Database Fetcher *********************/

class MysqlDB {
    const SOURCE = 'mysql';
}

class DBTheaterFetcher extends TheatersFetcher{
    public $search_source = '';
    public function __construct($zipcode, $source) {
        parent::__construct($zipcode);
        $this->theaterList->source = MysqlDB::SOURCE;
        $this->search_source = $source;
        //fetch theaters immediately to prevent fetching from super class
        //and readd to database
        $this->theaterList->theaters = $this->fetchTheaters();
    }

    protected function fetchTheaters() {
        $orm = new MysqlORM('Theater');
        $mysqli = $orm->mysqli();
        $search_sign = $mysqli->real_escape_string($this->theaterList->zipcode);
        $search_source = $mysqli->real_escape_string($this->search_source);
        $query = <<<EOL
SELECT search_sign, source, tid, name, link, address, phone, created_time
    FROM theaters
    WHERE search_sign = '$search_sign' AND source = '$search_source'
EOL;
        $theaters = $orm->mapArray($query);
        $orm->close();
        return $theaters;
    }

    public function hasDataReserved() {
        return !empty($this->theaterList->theaters);
    }
}

class DBMoviesFetcher extends MoviesFetcher{
    protected function fetchTheater() {
        return null;
    }

    protected function fetchTheaterMovies() {

    }

    public function hasDataReserved() {
        return !empty($this->movieList->movies);
    }
}



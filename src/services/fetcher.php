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
            $reserver = DBReverser::instance();
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

    public function __construct($tid = '', $date = null) {
        $list = new MovieList();
        $list->showtime_date = $date;
        $list->tid = $tid;
        $this->movieList = $list;
    }

    public function movieList() {
        if (empty($this->movieList->movies)) {
            $this->movieList->theater = $this->fetchTheater();
            $this->movieList->movies = $this->fetchTheaterMovies();

            $reserver = DBReverser::instance();
            $reserver->reserveMovieList($this->movieList);
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
        $source = $mysqli->real_escape_string($this->search_source);
        $query = <<<EOL
SELECT search_sign, source, tid, name, link, address, phone
    FROM theaters
    WHERE search_sign = '$search_sign' AND source = '$source'
EOL;
        $theaters = $orm->mapArray($query);
        $orm->close();
        return $theaters;
    }

    public static function fetchTheater($tid, $source) {
        $orm = new MysqlORM('Theater');
        $mysqli = $orm->mysqli();
        $tid = $mysqli->real_escape_string($tid);
        $source = $mysqli->real_escape_string($source);
        $query = <<<EOL
SELECT source, tid, name, link, address, phone
    FROM theaters
    WHERE tid = '$tid' AND source = '$source'
EOL;

        $theater = $orm->mapObject($query);
        $orm->close();
        return $theater;
    }

    public function hasDataReserved() {
        return !empty($this->theaterList->theaters);
    }
}

class DBMoviesFetcher extends MoviesFetcher{
    public $search_source = '';
    
    public function __construct($tid, $date, $source) {
        parent::__construct($tid, $date);
        $this->search_source = $source;
        $this->theaterList->source = MysqlDB::SOURCE;
        $theater = $this->fetchTheater();
        if ($theater) {
            $this->movieList->theater = $theater;
            $this->movieList->movies = $this->fetchTheaterMovies();
        }
    }

    protected function fetchTheater() {
        return DBTheaterFetcher::fetchTheater($this->movieList->tid, $this->search_source);
    }

    protected function fetchTheaterMovies() {
        $orm = new MysqlORM('Movie');
        $mysqli = $orm->mysqli();
        $tid = $mysqli->real_escape_string($this->movieList->tid);
        $source = $mysqli->real_escape_string($this->search_source);
        $date = $mysqli->real_escape_string($this->movieList->showtime_date);
        $query = <<<EOL
SELECT m.source, m.mid, m.name, m.link, m.imageURL, m.runtime, m.info, s.showtimes
    FROM movies AS m JOIN showtimes AS s
        ON (m.mid = s.mid AND m.source = s.source)
    WHERE s.tid = '$tid' 
        AND s.showtime_date = '$date'
        AND s.source = '$source'
EOL;
        $movies = $orm->mapArray($query);
        $movies = array();
        // json decode movie showtimes to array
        // and movie info to dictionary
        foreach ($movies as $movie) {
            $movie->showtimes = json_decode($movie->showtimes);
            $movie->info = json_decode($movie->info);
        }

        $orm->close();
        return $movies;
    }

    public function hasDataReserved() {
        return !empty($this->movieList->movies);
    }
}



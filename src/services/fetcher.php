<?php
include_once 'src/models/theater.php';
include_once 'src/models/movie.php';

abstract class TheatersFetcher {
    protected $theaterList;

    public function __construct($zipcode = '') {
        $this->theaterList = new TheaterList();
        $this->theaterList->zipcode = $zipcode;
    }

    public function theaterList() {
        if (empty($this->theaterList->theaters)) {
            $this->theaterList->theaters = $this->fetchTheaters();
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

    public function __construct($tid = '', DateTime $date = null) {
        $list = new MovieList();
        $list->date = $date;
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
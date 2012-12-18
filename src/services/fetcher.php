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
    public function __construct(Theater $theater = null, DateTime $date = null) {
        $list = new MovieList();
        $list->date = $date;
        $list->theater = $theater;
        $this->movieList = $list;
    }

    public function movieList() {
        if (empty($this->movieList->movies)) {
            $this->movieList->movies = $this->fetchTheaterMovies();
        }

        return $this->movieList;
    }

    /**
     * Fetch a list of movies infomation that showing in theater in that date
     */
    abstract protected function fetchTheaterMovies();
}
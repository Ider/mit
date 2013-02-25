<?php
include_once 'src/models/matcher.php';
include_once 'src/services/fetcher.php';
include_once 'src/services/reservation/loader.php';

/**
 * 
 */
class ReservationTheaterFetcher extends TheatersFetcher{
    protected $search_source = '';
    protected $loader;

    public function __construct($area, $source) {
        parent::__construct($area);
        $this->theaterList->source = $source;
        $this->search_source = $source;

        $this->loader = ReservationFactory::loader($source);

        //Fetch theaters immediately so that caller could check with theaterListSize() to know if data has reservation 
        //it also prevents fetching from super class
        $this->theaterList->theaters = $this->fetchTheaters();
    }

    protected function fetchTheaters() {
        $search_sign = $this->theaterList->area;
        
        $theaters = $this->loader->loadTheatersWithSearch($search_sign);
        return $theaters;
    }
}

/**
 * 
 */
class ReservationMoviesFetcher extends MoviesFetcher{
    protected $search_source = '';
    protected $loader;

    public function __construct($tid, $date, $source) {
        parent::__construct($tid, $date);
        $this->search_source = $source;
        $this->movieList->source = $source;

        $this->loader = ReservationFactory::loader($source);

        //Fetch movies immediately so that caller could check with size() to know if data has reservation 
        //it also prevents fetching from super class
        $theater = $this->fetchTheater();
        if ($theater) {
            $this->movieList->theater = $theater;
            $this->movieList->movies = $this->fetchTheaterMovies();
        }
    }

    protected function fetchTheater() {
        $tid = $this->movieList->tid;

        $theater = $this->loader->loadTheaterWithId($tid);
        return $theater;
    }

    protected function fetchTheaterMovies() {
        $tid = $this->movieList->tid;
        $date = $this->movieList->showtime_date;

        $movies = $this->loader->loadMoviesWithShowTime($tid, $date);
        return $movies;
    }
}
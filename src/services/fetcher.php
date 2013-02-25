<?php
include_once 'src/models/theater.php';
include_once 'src/models/movie.php';
include_once 'src/services/factory.php';

abstract class TheatersFetcher {
    protected $theaterList;

    public function __construct($area = '') {
        $this->theaterList = new TheaterList();
        $this->theaterList->area = $area;
    }

    /**
     * This method directly measure $theaterList varaible, it does not call theaterList(), hence it would
     * not fire fetching process, even if movies does not fetched.
     * @return int      Count of theaters in $theaterList
     */
    public function theaterListSize() {
        return count($this->theaterList->theaters);
    }

    /**
     * Getter of $theaterList, if $theaters of $theaterList is empty, it call fetchTheaters() to fetch
     * theater data from out source, and reserve to local reservation
     * @return TheaterList      Class member $theaterList
     */
    public function theaterList() {
        if (empty($this->theaterList->theaters)) {
            $this->theaterList->theaters = $this->fetchTheaters();

            //reserve theater data to database,
            //if do not want this happen, update $this->theaterList->theaters in __construct 
            $reserver = ReservationFactory::reserver();
            $reserver->reserveTheaterList($this->theaterList);
        }

        return $this->theaterList;
    }

    /**
     * Fetch a list of theaters infomation associated with the area
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

    /**
     * This method directly measure $movieList variable, it does not call movieList() to get $moiveList.
     * @return int      Count of movies in current $movieList
     */
    public function movieListSize() {
        return count($this->movieList->movies);
    }

    /**
     * Getter of $movieList. If $movies of $movieList is empty, it call abstract methods
     * to fetcher required data, and reserve data to local reservation.
     * @return MovieList    Class member $movieList
     */
    public function movieList() {
        if (empty($this->movieList->movies)) {
            $this->movieList->theater = $this->fetchTheater();
            $this->movieList->movies = $this->fetchTheaterMovies();

            $reserver = ReservationFactory::reserver();
            $reserver->reserveMovieList($this->movieList);
        }

        return $this->movieList;
    }

    /**
     * Fetch theater infomation associfated with tid
     * @return Theater 
     */
    abstract protected function fetchTheater();

    /**
     * Fetch a list of movies infomation that showing in theater in that date
     * @return MovieList 
     */
    abstract protected function fetchTheaterMovies();
}



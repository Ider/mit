<?
include_once './src/models/theater.php';
include_once './src/models/movie.php';

abstract class TheatersFetcher {
    protected $theaterList;

    public function __construct($zipcode) {
        $this->theaterList = new TheaterList();
        $this->theaterList->zipcode = $zipcode;
    }

    /**
     * Fetch a list of theaters infomation associated with the zipcode
     */
    abstract public function fetchTheaters();
}


abstract class MoviesFetcher {
    protected $movieList;
    public function __construct(Theater $theater, $date) {
        $list = new MovieList();
        $list->date = $date;
        $list->theater = $theater;
        $this->movieList = $list;
    }

    /**
     * Fetch a list of movies infomation that showing in theater in that date
     */
    abstract public abstract fetchTheaterMovies();
}
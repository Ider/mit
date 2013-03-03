<?php
include_once 'src/models/matcher.php';
include_once 'src/services/fetcher.php';
include_once 'src/services/reservation/loader.php';
include_once 'src/utilities/util.php';


class GoogleMovie {
    const URL = 'http://www.google.com/movies?near=usa';
    const SOURCE = 'google';
    const ENCODING = 'ISO-8859-1';
    
    public static function theaterListContentURL($area) {
        $url = static::URL;
        return "$url+$area";
    }

    public static function theaterLink($tid) {
        $url = static::URL;
        return "$url&tid=$tid";
    }

    public static function movieListContentURL($tid, $date = null) {
        $url = static::URL;
        $date = new DateTime($date);

        //Google using day diff to determine showtime date and only give small date span
        $diff = $date->diff(new DateTime());
        $day = $diff->d;

        return "$url&tid=$tid&date=$day";
    }

    public static function movieLink($mid) {
        $url = static::URL;
        return "$url&mid=$mid";
    }
}

class GoogleTheatersFetcher extends TheatersFetcher {
    protected $contents;

    public function __construct($area) {
        parent::__construct($area);
        $this->initContents();
        $this->theaterList->source = GoogleMovie::SOURCE;
    }
    
    protected static function configClass() {
        return 'GoogleMovie';
    }

    protected function initContents() {
        $this->contents = array();
        $config = static::configClass();

        $area = $this->theaterList->area;

        $url = $config::theaterListContentURL($area);
        $con = file_get_contents($url);
        if ($con === false) {
            //TODO: log error here
            return;
        }

        $con = EncodingUtil::convertToUTF8($con, $config::ENCODING);
        
        static $separator = '<div class=theater>';
        $arr = explode($separator, $con);
        if (array_shift($arr) === null) {
            //TODO: log error here
            return;
        }

        static $spliter = '<div class=showtimes>';
        $count = count($arr);
        for ($i=0; $i < $count; $i++) { 
            $theaterCon = explode($spliter , $arr[$i]);
            $arr[$i] = $theaterCon[0];
        }

        $this->contents = $arr;
    }

    public function fetchTheaters() {
        $theaters = array();
        $config = static::configClass();

        foreach ($this->contents as $content) {
            $theater = static::fetchTheater($content);
            if ($theater == null) continue;

            $theater->link = $config::theaterLink($theater->tid);
            $theater->source = $config::SOURCE;

            $theater->name = EncodingUtil::htmlDecode($theater->name);
            $theater->address = EncodingUtil::htmlDecode($theater->address);
            
            $theaters[] = $theater;
        }
        return $theaters;
    }

    public static function fetchTheater($content) {
        $theater = new Theater();
        $matcher = new StringMatcher($content);
        $patternList = static::theaterMatchingPatternList();

        $matcher->execute($patternList, $theater);

        return $theater;
    }

    protected static function theaterMatchingPatternList() {
        static $patternList = null;
        if ($patternList == null) {
            $patternList = array(
                array('name', '>([^<>]+)</a></h2>'),
                array('tid', 'tid=([a-z0-9]+)'),
                array('address', '<div class=info>([^<]+) - '),
                array('phone', '<div class=info>[^<]+ - ([^<]+)'),
            );
        }

        return $patternList;
    }
}

class GoogleMoviesFetcher extends MoviesFetcher  {
    /* inherited from MoviesFetcher
     *
     * protected $movieList;
     * 
    **/

    protected $contents;
    protected $theaterContent;

    public function __construct($tid = '', $date) {
        parent::__construct($tid, $date);
        $this->movieList->source = GoogleMovie::SOURCE;
        $this->initContents();        
    }

    protected static function configClass() {
        return 'GoogleMovie';
    }

    protected function initContents() {
        $this->contents = array();
        $config = static::configClass();
        
        $date = $this->movieList->showtime_date;
        $tid = $this->movieList->tid;
        $url = $config::movieListContentURL($tid, $date);
        $con = file_get_contents($url);

        if ($con === false) {
            //TODO: log error here
            return;
        }

        $con = EncodingUtil::convertToUTF8($con, $config::ENCODING);
        
        static $separator = '<div class=movie>';
        $arr = explode($separator, $con);
        $theaterContent = array_shift($arr);
        if ($theaterContent === null) {
            //TODO: log error here
            return;
        }

        $this->theaterContent = $theaterContent;
        $this->contents = $arr;
    }

    public function fetchTheater() {
        $theater = GoogleTheatersFetcher::fetchTheater($this->theaterContent);
        return $theater;
    }

    public function fetchTheaterMovies() {
        $movies = array();
        $config = static::configClass();
        foreach ($this->contents as $content) {
            $movie = static::fetchMovie($content);
            if ($movie == null) continue;

            $movie->link = $config::movieLink($movie->mid);
            $movie->source = $config::SOURCE;

            $movie->name = EncodingUtil::htmlDecode($movie->name);

            $movies[] = $movie;
        }

        $this->fetchExtraInfo($movies);

        return $movies;
    }

    protected function fetchExtraInfo($movies) {
        if (empty($movies)) return $movies;
        
        $notFoundMovies = $this->fetchReservedMovies($movies);

        foreach ($notFoundMovies as $movie) {
            $this->fetchExtraMovieInfo($movie);
        }
    }

    /**
     * Try to load $movies information from database first
     * @param  Array $movies orignal movies
     * @return Array of movies that are not found in Database
     */
    protected function fetchReservedMovies($movies) {
        $loader = ReservationFactory::loader($this->movieList->source);

        $moviesMap = array();
        $moviesMid = array();
        foreach ($movies as $movie) {
            $moviesMid[] = $movie->mid;
            $moviesMap[$movie->mid] = $movie;
        }

        $reservedMovies = $loader->loadMoviesWithIds($moviesMid);
        error_log(count($reservedMovies));
        foreach ($reservedMovies as $reservedMovie) {
            $mid = $reservedMovie->mid;
            $movie = $moviesMap[$mid];

            $reservedMovie->info = json_decode($reservedMovie->info, true);
            $reservedMovie->showtimes = $movie->showtimes;

            $movie->assignMovie($reservedMovie);
            
            unset($moviesMap[$mid]);
        }

        return $moviesMap;
    }


    protected function fetchExtraMovieInfo($movie) {
        static $matcher = null;
        static $tid = '';
        if ($matcher == null) {
            $matcher = new PatternMatcher('<img src="(//ssl.+?)"');
            $tid = '&tid='. $this->movieList->theater->tid;
        }

        //google movie will look for the place to determiter the movie is show in that area
        //or not, hence $tid is append to specify the area, otherwise movie page cannot be load
        $mLink = $movie->link.$tid;
        $con = file_get_contents($mLink);
        $matches = $matcher->match($con);
        if (empty($matches)) {
            error_log('Cannot find movie thumbnail image');
            return;
        }
        $movie->imageURL = 'http:'.$matches[1];
    }

    public static function fetchMovie($content) {
        $movie = new Movie();
        $matcher = new StringMatcher($content);
        $patternList = static::movieMatchingPatternList();

        $matcher->execute($patternList, $movie);

        $showtimePattern = '(\d+):(\d+)(am|pm)?&';
        $matches = $matcher->match($showtimePattern, true);
        if (empty($matches)) {
            return null;
        }

        $count = count($matches);
        $isPM = false;
        $showtimes = array();
        for ($i=$count-1; $i >= 0 ; $i--) { 
            $showtime = $matches[$i];
            $h = $showtime[1];
            $m = $showtime[2];
            if(!empty($showtime[3])) $isPM = ($showtime[3] == 'pm');

            if ($h < 12 && $isPM) $h += 12;

            $showtimes[] = "$h:$m";
        }
        
        $movie->showtimes = array_reverse($showtimes);
        
        return $movie;
    }

    protected static function movieMatchingPatternList() {
        static $patternList = null;
        if ($patternList == null) {
            $patternList = array(
                array('name', '>([^><]+?)</a>'),
                array('mid', 'mid=([a-z0-9]+)'),
                array('runtime', '(\d+)hr (\d+)min', function($matches){ return $matches[1]*60+$matches[2]; }),
            );
        }

        return $patternList;
    }
}




















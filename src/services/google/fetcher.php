<?php
require_once 'src/services/fetcher.php';
require_once 'src/models/matcher.php';

class GoogleMovie {
    const URL = 'http://www.google.com/movies';
    const SOURCE = 'Google';

    public static function theaterListContentURL($zipcode) {
        $url = self::URL;
        return "$url?near=$zipcode";
    }

    public static function movieListContentURL($tid, DateTime $date = null) {
        $url = self::URL;
        if (!isset($date)) $date = new DateTime();

        //Google using day diff to determine showtime date and only give small date span
        $diff = $date->diff(new DateTime());
        $day = $diff->d;

        return "$url?tid=$tid&date=$day";
    }

    public static function movieContentURL($mid) {
        $url = self::URL;
        return "$url?mid=$mid";
    }
}

class GoogleTheatersFetcher extends TheatersFetcher {
    private $contents;

    public function __construct($zipcode) {
        parent::__construct($zipcode);
        $this->initContents();
        $this->theaterList->source = GoogleMovie::SOURCE;
    }

    private function initContents() {
        $this->contents = array();

        $zipcode = $this->theaterList->zipcode;
        $url = GoogleMovie::theaterListContentURL($zipcode);
        $con = file_get_contents($url);

        if ($con === false) {
            //TODO: log error here
            return;
        }

        static $separator = '<div class=theater>';

        $arr = explode($separator, $con);
        if (array_shift($arr) === null) {
            //TODO: log error here
            return;
        }

        static $spliter = '<div class=showtime>';
        $count = count($arr);
        for ($i=0; $i < $count; $i++) { 
            $theaterCon = explode($spliter , $arr[$i]);
            $arr[$i] = $theaterCon[0];
        }

        $this->contents = $arr;
    }

    public function fetchTheaters() {
        $theaters = array();
        foreach ($this->contents as $content) {
            $theater = $this->fetchTheater($content);
            if ($theater == null) continue;
            $theaters[] = $theater;
        }
        return $theaters;
    }

    protected function fetchTheater($content) {
        $theater = new Theater();
        $matcher = new StringMatcher($content);
        $patternList = self::theaterMatchingPatternList();

        $matcher->execute($patternList, $theater);

        $theater->link = GoogleMovie::movieListContentURL($theater->tid);

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
    // protected $movieList;    //inherited from MoviesFetcher

    private $contents;

    public function __construct(Theater $theater, DateTime $date) {
        parent::__construct($theater, $date);
        $this->initContents();        
        $this->movieList->source = GoogleMovie::SOURCE;
    }

    private function initContents() {
        $this->contents =array();

        $tid = $this->movieList->theater->tid;
        $date = $this->movieList->date;
        $url = GoogleMovie::movieListContentURL($tid, $date);
        $con = file_get_contents($url);

        if ($con === false) {
            //TODO: log error here
            return;
        }
        static $separator = '<div class=movie>';

        $arr = explode($separator, $con);
        if (array_shift($arr) === null) {
            //TODO: log error here
            return;
        }

        $this->contents = $arr;
    }

    public function fetchTheaterMovies() {
        $movies = array();
        foreach ($this->contents as $content) {
            $movie = $this->fetchMovie($content);
            if ($movie == null) continue;
            $movies[] = $movie;
        }
        return $movies;
    }

    protected function fetchMovie($content) {
        $movie = new Movie();
        $matcher = new StringMatcher($content);
        $patternList = self::movieMatchingPatternList();

        $matcher->execute($patternList, $movie);

        $movie->link = GoogleMovie::movieContentURL($movie->mid);

        $showtimePattern = '(\d+):(\d+)(am|pm)?&';
        $matches = $matcher->match($showtimePattern, true);
        if (empty($matches)) {
            return null;
        }

        $count = count($matches);
        $isPM = true;
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




















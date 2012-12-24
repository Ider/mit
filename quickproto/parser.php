<?php

class Theater {
    public $name = "";
    public $address = "";
    public $phone = "";
    public $movies = array();
}

class Movie {
    public $name = "";
    public $runtime = "";
    public $showtimes = array();

    //for gooogle
    public $mid="";
}

class GoogleMovieParser {

    public function parse($content) {
        $movies = explode('<div class=movie>', $content);
        $count = count($movies);
        if ($count == 1) {
            $this->error("Content does not contain movie information");
            return null;
        }
        $theater = $this->getTheater($movies[0]);
        if ($theater == null) {
            $this->error("No theater infomation find");
            return null;
        }

        for ($i=1; $i < $count; $i++) { 
            $movie = $this->getMovie($movies[$i]);
            if ($movie == null) continue;

            $theater->movies[] = $movie;
        }

        return $theater;
    }

    protected function getTheater($content) {
        $match = array();
        $namePattern = '/<h2 class=name>(.+?)<\/h2>/';
        $result = preg_match($namePattern, $content, $match);
        //0 or false returned
        if (!$result) {
            $this->error('No theater name find');
        }
        $theater = new Theater();
        $theater->name = $match[1];

        $infoPattern = '/<div class=info>([^<]+)</';

        $result = preg_match($infoPattern, $content, $match);
        //0 or false returned
        if (!$result) {
            $this->error('No theater info find');
        }
        $info = explode(' - ', $match[1]);
        $theater->address = $info[0];
        $theater->phone = $info[1];

        return $theater;
    }

    protected function getMovie($content) {
        $match = array();
        $namePattern = '/>([^><]+?)<\/a>/';

        $result = preg_match($namePattern, $content, $match);
        //0 or false returned
        if (!$result) {
            $this->error('No movie name find');
            return null;
        }
        $movie = new Movie();
        $movie->name = $match[1];

        $idPattern = '/mid=([a-z0-9]+)/';
        $result = preg_match($idPattern, $content, $match);
        if ($result) {
           $movie->mid = $match[1];
        }
        
        $runtimePattern = '/(\d+)hr (\d+)min/';
        $result = preg_match($runtimePattern, $content, $match);
        if (!$result) {
           $this->error('No runtime find');
            return null;
        }

        $movie->runtime = $match[1]*60+$match[2];

        $showtimePattern = '/(\d+):(\d+)(am|pm)?&/i';
        $result = preg_match_all($showtimePattern, $content, $match, PREG_SET_ORDER);
        if (!$result) {
           $this->error('No showtime find');
            return null;
        }
        $count = count($match);
        $isPM = true;
        for ($i=$count-1; $i >= 0 ; $i--) { 
            $showtime = $match[$i];
            $h = $showtime[1];
            $m = $showtime[2];
            if(!empty($showtime[3])) $isPM = ($showtime[3] == 'pm');

            if ($h < 12 && $isPM) {
                $h += 12;
            }

            $movie->showtimes[] = "$h:$m";
        }
        
        $movie->showtimes = array_reverse($movie->showtimes);

        return $movie;
    }

    public function error($error) {
        // echo $error;
    }

}
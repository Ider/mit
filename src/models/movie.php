<?php

class Movie {
    public $source = 'unknown';
    public $mid = '';
    public $name = '';
    public $link = '';
    public $imageURL = '';
    public $runtime = '';
    public $showtimes = array();//times in 24h format
    public $info = array(); //a dictionary that save other movie informations with key/value paire

    /**
     * Assign other $movie to this object, assign each information to 
     * @param  Movie  $movie other movie object
     * @return $this object
     */
    public function assignMovie(Movie $movie) {
        foreach ($movie as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Assign any object to $movie with matched properties, this is very useful to change stdClass
     * from json_decode to real Movie object.
     * @param  $obj [description]
     * @return 
     */
    public function assignObject($obj) {
        foreach ($obj as $key => $value) {
            if (!isset($this->$key)) continue;

            if (gettype($value) == gettype($this->$key)) {
                $this->$key = $value;
            }
            //else //sliently ignore the values that type is not match
        }
        
        return $this;
    }
}


class MovieList {
    public $tid = '';
    public $theater = null; //should be instance of Theater
    public $showtime_date = '';
    public $movies = array();
    public $source = 'unknown';

    public function __get($name) {
        switch ($name) {
            case 'size':
                return count($this->movies);
            
            default:
                return null;
        }
    }
}


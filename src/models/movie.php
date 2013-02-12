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
    public $_fromDB = false; //boolean if true, the data is from Database; if false, it need to add to database
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


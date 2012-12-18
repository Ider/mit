<?php

class Movie {    
    public $mid = '';
    public $name = '';
    public $link = '';
    public $runtime = ''; 
    public $showtimes = array();//times in 24h format
}


class MovieList {
    public $theater = null; //should be instance of Theater
    public $date = null;    //instance of DateTime
    public $movies = array();

    public function __get($name) {
        switch ($name) {
            case 'size':
                return count($this->movies);
            
            default:
                return null;
        }
    }
}


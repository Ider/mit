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
    public $date = '';
    public $movies = array();
}


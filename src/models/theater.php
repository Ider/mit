<?php

class Theater {
    public $tid = '';
    public $name = '';
    public $link = '';
    public $address = '';
    public $phone = '';
    public $movies = null; //should be instance of MovieList
}

class TheaterList {
    public $zipcode = '';
    public $link = '';
    public $source = 'unknown';
    public $theaters = array();
}

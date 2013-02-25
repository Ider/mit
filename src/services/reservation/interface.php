<?php


interface IReserver {
    public function reserveTheaterList(TheaterList $list);
    public function reserveTheater(Theater $theater);
    public function reserveMovieList(MovieList $list);
    public function reserveMovie(Movie $movie);
}

interface ILoader {
/********************* Methods for loading Theater *********************/
    public function loadTheatersWithSearch($search_sign);
    public function loadTheaterWithId($tid);
    public function loadTheaterWithName($name);

    public function loadTheatersWithIds($tids);


/********************* Methods for loading Movie *********************/
    public function loadMovieWithId($mid);

    public function loadMoviesWithIds($mids);
    public function loadMoviesWithShowTime($tid, $date);
}

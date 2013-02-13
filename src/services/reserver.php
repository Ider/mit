<?php
include_once 'src/services/connector.php';
include_once "src/models/theater.php";
include_once "src/models/movie.php";

abstract class Reserver {
    abstract public function reserveTheaterList(TheaterList $list);
    abstract public function reserveTheater(Theater $theater);
    abstract public function reserveMovieList(MovieList $list);
    abstract public function reserveMovie(Movie $movie);
}


class DBReverser extends Reserver {
    public function reserveTheaterList(TheaterList $list) {
        $mysqli = DBConnector::getMysqli();
        $query = <<<EOL
INSERT INTO theaters (search_sign, source, tid, name, link, address, phone, created_time)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
EOL;

        $stm = $mysqli->prepare($query);

        $stm->bind_param('ssssssss', $search_sign, $source, $tid, $name, $link, $address, $phone, $created_time);
        
        $source = $list->source;
        $search_sign = $list->zipcode;
        $created_time = DateUtil::datetimeNow();
        $theaters = $list->theaters;

        foreach ($theaters as $theater) {
            $tid = $theater->tid;
            $name = $theater->name;
            $link = $theater->link;
            $address = $theater->address;
            $phone = $theater->phone;
            $stm->execute();
        }

        $stm->close();
        $mysqli->close();
    }

    public function reserveTheater(Theater $theater) {
        $list = new TheaterList();
        $list->zipcode = $theater->zipcode;
        $list->source = $theater->source;
        $list->theaters = array($theater);

        $this->reserveTheaterList($list);
    }

    public function reserveMovieList(MovieList $list) {

    }

    public function reserveMovie(Movie $movie) {

    }
}
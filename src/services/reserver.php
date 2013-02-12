
<?php
include_once 'src/services/connector.php';
include_once "src/models/theater.php";
include_once "src/models/movie.php";
include_once 'src/utilities/util.php';

abstract class Reserver {
    abstract public function reserveTheaterList(TheaterList $list);
    abstract public function reserveTheater(Theater $theater);
    abstract public function reserveMovieList(MovieList $list);
    abstract public function reserveMovie(Movie $movie);
}


class DBReverser extends Reserver {
    private static $_instatnce;
    public static function instance() {
        if (!self::$_instatnce) {
            self::$_instatnce = new DBReverser();
        }
        return self::$_instatnce;
    }

    public function reserveTheaterList(TheaterList $list) {
        if (!$list || empty($list->theaters)) {
            return;
        }

        $query = <<<EOL
INSERT INTO theaters (search_sign, source, tid, name, link, address, phone, created_time)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    
EOL;
        $mysqli = DBConnector::getMysqli();
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
        if (!$theater) {
            return;
        }

        $list = new TheaterList();
        $list->zipcode = $theater->zipcode;
        $list->source = $theater->source;
        $list->theaters = array($theater);

        $this->reserveTheaterList($list);
    }

    /**
     * Reserve movie showtimes, if not empty, as well as the movie infomation if its _fromDB is false;
     * @param  MovieList $list a list that contains movies with showtime in certain theater
     */
    public function reserveMovieList(MovieList $list) {
        $query4movie = <<<EOL
INSERT INTO movies(source, mid, name, link, imageURL, runtime, info)
    VALUES(?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE source = source
EOL;
        $mysqli4movie = DBConnector::getMysqli();
        $stm4movie = $mysqli4movie->prepare($query4movie);
        $stm4movie->bind_param('sssssis', $source, $mid, $name, $link, $imageURL, $runtime, $info);

        $query4showtime = <<<EOL
INSERT INTO showtimes(source, tid, mid, showtime_date, showtimes)
    VALUES(?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE source = source
EOL;
    
        $mysqli4showtime = DBConnector::getMysqli();
        $stm4showtime = $mysqli4showtime->prepare($query4showtime);
        $stm4showtime->bind_param('sssss', $source, $tid, $mid, $showtime_date, $showtimes);
        $source = $list->source;
        $tid = $list->tid;
        $showtime_date = $list->showtime_date;

        $movies = $list->movies;
        foreach ($movies as $movie) {
            //reserve movie showtimes
            $mid = $movie->mid;
            $showtimes = json_encode($movie->showtimes);
            $stm4showtime->execute();

            if ($movie->_fromDB) {
                continue;
            }

            //reserve movie information
            $name = $movie->name;
            $link = $movie->link;
            $imageURL = $movie->imageURL;
            $runtime = $movie->runtime;
            $info = json_encode($movie->info);
            $stm4movie->execute();
        }

        $stm4movie->close();
        $mysqli4movie->close();
        $stm4showtime->close();
        $mysqli4showtime->close();
    }

    /**
     * Reserve movie infomation without showtimes, if the movie->_fromDB is true, do nothing.
     * @param  Movie  $movie
     */
    public function reserveMovie(Movie $movie) {
        if ($movie->_fromDB) {
            return;
        }

        $query = <<<EOL
INSERT INTO movies(source, mid, name, link, imageURL, runtime, info)
    VALUES(?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE source = source
EOL;
        $mysqli = DBConnector::getMysqli();
        $stm = $mysqli->prepare($query);
        
        $stm->bind_param('sssssis', $source, $mid, $name, $link, $imageURL, $runtime, $info);
        
        $source = $movie->source;
        $mid = $movie->mid;
        $name = $movie->name;
        $link = $movie->link;
        $imageURL = $movie->inmageURL;
        $runtime = $movie->runtime;
        $info = json_encode($movie->info);

        $stm->execute();

        $stm->close();
        $mysqli->close();
    }
}
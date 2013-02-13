
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

        $mysqli = DBConnector::getMysqli();

        $theaterValues = array();

        
        $search_sign = $mysqli->real_escape_string($list->zipcode);
        $source = $mysqli->real_escape_string($list->source);
        $created_time = DateUtil::datetimeNow();

        $theaters = $list->theaters;
        foreach ($theaters as $theater) {
            $tid = $mysqli->real_escape_string($theater->tid);
            $name = $mysqli->real_escape_string($theater->name);
            $link = $mysqli->real_escape_string($theater->link);
            $address = $mysqli->real_escape_string($theater->address);
            $phone = $mysqli->real_escape_string($theater->phone);

            $theaterValues[] = "('$search_sign', '$source', '$tid', '$name', '$link', '$address', '$phone', '$created_time')";
        }

        if (!empty($theaterValues)) {
            $theatersValue = implode(',', $theaterValues);
            $query = <<<EOL
INSERT INTO theaters (search_sign, source, tid, name, link, address, phone, created_time)
    VALUES $theatersValue
        ON DUPLICATE KEY UPDATE source = source
EOL;
            
            $mysqli->query($query);
        }

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
        $mysqli = DBConnector::getMysqli();
        
        $source = $mysqli->real_escape_string($list->source);
        $tid = $mysqli->real_escape_string($list->tid);
        $showtime_date = $mysqli->real_escape_string($list->showtime_date);

        $movieValues = array();
        $shotimeValues = array();

        $movies = $list->movies;
        foreach ($movies as $movie) {
            $mid = $mysqli->real_escape_string($movie->mid);
            $name = $mysqli->real_escape_string($movie->name);
            $link = $mysqli->real_escape_string($movie->link);
            $imageURL = $mysqli->real_escape_string($movie->imageURL);
            $runtime = intval($movie->runtime);
            $info = $mysqli->real_escape_string(json_encode($movie->info));

            $movieValues[] = "('$source', '$mid', '$name', '$link', '$imageURL', $runtime, '$info')";

            if (empty($movie->showtimes)) {
                continue;
            }

            $showtimes = $mysqli->real_escape_string(json_encode($movie->showtimes));

            $shotimeValues[] = "('$source', '$tid', '$mid', '$showtime_date', '$showtimes')";

        }

        if (!empty($movieValues)) {
            $moviesValue = implode(',', $movieValues);
            $query4movie = <<<EOL
INSERT INTO movies(source, mid, name, link, imageURL, runtime, info)
    VALUES $moviesValue
    ON DUPLICATE KEY UPDATE source = source
EOL;
            $mysqli->query($query4movie);
        }

        if (!empty($shotimeValues)) {
            $showtimesValue = implode(',', $shotimeValues);

            $query4showtime = <<<EOL
INSERT INTO showtimes(source, tid, mid, showtime_date, showtimes)
    VALUES $showtimesValue
    ON DUPLICATE KEY UPDATE source = source
EOL;

            $mysqli->query($query4showtime);
        }
        $mysqli->close();
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
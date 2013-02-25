
<?php
include_once 'src/services/connector.php';
include_once 'src/models/theater.php';
include_once 'src/models/movie.php';
include_once 'src/utilities/util.php';
include_once 'src/services/reservation/interface.php';

/**
 * MysqlReverser reserve all fetched data to mysql databse on local server
 */
class MysqlReverser extends IReserver {

    public function reserveTheaterList(TheaterList $list) {
        if (!$list || empty($list->theaters)) {
            return;
        }

        $mysqli = MysqlConnector::getMysqli();

        $theaterValues = array();

        
        $search_sign = $mysqli->real_escape_string($list->area);
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
        $list->area = $theater->search_sign;
        $list->source = $theater->source;
        $list->theaters = array($theater);

        $this->reserveTheaterList($list);
    }

    /**
     * Reserve movie showtimes, if not empty
     * @param  MovieList $list a list that contains movies with showtime in certain theater
     */
    public function reserveMovieList(MovieList $list) {
        $mysqli = MysqlConnector::getMysqli();
        
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
     * Reserve movie infomation without showtimes.
     * @param  Movie  $movie
     */
    public function reserveMovie(Movie $movie) {
        $query = <<<EOL
INSERT INTO movies(source, mid, name, link, imageURL, runtime, info)
    VALUES(?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE source = source
EOL;
        $mysqli = MysqlConnector::getMysqli();
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

/**
 * BogusReserver, it is uesed when local reservation is disabled
 *        all inherited reserving methods are doing nothing
 */
class BogusReserver extends Reserver {
    public function reserveTheaterList(TheaterList $list) {}
    public function reserveTheater(Theater $theater) {}
    public function reserveMovieList(MovieList $list) {}
    public function reserveMovie(Movie $movie) {}
}


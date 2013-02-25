<?php
include_once 'src/models/movie.php';
include_once 'src/models/theater.php';
include_once 'src/models/orm.php';
include_once 'src/services/reservation/interface.php';

class MysqlLoader implements ILoader {
    protected $source;

    public function __construct($source = '') {
        $this->source = $source;
    }

    public function loadTheatersWithSearch($search_sign) {
        $orm = new MysqlORM('Theater');
        $mysqli = $orm->mysqli();
        $search_sign = $mysqli->real_escape_string($search_sign);
        $source = $mysqli->real_escape_string($this->source);
        $query = <<<EOL
SELECT search_sign, source, tid, name, link, address, phone
    FROM theaters
    WHERE search_sign = '$search_sign' AND source = '$source'
EOL;
        $theaters = $orm->mapArray($query);
        $orm->close();
        return $theaters;
    }

    public function loadTheaterWithId($tid) {
        $orm = new MysqlORM('Theater');
        $mysqli = $orm->mysqli();

        $tid = $mysqli->real_escape_string($tid);
        $source = $mysqli->real_escape_string($this->source);

        $query = <<<EOL
SELECT source, tid, name, link, address, phone
    FROM theaters
    WHERE tid = '$tid' AND source = '$source'
EOL;

        $theater = $orm->mapObject($query);
        $orm->close();
        return $theater;
    }
    
    public function loadTheatersWithIds($tids) {
        error_log('not implemented');

        return array();
    }
    
    public function loadMovieWithId($mid) {
        error_log('not implemented');
        
        return null;
    }
    
    public function loadTheaterWithName($name) {
        error_log('not implemented');

        return null;
    }
    
    public function loadMoviesWithIds($mids) {
        $orm = new MysqlORM('Movie');
        $mysqli = $orm->mysqli();

        $moviesMid = array();
        foreach ($mids as $mid) {
            $mid = $mysqli->real_escape_string($mid);
            $moviesMid[] = "'$mid'";
        }

        $source = $mysqli->real_escape_string($this->source);
        $movieMids = implode(',', $moviesMid);
        $query = <<<EOL
SELECT source, mid, name, link, imageURL, runtime, info
    FROM movies
    WHERE source = '$source' AND mid in ($movieMids)
EOL;
        $movies = $orm->mapArray($query);

        $orm->close();
        return $movies;
    }
    
    public function loadMoviesWithShowTime($tid, $date) {
        $orm = new MysqlORM('Movie');
        $mysqli = $orm->mysqli();
        $tid = $mysqli->real_escape_string($tid);
        $source = $mysqli->real_escape_string($this->source);
        $date = $mysqli->real_escape_string($date);
        $query = <<<EOL
SELECT m.source, m.mid, m.name, m.link, m.imageURL, m.runtime, m.info, s.showtimes
    FROM movies AS m JOIN showtimes AS s
        ON (m.mid = s.mid AND m.source = s.source)
    WHERE s.tid = '$tid' 
        AND s.showtime_date = '$date'
        AND s.source = '$source'
EOL;
        $movies = $orm->mapArray($query);

        // json decode movie showtimes to array
        // and movie info to dictionary
        foreach ($movies as $movie) {
            $movie->showtimes = json_decode($movie->showtimes, true);
            $movie->info = json_decode($movie->info, true);
        }

        $orm->close();
        return $movies;
    }
}

/**
* 
*/
class BogusLoader implements ILoader {
    public function __construct($source = '') {}
    public function loadTheatersWithSearch($search_sign) { return array();}
    public function loadTheaterWithId($tid) { return null;}
    public function loadTheaterWithName($name) { return null;}
    public function loadTheatersWithIds($tids) { return array();}


/********************* Methods for loading Movie *********************/
    public function loadMovieWithId($mid) { return null; }
    public function loadMoviesWithIds($mids) { return array();}
    public function loadMoviesWithShowTime($tid, $date) { return array();}
}

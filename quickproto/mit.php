<style type="text/css">
/*body {
    width: 980px;
    margin: auto;
}*/
.movie_showtime:hover {
    background-color: green !important;
}

.outer_container {
    position: relative;
    width: 800px;

}
.inner_container {
    position: absolute;
    left: -600px;
    top:0px;
}
h3 {
    text-align: right;
    margin-bottom: 0;
    padding-right: 10px
}

.wishlist_outter_container {
    position: fixed;
    bottom: 5px;
    width: 98%;
    height: 64px;

    left: 1%; 
}
.wish_zone {
    margin: auto;
    width: 900px;
    height: 64px;
    border: 1px solid blue;
        background-color: white;
        overflow: hidden;
}
.movie_showtime {
    box-shadow: 3px 3px 3px #777777;
    border-radius: 8px;
}

.movie_showtime:active {
    box-shadow: none;
}

/* to make page long so that wishlist would not hide the bottom of move showtime list*/
.footer_placeholder {
    height: 64px;
    clear: both;
}
</style>

<style type="text/css">
.movie_info_box {
    position: absolute;
    background: #2a75a9;
    border: 2px solid #7EB5D6;
    padding: 7px;
    min-width: 100px;
    min-height: 30px;
    border-radius: 7px;
    box-shadow: 3px 3px 3px #777777;

    text-align: center;
    color: white;
}
.movie_info_box:after, .movie_info_box:before {
    top: 100%;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
}

.movie_info_box:after {
    /*border-color: rgba(42, 117, 169, 0);*/
    border-top-color: #2a75a9;
    border-width: 10px;
    left: 50%;
    margin-left: -10px;
}
.movie_info_box:before {
    /*border-color: rgba(126, 181, 214, 0);*/
    border-top-color: #7EB5D6;
    border-width: 13px;
    left: 50%;
    margin-left: -13px;
}
</style>

<style type="text/css" id="none_script_style">
    
.movie_showtime:hover:before {
    content: attr(title);
    color: #FFFFFF;
    font-size: 13px;
    padding-left:5px;
}

</style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<input type="checkbox" value="Toggle Title" id="toggle_title"/>
<label for="toggle_title" >Toggle Title</label>
 <?php
date_default_timezone_set('America/Los_Angeles');
$date = date('Y-m'). '-'. (date('d')+2);
$path = '/tmp/mit/'.$date;
$theater = null;
if (is_file($path)) {
    $content = file_get_contents($path);
    $theater = json_decode($content);
} else {
    $url = 'http://www.google.com/movies?tid=8aae557c9baf2f50&date=2';
    $page = file_get_contents($url);

    $theather = 'class=theater';

    $results = explode($theather, $page);
    array_shift($results);

    switch (count($results)) {
        case 0:
            echo "No theater results";
            return;
        break;
        
        case 1:
            //good
            break;

        default:
            echo "Find multiple theater results";
            return;
            break;
    }
    //remove foot content
    $results = explode('<p class=clear>', $results[0]);

    $movieInTheater = $results[0];

    // echo $movieInTheater;
    // echo htmlspecialchars($theater);

    include_once 'parser.php';

    $parser = new GoogleMovieParser();

    $theater = $parser->parse($movieInTheater);
    if ($theater) {
        file_put_contents($path, json_encode($theater));
    }

}

include_once 'displayer.php';

$displayer = new Displayer($theater);
$displayer->generate();
// echo '<pre>';
//  var_dump($theater);
//  echo '</pre>';
?>
<div class="footer_placeholder"> 
</div>
<div class="wishlist_outter_container">
    <div class="wish_zone">
        <div id="wishlist_container">
            <div id="wish_movie_container"></div>
        </div>
    </div>
</div>
<div id="movie_info_box" class="movie_info_box">
    <div style="white-space:nowrap;"></div>
    <div></div>
</div>
<script type="text/javascript">
(function($) {

    $('#none_script_style').remove();

    var titles = $('.movie_container').children('h3').on('click', function() {
        $(this).next().slideToggle();
    }),
        movieShowtimeContainers = $('.movie_showtime_container'),
        border = movieShowtimeContainers.css('border'),
        innerContainer = $('.movie_showtime_inner_container');

    $('#toggle_title').on('click', function() {
        if(this.checked) {
            titles.slideUp(function() {
                movieShowtimeContainers.css('border', 'none');
                innerContainer.css('border', '1px dashed white')
            });
            
        } else {
            titles.slideDown();
            movieShowtimeContainers.css('border', border);
            innerContainer.css('border', 'none');

        }
    });


    var wishlistContainer = $('#wishlist_container');
    var style = $('.movie_showtime_inner_container').attr('style');
    wishlistContainer.attr('style', style);

    var wishMovieContainer = $('#wish_movie_container');
    style = $('.movie_showtime_container').attr('style');
    wishMovieContainer.attr('style', style);

    wishMovieContainer.css({
        height: '16px',
        top: '32px',
    });
    // titles.slideUp(function() {
    //             movieShowtimeContainers.css('border', 'none');
    //             innerContainer.css('border', '1px dashed black')
    //         });
    wishMovieContainer.on('click', '.movie_showtime', function() {
        //move it to top
        var showtime = $(this);
        showtime.parent().append(showtime);
    });

    

    var movieShotimes = $('.movie_showtime');
    movieShotimes.css('cursor', 'pointer');

    var wishlist = {}; 
    movieShotimes.on('click', function() {

        var showtime = $(this);
            movieName = showtime.parent().attr('title'),
            identifier = showtime.data('showtime') + ' - '+ movieName;
        
        // console.log(identifier);
        if (wishlist.hasOwnProperty(identifier)) {
            wishlist[identifier].toggle();
            return;
        }

        var wishshowtime = showtime.clone();
        wishMovieContainer.append(wishshowtime);
        wishshowtime.css({top: 0});
        wishshowtime.data('moviename', movieName);

        wishlist[identifier] = wishshowtime;
    });
    var movieInfoBox = $('#movie_info_box').hide();
    var moveInfoNameLable = movieInfoBox.children().eq(0);
    var moveInfoShowtimeLable = movieInfoBox.children().eq(1);

    var lastHoveredShowtime = null;
    movieShotimes.on('mouseover', function() {
        movieInfoBox.stop(true);
        if (lastHoveredShowtime == this) return;
        lastHoveredShowtime = this;

        movieInfoBox.hide();
        var showtime = $(this),
            pos = showtime.position(),
            offset = showtime.offset();


        moveInfoNameLable.text(showtime.parent().attr('title'));
        moveInfoShowtimeLable.text(showtime.attr('title'));
        offset.top -= movieInfoBox.outerHeight()+10;
        offset.left -= (movieInfoBox.outerWidth()-showtime.outerWidth()) / 2;
        movieInfoBox.css(offset);
        movieInfoBox.fadeIn('fast');
    });

    movieShotimes.on('mouseout', function() {
        movieInfoBox.delay(2107).fadeOut();
    });

    wishMovieContainer.on('mouseover', '.movie_showtime', function() {
        movieInfoBox.hide();

        var showtime = $(this),
            pos = showtime.position(),
            offset = showtime.offset();

        moveInfoNameLable.text(showtime.data('moviename'));
        moveInfoShowtimeLable.text(showtime.data('showtime'));
        offset.top -= movieInfoBox.outerHeight()+10;
        offset.left -= (movieInfoBox.outerWidth()-showtime.outerWidth()) / 2;
        movieInfoBox.css(offset);
        movieInfoBox.fadeIn('fast');
    });


})(jQuery);

</script>




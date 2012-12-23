<html>
    <head>
        <title>Movies</title>
    </head>
    <link rel="stylesheet" type="text/css" href="./css/style.css" />

<style type="text/css" id="none_script_style">
    
.movie_showtime:hover:before {
    content: attr(title);
    color: #FFFFFF;
    font-size: 13px;
    padding-left: 10px;
}
</style>

<body>

<?php
include 'searchsubview.php';

include_once 'src/services/factory.php';
include_once 'src/services/displayer.php';

$source = '';
if (isset($_GET['source'])) {
    $source = $_GET['source'];
}

if (isset($_GET['tid'])) {
    $tid = $_GET['tid'];
}

// if (isset($_GET['date'])) {
//     $date = $_GET['date'];
// }

$fetcher = FetcherFactory::movieListFetcher($tid, new DateTime(), $source);

$list = $fetcher->movieList();
$displayer = new MovieListDisplayer($list);
$displayer->generate(); 
$displayer->show();

?>

<div class="footer_placeholder"> 
</div>
<div id="wishlist_outter_container" class="wishlist_outter_container">
    <div class="wish_zone">
        <div id="wishlist_container">
            <div id="wish_movie_container"></div>
        </div>
    </div>
</div>
<div id="movie_info_panel" class="movie_info_panel">
    <div id="movie_info_title"></div>
    <div id="movie_info_showtime"></div>
</div>
<script type="text/javascript">
(function($) {
    $('#none_script_style').remove();
    $('#wishlist_outter_container').show();

    var config = {  arrow:{position:'middle', direction:'down', pointto:'middle', size: 10},
                    animate: {show: 'show', hide: 'hide'},
        },
        panel = $.tip('#movie_info_panel', config),
        ext = {
                lastPointTo: null,
                title: $('#movie_info_title'),
                showtime: $('#movie_info_showtime'),
                setTitle: function(title, showtime) {
                    this.title.text(title);
                    this.showtime.text(showtime);
                },
                showAt: function(obj) {
                    if (this.lastPointTo == obj) return;
                    this.dismiss();
                    this.lastPointTo = obj;
                    
                    var showtime = $(obj);//.css('z-index', 99);
                    
                    this.setTitle(showtime.parent().data('moviename'), 
                                            showtime.data('showtime'));
                    this.pointTo(showtime);
                    this.fadeIn();
                },
                dismiss: function() {
                    // $(this.lastPointTo).css('z-index', '');
                    this.lastPointTo = null;
                    this.hide();
                }
        };
        $.extend(panel, ext);

    var lastHoveredShowtime = null;
    $('.movie_showtime').on('mouseover', function() {
        panel.showAt(this);
    });
    // .on('mouseout', function() {
    //     $(lastHoveredShowtime).css('z-index', '');
    //     panel.hide();
    // });

    $('body').on('click', function() {
        panel.dismiss();
    });



//////////following code need refactory////




    var movieShotimes = $('.movie_showtime').css('cursor', 'pointer');


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
        wishshowtime.css({top: 0});
        wishshowtime = $('<div></div>')
                        .data('moviename', movieName)
                        .append(wishshowtime);
        wishMovieContainer.append(wishshowtime);

        wishlist[identifier] = wishshowtime;
    });


var wishlistContainer = $('#wishlist_container');
    var style = $('.movie_list_inner_container').attr('style');
    wishlistContainer.attr('style', style);

    var wishMovieContainer = $('#wish_movie_container');
    style = $('.showtime_container').attr('style');
    wishMovieContainer.attr('style', style);

    wishMovieContainer.css({
        height: '16px',
        top: '24px',
    });
    // titles.slideUp(function() {
    //             movieShowtimeContainers.css('border', 'none');
    //             innerContainer.css('border', '1px dashed black')
    //         });
    wishMovieContainer.on('click', '.movie_showtime', function() {
        //move it to top
        var showtime = $(this).parent();
        showtime.parent().append(showtime);
    });

    wishMovieContainer.on('mouseover', '.movie_showtime', function() {
        var showtime = $(this);
        panel.showAt(this);
    });

})(jQuery);
</script>
</body>
</html>
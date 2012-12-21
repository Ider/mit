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
<div id="movie_info_panel" class="movie_info_panel">
    <div id="movie_info_title"></div>
    <div id="movie_info_showtime"></div>
</div>

<script type="text/javascript">
(function($) {
    $('#none_script_style').remove();

    var config = {  arrow:{position:'middle', direction:'down', pointto:'middle', size: 10},
                    animate: {show: 'show', hide: 'hide'},
        },
        panel = $.tip('#movie_info_panel', config);
        panel.title = $('#movie_info_title');
        panel.showtime = $('#movie_info_showtime');
        panel.setTitle = function(title, showtime) {
            this.title.text(title);
            this.showtime.text(showtime);
        };

    var lastHoveredShowtime = null;
    $('.movie_showtime').on('mouseover', function() {
        lastHoveredShowtime = this;
        var showtime = $(this).css('z-index', 99);

        panel.setTitle(showtime.parent().data('moviename'), showtime.data('showtime'));
        panel.showAt(showtime);
    })
    .on('mouseout', function() {
        $(lastHoveredShowtime).css('z-index', '');
        panel.hide();
    });



})(jQuery);
</script>
</body>
</html>
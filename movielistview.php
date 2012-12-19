<html>
    <head>
        <title>Movies</title>
    </head>
    <link rel="stylesheet" type="text/css" href="./css/style.css" />
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

</body>
</html>
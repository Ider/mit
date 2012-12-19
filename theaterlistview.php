<html>
    <head>
        <title>Theaters</title>
    </head>
    <link rel="stylesheet" type="text/css" href="./css/style.css" />
<body>
<?php

include_once 'src/services/factory.php';
include_once 'src/services/displayer.php';

$source = '';
if (isset($_GET['source'])) {
    $source = $_GET['source'];
}

if (isset($_GET['zipcode'])) {
    $zipcode = $_GET['zipcode'];
}

$fetcher = FetcherFactory::theaterListFetcher($zipcode, $source);

$list = $fetcher->theaterList();
$displayer = new TheaterListDisplayer($list);
$displayer->generate(); 
$displayer->show();

?>

</body>
</html>
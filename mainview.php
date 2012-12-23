<?php
include_once 'config.php';

if (isset($_GET['tid'])) {
    include 'movielistview.php';
    return;
} else if (isset($_GET['zipcode'])) {
    include 'theaterlistview.php';
    return;
} 

?>
        <title>Let's Movie Movie</title>

<link rel="stylesheet" type="text/css" href="./css/style.css" />
<link href='http://fonts.googleapis.com/css?family=Gorditas' rel='stylesheet' type='text/css'>
<div class="search_middle">
<div style="font-size:80px; font-family:'Gorditas'; text-align:center; margin-bottom: 40px;">Let's Movie Movie</div>

<?php
    include 'searchsubview.php';
?>

</div>

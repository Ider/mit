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

<link rel="stylesheet" type="text/css" href="./css/style.css" />
<div class="search_middle">

<?php
    include 'searchsubview.php';
?>

</div>

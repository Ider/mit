<?php

$loading_start = microtime(true);
include 'mainview.php';
$loading_end = microtime(true);

// error_log('loadingtime: '. ($loading_end - $loading_start));
<?php

date_default_timezone_set('America/Los_Angeles');

define('SITEURL', 'index.php');

define('DB_SERVER', 'DB_SERVER');
define('DB_USER', 'DB_USER');
define('DB_DATABASE', 'DB_DATABASE');
define('DB_PASSWORD', 'DB_PASSWORD');


//if this set to true, any request for movie and theater will try to fetch from
//local reservation, which could be database, file etc., and all data fetched 
//from out source will be saved to local reservation
define('ENABLE_RESERVATION', false);

<?php

define('ESA_DIR', 'yourdbapplicationpath');     // location of ESA app
define('ESA_DB_NAME', 'yourdbname');            // name of the database
define('ESA_DB_HOST', 'yourdbhost');            // host name of the server housing the database
define('ESA_DB_USER', 'yourdbuser');            // database user
define('ESA_DB_PASSWORD', 'yourdbpassword');    // database user's password
define('ESA_DB_PREFIX', 'yourdbtableprefix');   // table prefix
define('ESA_COOKIENAME', 'yourcookiename');     // ESA app cookie name
define('ESA_ABSOLUTEPATH', 'yourabsolutepath'); // url path of ESA app
define('ESA_SALT', 'yoursaltstring');           // salt string for users password hashing
define('ESA_IP_EXCLUSIONS', ['yourip']);        // ip list that you like to exclude from traffic monitoring
?>
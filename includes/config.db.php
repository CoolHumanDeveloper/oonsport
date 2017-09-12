<?php
//die('config.db.php');
if( $_SERVER['HTTP_HOST']=='dev-images.oon-sport.de'
    || $_SERVER['HTTP_HOST'] == 'dev.oon-sport.de' 
    ||$_SERVER['HTTP_HOST'] == 'en-dev.oon-sport.de' 
    || $_SERVER['HTTP_HOST'] == '192.168.2.106' ) { 

    error_reporting(1);
    ini_set("display_errors", 1);
    
define('DB_DEFAULT','oon_sport');

    define('SERVER_PATH','C:/xampp/htdocs/oon-sport.de/v1/');
    define('DB_USER','root');
    define('DB_PASSWORD','Ca290701!');
    define('SITE_URL',"http://".$_SERVER['HTTP_HOST']."/oon-sport.de/v1/");
    define('SERVER_IMAGE_PATH','C:/xampp/htdocs/oon-sport.de-images/');
    define('SITE_IMAGE_URL','http://'.$_SERVER['HTTP_HOST'].'/oon-sport.de-images/');
}
else if ($_SERVER['HTTP_HOST']=='localhost') {
    error_reporting(0);
    ini_set("display_errors", 0);
    define('SERVER_PATH','/srv/www/vhosts/oon-sport.de/httpdocs/oon-sport/v1/');
    define('DB_USER','os-dbadmin');
    define('DB_PASSWORD','qZdXWL6muT2E3B4M');
    define('SITE_URL',"https://www.oon-sport.de/");
    define('SERVER_IMAGE_PATH','/srv/www/vhosts/oon-sport.de/subdomain_images/');
    define('SITE_IMAGE_URL','https://images.oon-sport.de/');

    define('DB_DEFAULT','oon-sport2015');
}
else {
    error_reporting(0);
    ini_set("display_errors", 0);
    define('SERVER_PATH','/srv/www/vhosts/oon-sport.de/httpdocs/oon-sport/v1/');
    define('DB_USER','os-dbadmin');
    define('DB_PASSWORD','qZdXWL6muT2E3B4M');
    define('SITE_URL',"https://www.oon-sport.de/");
    define('SERVER_IMAGE_PATH','/srv/www/vhosts/oon-sport.de/subdomain_images/');
    define('SITE_IMAGE_URL','https://images.oon-sport.de/');
    
define('DB_DEFAULT','oon-sport2015');
}


ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');


define('DB_GEO','geodb_world'); // Datenbank für GEO Daten
define('MODULE_PATH',SERVER_PATH . 'includes/modules/');
define('SITE_NAME',"oon-sport.de");
define("IMAGE_MAX_DEFAULT_SIZE","1170x1170");

require(SERVER_PATH . "includes/functions/db.php");

$DB = db_start();
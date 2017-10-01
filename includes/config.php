<?php
ini_set("date.timezone","Europe/Berlin");
ini_set("session.cookie_domain",".oon-sport.de");

$globalLanguage = array();
$selectLanguageValue = array(   'de' => 'Deutsch',
                                'en' => 'English',
                                'es' => 'Español',
                                'fr' => 'Français',
                                'fi' => 'Suomi',// Finish
                                'pt' => 'Português',
                                'dk' => 'Dansk',
                                'it' => 'Italiano',
                             
                             'sv' => 'svenska',// Swedish
                             'nl' => 'Nederlands',// Finish
                             'ru' => 'Русский',// Russisch
                             'pl' => 'Polski',// Finish
                             'cz' => 'český',// Tschechisch
                             'ar' => 'العربية',// Arabisch
                            'gr' => 'ελληνικά',// griechisch
                             'th' => 'ไทย',// Thailändisch
                             'tk' => 'Türkçe',// Türkisch
                             'no' => 'Norsk',// norwegisch
                             'cn' => '中国',// chinesisch
                             'jp' => '日本語',// japanisch
                             
                            
                            ); 
foreach($selectLanguageValue as $sLanguage => $sLanguageValue) {
    if($sLanguage!= 'de') {
        $globalLanguage[] = $sLanguage;    
    }
}

if( stristr($_SERVER['HTTP_HOST'], 'dev.oon-sport')== true || $_SERVER['HTTP_HOST'] == 'dev-images.oon-sport.de' 
    || $_SERVER['HTTP_HOST'] == '192.168.2.106' ) {
	define('SERVER_PATH','C:/xampp/htdocs/oon-sport.de/v1/');
	include (SERVER_PATH . "includes/functions/locate.php");
	
	$skriptstart = microtime(true); 
	
	error_reporting(0);
	ini_set("display_errors", 0);

	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	ini_set("display_errors", 1);

	define('DE_SITE_URL',"http://dev.oon-sport.de/");
    foreach($globalLanguage as $gLanguage) {
        define(strtoupper($gLanguage) . '_SITE_URL',"http://" . $gLanguage . "-dev.oon-sport.de/");
    }
    
	define('GOOGLE_ANALYTICS_ID','');
    
    
    $languagePrefix = explode('-dev.', $_SERVER['HTTP_HOST']);
    $languagePrefix = $languagePrefix[0];
	if(in_array($languagePrefix, $globalLanguage) || force_en_redirect() === true){
        define('SITE_URL', constant(strtoupper($languagePrefix) . '_SITE_URL'));
		define('DEFAULT_LANGUAGE', $languagePrefix);
        if($languagePrefix == 'en') {
            define('RADIUS',3961);
        } else {
           define('RADIUS',6375); 
        }
	}
    else {
        define('SITE_URL',"http://".$_SERVER['HTTP_HOST']."/");
        define('DEFAULT_LANGUAGE','de');
        define('RADIUS',6375);
    }

    define('DB_USER','root');
    define('DB_PASSWORD','Ca290701!');
    define('DB_DEFAULT','oon_sport');
    
    define('SERVER_IMAGE_PATH','C:/xampp/htdocs/oon-sport.de-images/');
    define('SITE_IMAGE_URL','http://dev-images.oon-sport.de/');
    
    
}
else if($_SERVER['SERVER_PORT'] == 443) {
	define('SERVER_PATH','/srv/www/vhosts/oon-sport.de/httpdocs/oon-sport/v1/');
	include(SERVER_PATH . "includes/functions/locate.php");
	
	error_reporting(0);
	ini_set("display_errors", 0);
	define('DB_USER','os-dbadmin');
	define('DB_PASSWORD','qZdXWL6muT2E3B4M');
	define('DE_SITE_URL',"https://www.oon-sport.de/");
    
    foreach($globalLanguage as $gLanguage) {
        define(strtoupper($gLanguage) . '_SITE_URL',"https://" . $gLanguage . ".oon-sport.de/");
    }
	
    $languagePrefix = explode('.', $_SERVER['HTTP_HOST']);
    $languagePrefix = $languagePrefix[0];
    
	if(in_array($languagePrefix, $globalLanguage) || force_en_redirect() === true){
		define('SITE_URL', constant(strtoupper($languagePrefix) . '_SITE_URL'));
		define('DEFAULT_LANGUAGE', $languagePrefix);
        if($languagePrefix == 'en') {
            define('RADIUS',3961);
            define('GOOGLE_ANALYTICS_ID','UA-73893360-2');
        } else {
           define('RADIUS',6375); 
            define('GOOGLE_ANALYTICS_ID','');
        }
	}
	else {
		define('SITE_URL',DE_SITE_URL);
		define('DEFAULT_LANGUAGE','de');
		define('RADIUS',6375);
		define('GOOGLE_ANALYTICS_ID','UA-73893360-1');
	}
    
	
	define('SERVER_IMAGE_PATH','/srv/www/vhosts/oon-sport.de/subdomain_images/');
	define('SITE_IMAGE_URL','https://images.oon-sport.de/');
	define('DB_DEFAULT','oon-sport2015');
} else if( $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.0.116' ) {
	define('SERVER_PATH','D:/xampp7/htdocs/oonsrc/');
    include (SERVER_PATH . "includes/functions/locate.php");

    $skriptstart = microtime(true);

    error_reporting(0);
    ini_set("display_errors", 0);

    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set("display_errors", 1);

    define('LOCALHOST_SITE_URL',"http://" . $_SERVER['HTTP_HOST'] . "/oonsrc/");
    foreach($globalLanguage as $gLanguage) {
        define(strtoupper($gLanguage) . '_SITE_URL', LOCALHOST_SITE_URL);
    }

    define('GOOGLE_ANALYTICS_ID','');

    $languagePrefix = 'en';
    define('SITE_URL', LOCALHOST_SITE_URL);
    define('DEFAULT_LANGUAGE', 'en');
    define('RADIUS',3961); // 6375

    define('DB_USER','root');
    define('DB_PASSWORD','');
    define('DB_DEFAULT','oon_sport');

    define('SERVER_IMAGE_PATH','D:/xampp7/htdocs/oonsrc/images/');
    define('SITE_IMAGE_URL','http://' . $_SERVER['HTTP_HOST'] . '/oonsrc/images/');
}
else {
	define('SERVER_PATH','/srv/www/vhosts/oon-sport.de/httpdocs/oon-sport/v1/');
	include(SERVER_PATH . "includes/functions/locate.php");
	
	if(force_en_redirect() === true) {
		define('SITE_URL',"https://en.oon-sport.de/?redirect=1");
	}
	else {
		define('SITE_URL',"https://www.oon-sport.de/?redirect=1");
	}
	header("HTTP/1.1 301 Moved Permanently"); 
	header("location: ".SITE_URL); 
}


ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');

define('DB_GEO','geodb_world'); // Datenbank für GEO Daten
define('MODULE_PATH',SERVER_PATH . 'includes/modules/');
define('SITE_NAME',"OON-Sport");
define('MOBILE_DETECT_CLASS',SERVER_PATH . 'includes/classes/Mobile-Detect-2.8.12/Mobile_Detect.php');

define('PHP_MAILER_CLASS',SERVER_PATH . 'includes/classes/PHPMailer_5.2.2/class.phpmailer.php');

define('SYSTEM_MAIL_SERVER',"smtp.1und1.de");
define('SYSTEM_MAIL',"support@oon-sport.de");
define('SYSTEM_MAIL_PW','pferdefussmatte');

define('COOKIE_TIME',3600*24*30);

define('VIEW_PER_PAGE',20);
define('DEFAULT_DISTANCE_SEARCH',0); //0,10,25,50,100,200
define('DEFAULT_DISTANCE_SEARCH_ADVANCED',0); //0,10,25,50,100,200
define('PW_LENGTH',6);
define('REGISTER_MIN_YEARS',7);
define('SEARCH_MAX_GROUPS',10);
define('SEARCH_MAX_GROUPS_PREVIEW',5);

define('KEEP_MARKETPLACE_DAYS', 30);

define('PROFILE_MAX_IMAGES',6);

define("IMAGE_MAX_DEFAULT_SIZE","1170x1170");
define('IMAGE_TYPES',"html"); // html <img> oder plain Url

require(SERVER_PATH . "includes/functions/db.php");
$DB = db_start();

require(SERVER_PATH . "includes/functions/default.php");
require(SERVER_PATH . "includes/functions/form.php");

require(SERVER_PATH . "includes/functions/images.php");
require(SERVER_PATH . "includes/functions/session.php");

require(SERVER_PATH . "includes/functions/messages.php");
require(SERVER_PATH . "includes/functions/shoutbox.php");
require(SERVER_PATH . "includes/functions/group.php");

// DEFAULT SETTINGS_SQL
define("DEFAULT_SETTINGS_SQL","INSERT INTO `user_settings` (`user_id`, `settings_key`, `settings_value`, `settings_type`) VALUES
	(#USERID#, 'emailcopy_on_friendship_accept', '1', 'emailcopy'),
	(#USERID#, 'emailcopy_on_friendship_request', '1', 'emailcopy'),
	(#USERID#, 'emailcopy_on_group_message', '1', 'emailcopy'),
	(#USERID#, 'emailcopy_on_group_request', '1', 'emailcopy'),
	(#USERID#, 'emailcopy_on_message', '1', 'emailcopy'),
	(#USERID#, 'email_newsletter', '1', 'email'),
	(#USERID#, 'email_searches', '1', 'email'),
	(#USERID#, 'shoutbox_display_radius', '50', 'shoutbox'),
	(#USERID#, 'shoutbox_display_sports', '0', 'shoutbox'),
    (#USERID#, 'shoutbox_display_marketplace', '0', 'shoutbox'),
	(#USERID#, 'shoutbox_display_friends', '0', 'shoutbox'),
    (#USERID#, 'emailcopy_on_marketplace', '1', 'emailcopy');");

	
if($_SESSION['logged_in'] == 0) {
    // init new register steps
    if(!isset($_SESSION['register_step'])) {
         $_SESSION['register_step']=1;
    }

     // handle register_steps
    require(SERVER_PATH . "includes/functions/session_register.php");
}
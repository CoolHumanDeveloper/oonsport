<?php

session_start();
if(isset($_GET['logout']) && $_GET['logout'] == true) {
	$sql = "DELETE FROM user_sessions WHERE session_id='".$_COOKIE['oon-sid']."' OR session_id='".session_id()."' ";
	
    $query = $DB->prepare($sql);
    $query->execute();
	
	setcookie("oon-site", "", time()-3600, '/');
	setcookie("oon-sid", "", time()-3600, '/');
	setcookie("oon-userid", "", time()-3600, '/');

	unset($_SESSION);
	unset($_COOKIE);
	
	session_destroy();
	session_start();
    $_SESSION['system_message'] .= set_system_message("success", TEXT_LOGOUT_SUCCESS);
	header("Location: ".SITE_URL."login/");

}

//SWITCHING THE LANGUAGE
$_SESSION['language_code'] = DEFAULT_LANGUAGE;

// Texte werden geladen
$sql = "SELECT * FROM text WHERE language_code='".$_SESSION['language_code']."'";

$query = $DB->prepare($sql);
$query->execute();
$get_language = $query->fetchAll();


foreach ($get_language as $text_language) {
	define("TEXT_".$text_language['text_key'],$text_language['text_value']);
}

if(isset($_SESSION['system_temp_message'])) {
    $_SESSION['system_message'] = $_SESSION['system_temp_message'];
    unset($_SESSION['system_temp_message']);
}


if(!isset($_SESSION['user']['secure_spam_key']) || $_SESSION['user']['secure_spam_key'] == "") {
	$_SESSION['user']['secure_spam_key']=rand(123,999);
	$_SESSION['logged_in']=0;
}

// DEVICE DETECTION
if(!isset($_SESSION['user']['device'])) {
	require(MOBILE_DETECT_CLASS);
	$detect = new Mobile_Detect;
	$isMobile = $detect->isMobile();
    $isTablet = $detect->isTablet();
	$_SESSION['user']['device']= ($isMobile ? ($isTablet ? 'tablet' : 'mobile') : 'classic');
}

//Standard Listenansicht
if(!isset($_SESSION['user']['default_view']) || $_SESSION['user']['default_view'] == '') {
    $_SESSION['user']['default_view'] = 'grid';
}

// Reaktivieren einer gespeicherten SESSION

if($_SESSION['logged_in'] == 0 && isset($_COOKIE['oon-sid'])) {
	$sql = "SELECT * FROM user u, user_details ud, user_sessions us WHERE u.user_id=ud.user_id AND us.user_id=u.user_id AND us.session_id='".$_COOKIE['oon-sid']."' AND u.user_status=1 LIMIT 1";	
	//echo $sql;
    
    $query = $DB->prepare($sql);
    $query->execute();
    $get_reakt_user = $query->fetch();

    $temp_spam_key = $_SESSION['user']['secure_spam_key'];
	$temp_user_device = $_SESSION['user']['device'];
	
	$_SESSION['user'] = $get_reakt_user;
	
	if($_SESSION['user']['user_id']) {
		$_SESSION['logged_in']=1;
		$_SESSION['user']['secure_spam_key']=$temp_spam_key;
		$_SESSION['user']['device']=$temp_user_device;
		
		build_history_log($_SESSION['user']['user_id'],"session_recovered","");
		
		// SESSION erneuern
		$sql = "UPDATE `user_sessions` SET `session_expires`='".date("Y-m-d H:i:s",time()+COOKIE_TIME)."' WHERE  `user_id`='".$_SESSION['user']['user_id']."' AND `session_id`='".$_COOKIE['oon-sid']."'";
        $query = $DB->prepare($sql);
        $query->execute();
	}
	else {
        $sql = "DELETE FROM user_sessions WHERE session_id='".$_COOKIE['oon-sid']."' OR session_id='".session_id()."' ";

        $query = $DB->prepare($sql);
        $query->execute();
        unset($_SESSION);

        setcookie("oon-site", "", time()-3600, '/');
        setcookie("oon-sid", "", time()-3600, '/');
        setcookie("oon-userid", "", time()-3600, '/');

        unset($_COOKIE);
        session_destroy();
        session_start();
        $_SESSION['system_message'] .= set_system_message("error", TEXT_LOGOUT_AUTOMATIC);
        header("Location: ".SITE_URL."login/");
	}
	
	
}


if($_SESSION['logged_in']==1 && $_SESSION['user']['user_id']!=0) {
	$sql = "SELECT * FROM user u, user_details ud WHERE u.user_id=ud.user_id AND u.user_status=1 AND u.user_id='".$_SESSION['user']['user_id']."' LIMIT 1";
    
    if(!isset($_SESSION['user']['user_uptime'])) {
        $_SESSION['user']['user_uptime'] = time();
    }
    
    $query = $DB->prepare($sql);
    $query->execute();
    $get_user = $query->fetch();
	
	$temp_spam_key = $_SESSION['user']['secure_spam_key'];
	$temp_user_device = $_SESSION['user']['device'];
	$temp_uptime = $_SESSION['user']['user_uptime'];
	
	$_SESSION['user'] = $get_user;
        
	if(!$_SESSION['user']['user_id']) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_LOGIN_ERROR_INVALID_SESSION);
	}
	
	$_SESSION['user']['secure_spam_key']=$temp_spam_key;
	$_SESSION['user']['device']=$temp_user_device;
	$_SESSION['user']['user_uptime'] = $temp_uptime;
}
else {
    $_SESSION['logged_in']=0;
}

// Login Prozess
if(isset($_POST['login_name'.$_SESSION['user']['secure_spam_key']]) 
    && $_POST['login_name'.$_SESSION['user']['secure_spam_key']] != ''
    && isset($_POST['login_password'.$_SESSION['user']['secure_spam_key']]) 
    && $_POST['login_password'.$_SESSION['user']['secure_spam_key']] != '') {
	//echo "SESSION PROCESS";

	$sql = "SELECT * FROM user u, user_details ud WHERE u.user_id=ud.user_id AND u.user_email='".$_POST['login_name'.$_SESSION['user']['secure_spam_key']]."' AND u.user_password='".md5($_POST['login_password'.$_SESSION['user']['secure_spam_key']])."' AND u.user_status=1  LIMIT 1";

    $query = $DB->prepare($sql);
    $query->execute();
    $get_user = $query->fetch();
    
	$temp_spam_key = $_SESSION['user']['secure_spam_key'];
	$temp_user_device = $_SESSION['user']['device'];
	

	$_SESSION['user'] = $get_user;
        
	if(!$_SESSION['user']['user_id']) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_LOGIN_ERROR_INVALID_USER_PW);
				
		//SPAM KEY WIEDERHERSTELLEN
		$_SESSION['user']['secure_spam_key']=$temp_spam_key;
		$_SESSION['user']['device']=$temp_user_device;
		
	}
	else {
		$_SESSION['system_message'] .= set_system_message("success",TEXT_LOGIN_ERROR_SUCCESS_LOGIN);
		$_SESSION['logged_in']=1;
        
        /*if(count_total_media($_SESSION['user']['user_id']) == 0) {
            $_SESSION['system_message'] .= set_system_message("info",TEXT_LOGIN_MISSING_IMAGE);
        }*/
		
		build_history_log($_SESSION['user']['user_id'],"logged_in","");
		
		//SPAM KEY WIEDERHERSTELLEN
		$_SESSION['user']['secure_spam_key']=$temp_spam_key;
		$_SESSION['user']['device']=$temp_user_device;
		
		// Cookie Setzen
		if($_POST['login_remember'.$_SESSION['user']['secure_spam_key']]==1) {
			$cookie_time=time()+(COOKIE_TIME);
			setcookie("oon-site", SITE_URL, $cookie_time, '/');
			setcookie("oon-sid", session_id(), $cookie_time, '/');
			setcookie("oon-userid", $_SESSION['user']['user_id'], $cookie_time, '/');
			//setcookie("oon-expire", time()+(3600*24*30));
			
			//store Session
			$sql = "INSERT INTO `user_sessions` (`user_id`, `session_id`, `session_start`, `session_expires`, `session_viewport`, `session_device`) VALUES ('".$_SESSION['user']['user_id']."', '".session_id()."', NOW(), '".date("Y-m-d H:i:s",$cookie_time)."', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SESSION['user']['device']."')";
            $query = $DB->prepare($sql);
            $query->execute();
		}
		
		if($_SESSION['logged_in']==1 && isset($_SESSION['temp_url_redirect'])) {
            $redirect = $_SESSION['temp_url_redirect'];
            unset($_SESSION['temp_url_redirect']);

            header("Location: ".$redirect);
            die();
        }
		header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
		die();
	}

}


function is_user() {
	if($_SESSION['logged_in'] == 0) {
		$_SESSION['temp_url_redirect']= "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		header("Location: ".SITE_URL."register/");
		die();
	}
	else {
		if(!isset($_SESSION['user']['user_uptime'])) {
		$_SESSION['user']['user_uptime']=time();
		build_user_online($_SESSION['user']['user_id']);
		}
		else {
			if($_SESSION['user']['user_uptime'] < time()-3600) {
				$_SESSION['user']['user_uptime']=time();
				build_user_online($_SESSION['user']['user_id']);
			}
		}
		
	}
}
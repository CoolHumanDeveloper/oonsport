<?php

function force_en_redirect() {
	
	
	session_start();
/*	var_dump($_SESSION['language_code']);
	var_dump(is_bot());
	var_dump(browser_language());
	die();*/
	
	if(!isset($_SESSION['language_code']) && is_bot() === false && stristr(browser_language(),'de') == false) {
		return true;
	}
}


function is_bot($user_agent = NULL) {

	if(is_null($user_agent)) $user_agent = $_SERVER['HTTP_USER_AGENT'];
	$ROBOT_USER_AGENTS= array (
	'check_http',
	'nagios',
	'slurp',          
	'archive',
	'crawl',
	'bot',
	'spider',
	'search',
	'find',
	'rank',
	'java', 
	'wget',
	'curl',
	'Commons-HttpClient',
	'Python-urllib',
	'libwww',
	'httpunit',
	'nutch',
	'teoma', 
	'webmon',
	'httrack',
	'convera',
	'biglotron',
	'grub.org',
	'speedy',
	'fluffy',
	'bibnum.bnf',
	'findlink',
	'panscient',
	'IOI',
	'ips-agent',
	'yanga',
	'yandex',
	'Voyager',
	'CyberPatrol',
	'page2rss',
	'linkdex',
	'ezooms',
	'mail.ru',
	'heritrix',
	'Aboundex',
	'summify',
	'facebookexternalhit',
	'yeti',
	'RetrevoPageAnalyzer',
	'sogou',
	'wotbox',
	'ichiro',
	'drupact',
	'coccoc',
	'integromedb',
	'siteexplorer.info',
	'proximic',
	'changedetection',
	'ZmEu',
	'Novalnet',
	'COMODO',
	'Drupal',
	'facebook',
	'analytics',
	'PayPal',
	'revolt'
	);
	
	$returnval = FALSE;
	foreach($ROBOT_USER_AGENTS as $needle) {
		$pos = stripos($user_agent, $needle);
		if ($pos !== false) {
			$returnval = TRUE;
		}
	}
	return $returnval;
}

function browser_language($default = "de-de") {
    if(!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) || empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
        return $default;
    }
 
    $accepted = preg_split("{,\s*}", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    $language = $default;
    $quality = 0;
 
    if(is_array($accepted) && (count($accepted) > 0)) {
        foreach($accepted as $key => $value) {
            if(!preg_match("{^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$}i", $value, $matches)) {
                continue;
            }
 
            $code = explode("-", $matches[1]);
 
            if(isset($matches[2])) {
                $priority = floatval($matches[2]);
            } else {
                $priority = 1.0;
            }
 
            while(count($code) > 0) {
                if($priority > $quality) {
                    $language = strtolower(implode("-", $code));
                    $quality = $priority;
 
                    break;
                }
 
                break;
            }
        }
    }
	
	$language=explode("-",$language);
 
    return $language[0];
}
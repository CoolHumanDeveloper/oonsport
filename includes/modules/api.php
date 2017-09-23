<?php
define('DEBUG_MODE', false);
require ("api/functions.php");
$content = file_get_contents("php://input");
if ($content) {
//    debug("====" . __LINE__);
    $params = json_decode($content, true);
} else {
//    debug("====" . __LINE__);
    $params = $_REQUEST;
}

//debug("====" . __LINE__);
//debug($params);
extract($params);
$response = array();

require ("api/constants.php");
require ("api/jwt/src/JWT.php");

$jwt = new \Firebase\JWT\JWT();
$apiname = $_GET['content_value'];
//debug("==$apiname==" . __LINE__);
switch($apiname){
    case "getdata":
    case "search":
    case "get_marketplaces":
    case "get_shoutboxs":
    case "add_shoutbox":
    case "add_marketplace":
    case "del_shoutbox":
    case "reactivate_shoutbox":
    case "get_mails":
    case "create_mail":
    case "del_mail":
    case "get_blockedusers":
    case "set_blockuser":
    case "set_unblockuser":
    case "recover_mail":
    case "profile":
    case "update_profile":
    case "update_password":
    case "update_email":
    case "get_profiles":
    case "update_settings":
    case "update_settings_shoutbox":
    case "update_settings_marketplace":
        if (!isset($token))
        {
            header(HEADER_UNAUTHORIZED);
            die(json_encode(array("error" => MISSING_TOKEN)));
        } else {
            $infos = $jwt::decode($token, JWT_KEY, array(JWT_ALG));
            if ($infos == null || !isset($infos->email)) {
                header(HEADER_UNAUTHORIZED);
                die(json_encode(array("error" => INVALID_TOKEN)));
            }
        }
    case "login":
    case "register":
    case "chk_nickname":
    case "chk_email":
    case "get_countries":
    case "get_sportgroup":
    case "get_sportsubgroup":
    case "chk_logindata":
        require(MODULE_PATH . "api/$apiname.php");
        break;
	  
	default:
        require(MODULE_PATH . "api/notfound.php");
    break;
}
//debug("====" . __LINE__);
$response['code'] = SUCCESS;
die(json_encode($response));
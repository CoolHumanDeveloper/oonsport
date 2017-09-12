<?php
$content = file_get_contents("php://input");
if ($content)
    $params = json_decode($content);
else
    $params = $_REQUEST;

extract($params);
$response = array();

require ("api/constants.php");
require ("api/functions.php");
require ("api/jwt/src/JWT.php");

$jwt = new \Firebase\JWT\JWT();
$apiname = $_GET['content_value'];
switch($apiname){
    case "getdata":
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
    case "reigister":
    case "chk_nickname":
    case "chk_email":
    case "get_countries":
        require(MODULE_PATH . "api/$apiname.php");
        break;
	  
	default:
        require(MODULE_PATH . "api/notfound.php");
    break;
}
$response['code'] = SUCCESS;
die(json_encode($response));
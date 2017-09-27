<?php
if (
    !isset($switchprofile_user_id) || $switchprofile_user_id == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM user_details WHERE user_id=" . $user['user_id'];
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

    if(api_isValidUserParent($switchprofile_user_id, $user) == true) {
        $sql = "SELECT u.*, ud.* FROM 
					user u
					 LEFT JOIN user_details AS ud ON u.user_id = ud.user_id
					 WHERE 
					 MD5(CONCAT(u.user_id,ud.user_nickname)) = '".$_POST['switchprofile_user_id_'.$_SESSION['user']['secure_spam_key']]."' LIMIT 1";
        $query = $DB->prepare($sql);
        $query->execute();
        $user_profile = $query->fetch();

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

        $_SESSION['user']=$user_profile;
        $_SESSION['user']['secure_spam_key']=rand(123,999);
        $_SESSION['user']['user_uptime']=time();
        build_user_online($_SESSION['user']['user_id']);
        $_SESSION['logged_in']=1;

        header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
        die();
    }

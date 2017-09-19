<?php
if (!isset($register_type) || !$register_type
    || !isset($nickname) || trim($nickname) == ""
    || !isset($password) || trim($password) == ""
    || !isset($email) || trim($email) == ""
    || !isset($firstname) || trim($firstname) == ""
    || !isset($lastname) || trim($lastname) == ""
    || (!isset($gender) || $gender == "") && $register_type != 3 && $register_type != 4
    || !isset($dob) || trim($dob) == ""
    || !isset($country) || trim($country) == ""
    || (!isset($geo) || trim($geo) == "") && (!isset($place) || trim($place) == "")
    || !isset($groups) || trim($groups) == ""
    || !isset($profession) || trim($profession) == ""
    || (!isset($groups_status) || $groups_status == "") && $_SESSION['register_type'] != 3 && $_SESSION['register_type'] != 4
    || !isset($groups_handycap) || trim($groups_handycap) == ""
) {
    header(HEADER_SERVERERR);
    $response['error'] = MISSING_PARAMETER;
    die(json_encode($response));
}

if (check_user_nickname($nickname)) {
    header(HEADER_SERVERERR);
    $response['code'] = DUPLICATED_NICKNAME;
    die(json_encode($response));
}

if (strlen($_POST['register_nickname']) < 3) {
    header(HEADER_SERVERERR);
    $response['code'] = SHORT_NICKNAME;
    die(json_encode($response));
}

if (check_email($email)) {
    if (check_user_email($email))
    {
        header(HEADER_SERVERERR);
        $response['code'] = DUPLICATED_EMAIL;
        die(json_encode($response));
    }
} else {
    header(HEADER_SERVERERR);
    $response['code'] = INVALID_EMAIL;
    die(json_encode($response));
}

if (strtotime($_POST['register_dob']) > strtotime("- " .REGISTER_MIN_YEARS . "years")) {
    if($_SESSION['register_type'] < 3) {
        header(HEADER_SERVERERR);
        $response['code'] = INVALID_DOB;
        die(json_encode($response));
    }
}

$auth_key = md5($password . $email . $register_type . time() . rand(0, 999));

$sql = "INSERT INTO `user` (`user_password`, `user_email`, `user_type`, `user_status`, `user_auth_key`, user_register_date) VALUES (md5('$password'), '$email', '$register_type', 0, '$auth_key', NOW())";
$query = $DB->prepare($sql);
$query->execute();
$user_id = $DB->lastInsertId();

if (!$user_id > 0) {
    header(HEADER_SERVERERR);
    $response['code'] = FAIL_USER;
    die(json_encode($response));
}

$dob = date("Y-m-d", strtotime($dob));
$sql = "INSERT INTO `user_details` (`user_id`, `user_nickname`, `user_firstname`, `user_lastname`, `user_dob`, `user_gender`, user_country, `user_geo_city_id`) VALUES ('$user_id', '$nickname', '$firstname', '$lastname', '$dob', '$gender', '$country', '$place');";
$query = $DB->prepare($sql);
$query->execute();

$group_value = 0;
for ($i = 3; $i >= 1; $i--)
{
    if (${"groups$i"} != '') {
        $group_value = ${"groups$i"};
        break;
    }
}

$sql = "INSERT INTO `user_to_sport_group_value` (`user_id`, `sport_group_id`, `sport_group_value_id`, sport_group_profession, sport_group_handycap, sport_group_in_club) VALUES ('$user_id', '$groups', '$group_value', '$profession', '$groups_handycap', '$groups_status')";
$query = $DB->prepare($sql);
$query->execute();

$sql = "INSERT INTO `user_profile` (user_id) VALUES ('$user_id')";
$query = $DB->prepare($sql);
$query->execute();

// SET DEFAULT USER SETTINGS
$sql = str_replace("#USERID#", $user_id, DEFAULT_SETTINGS_SQL);
$query = $DB->prepare($sql);
$query->execute();

//require(PHP_MAILER_CLASS);
$email_content_array = array(
    "NAME" => $firstname,
    "AUTHKEY" => $auth_key
);
$email_content_template = email_content_to_template("register", $email_content_array, '');

if (sending_email($email, $firstname, "Registrierung auf " . SITE_NAME, $email_content_template, '',0) == true) {
    build_history_log($user_id, "register");
} else {
    header(HEADER_SERVERERR);
    $response['code'] = FAIL_SENDMAIL;
    die(json_encode($response));
}
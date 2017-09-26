<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($device_id) || $device_id == ""
    || !isset($device_kind) || $device_kind == ""
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$query = $DB->prepare("show tables");
$query->execute();
$tables = $query->fetchAll(PDO::FETCH_ASSOC);
$found = false;
foreach($tables as $table){
    if ($table['Tables_in_oon_sport'] == "user_device")
        $found = true;
}
if (!$found){
    $query = $DB->query("CREATE TABLE `user_device` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `device_kind` varchar(16) NOT NULL,
      `device_id` varchar(256) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    $query->execute();
}

$query = $DB->prepare("select * from user_device where user_id='" . $user['user_id'] . "' and device_id='$device_id'");
$query->execute();
$devices = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($devices) == 0)
{
    $query = $DB->prepare("insert into user_device (user_id, device_kind, device_id) values ('" . $user['user_id'] . "', '$device_kind', '$device_id')");
    $query->execute();
}
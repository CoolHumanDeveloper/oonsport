<?php
$sql = "SELECT * FROM all_countries";
$query = $DB->prepare($sql);
$query->execute();
$languages = $query->fetch();

$response['languages'] = $languages;

$response['data'] = $infos;
die(json_encode($response));
?>
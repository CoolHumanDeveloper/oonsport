<?php
$sql = "SELECT * FROM all_countries";
$query = $DB->prepare($sql);
$query->execute();
$countries = $query->fetch();

$response['countries'] = $countries;
?>
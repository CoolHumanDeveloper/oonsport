<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */
$lang = isset($lang) ? $lang : 'en';
$allSportGroupString = getGroupsNamedAll();

$sql = "SELECT * FROM sport_group sg, sport_group_details sgd WHERE sg.sport_group_id=sgd.sport_group_id AND sgd.language_code='$lang' AND sg.sport_group_id != 0 ORDER BY FIELD(sgd.sport_group_name, " . $allSportGroupString . ") DESC, sgd.sport_group_name";

$query = $DB->prepare( $sql );
$query->execute();
$sportgroups = $query->fetchAll(PDO::FETCH_ASSOC);

$response['sportgroups'] = $sportgroups;
<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/18/2017
 * Time: 8:29 AM
 */

if (!isset($groupid) || !isset($groupid)) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$lang = isset($lang) ? $lang : 'en';
$sub_groupid = isset($sub_groupid) ? $sub_groupid : 0;

$sql="SELECT * FROM sport_group_value sg, sport_group_value_details sgd WHERE sg.sport_group_value_id=sgd.sport_group_value_id AND sgd.language_code='$lang'
        AND sport_group_id='$groupid'
        AND sport_group_sub_of='$sub_groupid'

        ORDER BY 
        FIELD(sgd.sport_group_value_name,'Alle','All') DESC, sgd.sport_group_value_name";

$query = $DB->prepare($sql);
$query->execute();
$sportsubgroups = $query->fetchAll(PDO::FETCH_ASSOC);

$response['sportsubgroups'] = $sportsubgroups;
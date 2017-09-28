<?php
if (!isset($sport_groups_0) || $sport_groups_0 == ""
    || !isset($profession) || $profession == ""
    || !isset($handycap)
) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

$sql = "select * from user where user_email='{$infos->email}'";
$query = $DB->prepare($sql);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (isset($infos->used_in_profile))
    $used_in_profile_id = $infos->used_in_profile;
$sql = "SELECT * FROM user_details WHERE user_id=" . $used_in_profile_id;
$query = $DB->prepare($sql);
$query->execute();
$user_detail = $query->fetch(PDO::FETCH_ASSOC);
unset($user_detail['user_id']);
foreach($user_detail as $key => $ud)
    $userinfo[$key] = $ud;

for($sg_x = 3; $sg_x >=0; $sg_x--) {
    $group_var = "sport_groups_$sg_x";
    if(isset($$group_var) && $$group_var != 0) {
        $selected_sport_group_value = $$group_var;

        if($selected_sport_group_value == $sport_groups_0) {
            $selected_sport_group_value=0;
        }

        $sql = "INSERT INTO `user_to_sport_group_value` (`user_id`, `sport_group_id`, `sport_group_value_id`, sport_group_profession, sport_group_handycap) VALUES ('$used_in_profile_id', '$sport_groups_0', '".$selected_sport_group_value."', '$profession', '$handycap')";
        $query = $DB->prepare($sql);
        $query->execute();

        break;
    }
}

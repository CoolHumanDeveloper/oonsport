<?php
require("../../includes/config.php");

if($_GET['search_groupid']) {
    $sub_query="";
    $sub_query_sep="";
    if(isset($_GET['search_more_sport'])) {
        if(count($_GET['search_more_sport']) > 0) {
            $sub_query="AND sg.sport_group_value_id NOT IN (";
            foreach($_GET['search_more_sport'] as $sms) {
                $sub_query .= $sub_query_sep.$sms;
                $sub_query_sep=",";
            }
            $sub_query.=")";
        }
    }

    $option = "<select name=\"search_sport_groups_" . ($_GET['search_level'] + 1)."\" id=\"search_sport_groups_" . ($_GET['search_level']+1)."\"  class=\"form-control\" onChange=\"getSearchSubGroup('" . $_GET['search_groupid']."',this.value," . ($_GET['search_level']+1).")\"  style=\"padding-left:" . (($_GET['search_level']+1)*15)."px; margin-bottom:5px;\">
    #LIST#
    </select>";

    $sql="SELECT * FROM 
            sport_group_value sg, sport_group_value_details sgd WHERE sg.sport_group_value_id=sgd.sport_group_value_id AND sgd.language_code='" . $_SESSION['language_code']."'
            AND sport_group_id='" . $_GET['search_groupid'] . "'
            AND sport_group_sub_of='" . $_GET['search_sub_groupid'] . "'
            " . $sub_query . "
            ORDER BY 
            FIELD(sgd.sport_group_value_name,'Alle','All') DESC, sgd.sport_group_value_name";

    $query = $DB->prepare($sql);
    $query->execute();
    $get_groups = $query->fetchAll();

    $list = '';
    foreach($get_groups as $group) {
        $list.="<option value=\"".$group['sport_group_value_id']."\">-> 
    ".$group['sport_group_value_name']."</option>";
    }

    if($list == '') {
        $output = '';
    }
    else {
        $output = str_replace("#LIST#", $list,$option);
    }
    echo $output;
}
else {
    //echo "error: id missing";
}
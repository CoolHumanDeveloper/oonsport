<?php
require("../../includes/config.php");

//print_r($_GET);
	if($_GET['groupid']) {
        $option="<select name=\"sport_groups_".($_GET['level']+1)."\" id=\"sport_groups_".($_GET['level']+1)."\"  class=\"form-control\" onChange=\"getSubGroup('".$_GET['groupid']."',this.value,".($_GET['level']+1).")\"  style=\"padding-left:".(($_GET['level']+1)*15)."px; margin-bottom:5px;\">
        #LIST#
        </select>";

        $sql="SELECT * FROM sport_group_value sg, sport_group_value_details sgd WHERE sg.sport_group_value_id=sgd.sport_group_value_id AND sgd.language_code='".$_SESSION['language_code']."'
        AND sport_group_id='".$_GET['groupid']."'
        AND sport_group_sub_of='".$_GET['sub_groupid']."'

        ORDER BY 
        FIELD(sgd.sport_group_value_name,'Alle','All') DESC, sgd.sport_group_value_name";

        $query = $DB->prepare($sql);
        $query->execute();
        $get_groups = $query->fetchAll();

        $list='';
        foreach ($get_groups as $group)	{
            $list.="<option value=\"".$group['sport_group_value_id']."\">-> ".$group['sport_group_value_name']."</option>";
        }

        if($list == '') {
            $output='';
        }
        else {
            $output=str_replace("#LIST#",$list,$option);
        }
        echo $output;

	}
	else {
		//echo "error: id missing";
	}
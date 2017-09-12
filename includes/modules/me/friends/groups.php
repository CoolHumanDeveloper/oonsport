<?php
is_user();

if(isset($_POST['add_group_'.$_SESSION['user']['secure_spam_key']]) && $_POST['add_group_'.$_SESSION['user']['secure_spam_key']] == 1) {
	if(strlen($_POST['add_group_name_'.$_SESSION['user']['secure_spam_key']]) > 64) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_GROUP_NAME_ERROR_TEXT_LENGTH);
	}
	else 
	if(strlen($_POST['add_group_name_'.$_SESSION['user']['secure_spam_key']]) === 0) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_GROUP_NAME_ERROR_TEXT_SHORT);
	}
	if(strlen($_POST['add_group_description_'.$_SESSION['user']['secure_spam_key']]) > 255) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_GROUP_DESC_ERROR_TEXT_LENGTH);
	}
	else 
	if(strlen($_POST['add_group_description_'.$_SESSION['user']['secure_spam_key']]) === 0) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_GROUP_DESC_ERROR_TEXT_SHORT);
	}
	else {
		$sql = "INSERT INTO `groups` (`group_name`, `group_description`, `group_created`, `group_admin_user_id`) VALUES ('".$_POST['add_group_name_'.$_SESSION['user']['secure_spam_key']]."', '".$_POST['add_group_description_'.$_SESSION['user']['secure_spam_key']]."', NOW(), '".$_SESSION['user']['user_id']."')";
		
        $query = $DB->prepare($sql);
        $query->execute();
    
		$last_group_id = $DB->lastInsertId();
		
		$sql = "INSERT INTO user_to_groups (user_id, group_id, group_user_status, group_user_invited) VALUES ('".$_SESSION['user']['user_id']."', '".$last_group_id."', 1, NOW())";
		$query = $DB->prepare($sql);
        $query->execute();
		
		$_SESSION['system_temp_message'] .= set_system_message("success", TEXT_GROUP_ADD_SUCCESS);
		header("Location: ".SITE_URL."me/friends/groups/");
        die();
	}
}


$output = '<div class="col-md-9 col-sm-12 content-box-right">
    <h4 class="profile">'.TEXT_PROFILE_FRIENDS_GROUPS.'</h4><br>


    <div class="col-md-12">
';

$sql = "	SELECT 
			* 
		FROM 
			`groups` g,
			user_to_groups utg
		WHERE 
			utg.user_id='".$_SESSION['user']['user_id']."' AND
			utg.group_id = g.group_id AND 
			utg.group_user_status < 2 
			
		GROUP BY 
			g.group_id
		ORDER BY
			g.group_id DESC";


    $query = $DB->prepare($sql);
    $query->execute();
    $get_groups = $query->fetchAll();
			

if($query->rowCount() == 0) {
	$output.=set_system_message("error", TEXT_GROUP_EMPTY);
}
else {

	foreach ($get_groups as $groups) {
		$sub_query_sql="	SELECT 
								* 
							FROM 
								user u,
								user_to_groups utg
							WHERE 
								utg.group_id='".$groups['group_id']."' AND
								utg.user_id = u.user_id AND 
								utg.group_user_status = 1
							GROUP BY 
								u.user_id
							ORDER BY
								u.user_id DESC";
		
		$subquery_preview = get_subquery_preview($sub_query_sql, 5);
				
        $output .= '
            <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                    <a href="#SITE_URL#group/'.md5($groups['group_id'].$groups['group_name'].$groups['group_created']).'" class="thumbnail thumbnail-line">
                    <div class="row"><div class="grid_line col-xs-10 col-sm-10 col-md-5">
                    <strong>'.$groups['group_name'].' </strong><br>
            <small>'.$groups['group_description'].'</small>
                    </div>

                    <div class="grid_line col-xs-10 col-sm-10 col-md-5">
                      '.$subquery_preview.'
                      </div>

                    <div class="grid_line grid_button-right col-xs-2 col-sm-2 col-md-2">
                    <i class="fa fa-angle-double-right "></i>
                </div>

            </div>
            </a>

            </div>

            <div class="col-xs-12 col-sm-12 col-md-12"></div>

            </div>';
	}
}


$output .= '
    </div>
    <br>
    <br>

    <h4 class="profile">'.TEXT_GROUP_CREATE.':</h4>
    <form method="post" action="#SITE_URL#me/friends/groups/">
    <div class="col-md-12">
     <div class="col-md-8"><strong>'.TEXT_GROUP_NAME.':</strong><br>
    <input type="text" name="add_group_name_'.$_SESSION['user']['secure_spam_key'].'" value="'.$_POST['add_group_name_'.$_SESSION['user']['secure_spam_key']].'"  class="form-control" required><br>


    <strong>'.TEXT_GROUP_DESCRIPTION.':</strong><br>
    <textarea class="form-control" name="add_group_description_'.$_SESSION['user']['secure_spam_key'].'" rows="4" maxlength="255" placeholder="'.TEXT_GROUP_DESCRIPTION_PLACEHOLDER.'" required>'.$_POST['add_group_description_'.$_SESSION['user']['secure_spam_key']].'</textarea><br>
     </div>
    <div class="col-md-4">
    </div>

    <div class="col-md-12">
    <input type="hidden" name="add_group_'.$_SESSION['user']['secure_spam_key'].'" value="1">
    <input type="submit" value="'.TEXT_GROUP_CREATE.'" class="btn btn-sm btn-primary form-control"></div>

    </div>
    </form>

    </div>
    ';

$content_output = array('TITLE' => TEXT_PROFILE_FRIENDS_FRIENDS.' - '.TEXT_PROFILE_FRIENDS_GROUPS,
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
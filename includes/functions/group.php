<?php

function is_group_admin($group_id, $user_id) {
	global $DB;
    $sql = "SELECT * FROM 
	`groups` 
	WHERE
	group_admin_user_id = '".$user_id."' AND 
	group_id = '".$group_id."' LIMIT 1";
	
	$query = $DB->prepare($sql);
    $query->execute();

    if($query->rowCount() == 1) {	
	return true;
	}
	
	return false;
}

function is_group_member($group_id, $user_id) {
	global $DB;
    $sql = "SELECT * FROM 
	`user_to_groups` 
	WHERE
	user_id = '".$user_id."' AND 
	group_id = '".$group_id."' AND 
	group_user_status = 1 
	LIMIT 1";
	
	$query = $DB->prepare($sql);
    $query->execute();

    if($query->rowCount() == 1) {
	return true;
	}
	
	return false;
}

function is_group_invited($group_id, $user_id) {
	global $DB;
    $sql = "SELECT * FROM 
	`user_to_groups` 
	WHERE
	user_id = '".$user_id."' AND 
	group_id = '".$group_id."' AND 
	group_user_status = 0 
	LIMIT 1";
	
	$query = $DB->prepare($sql);
    $query->execute();

    if($query->rowCount() == 1) {	
	return true;
	}
	
	return false;
}

function is_group_connected($group_id, $user_id) {
	global $DB;
    $sql = "SELECT * FROM 
	`user_to_groups` 
	WHERE
	user_id = '".$user_id."' AND 
	group_id = '".$group_id."'
	LIMIT 1";
	
	$query = $DB->prepare($sql);
    $query->execute();

    if($query->rowCount() == 1) {	
	return true;
	}
	
	return false;
}

function build_group_feed($user_id,$group) {
	global $DB;
	// News
	$output = '';

	$sql = "	SELECT 
				u.*, 
				ud.*,
				gs.* 
			FROM 
				user u
				INNER JOIN user_details ud ON u.user_id=ud.user_id
				INNER JOIN groups_message gs ON u.user_id = gs.group_message_user_id
			WHERE 
				u.user_status=1 AND
				gs.group_message_group_id = '".$group."'
			GROUP BY gs.group_message_id
			ORDER BY 
				gs.group_message_date DESC 
			LIMIT 20";

    $query = $DB->prepare($sql);
    $query->execute();
    $get_group_message = $query->fetchAll();
    foreach ($get_group_message as $group_message) {
        $output .= '<div class="row">
                    <div class="shoutbox-left">
                        <a href="#SITE_URL#profile/'.md5($group_message['user_id'].$group_message['user_nickname']).'">'.build_default_image($group_message['user_id'],"50x50","").'</a>
                    </div>
                <div  class="shoutbox-right">';

        if($group_message['group_message_user_id'] == $_SESSION['user']['user_id'] && $page != 'profile') {
            $output .= '<span class="shoutbox_action">
                        <form method="post" action="#SITE_URL#group/'.$_GET['content_value'].'" >
                            <input name="group_message_key_'.$_SESSION['user']['secure_spam_key'].'" value="'.md5($group_message['group_message_id'].$group_message['group_message_user_id'].$group_message['group_message_date']).'" type="hidden">
                            <button name="delete_group_message_key_'.$_SESSION['user']['secure_spam_key'].'" class="btn btn-xs btn-danger" type="submit" value="1" >
                            <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </span>';
        }

        $output .= '
        <small>'.date("H:i d.m.Y",strtotime($group_message['group_message_date'])).'<br>
        <strong>'.$group_message['user_nickname'].'</strong> aus '.get_city_name($group_message['user_city_id'],$group_message['user_country']).'
        </small>

        <br>
        '.nl2br($group_message['group_message_value']).'


        </div>
        <hr>
        <div class="clearfix"></div>
        </div>
        ';
    }

	if($output == '')  { 
        $output = TEXT_SHOUTBOX_PROFILE_EMPTY;
    }

	return $output;
}

function build_group_select($user_id,$profile) {
	
	global $DB;
    $sql = "	SELECT 
			* 
			FROM 
				groups g,
				user_to_groups utg
			WHERE 
				utg.user_id='".$user_id."' AND
				utg.group_id = g.group_id
			GROUP BY 
				g.group_id
			ORDER BY
				g.group_id DESC";
			
			
    $query = $DB->prepare($sql);
    $query->execute();

    if($query->rowCount() == 1) {	
        $output = '';

        return $output;
    }
    else {

        $form='<form method="post" action="#SITE_URL#profile/'.md5($profile['user_id'].$profile['user_nickname']).'">
            <select name="invite_group_'.$_SESSION['user']['secure_spam_key'].'" class="form-control" required>
            <option value="">'.TEXT_GLOBAL_PLEASE_CHOOSE.'</option>';
        $query->fetchAll();
        foreach ($get_groups as $groups) {
            if(is_group_connected($groups['group_id'],$profile['user_id']) === false) {
                $form.='<option value="'.$groups['group_id'].'">'.cut_string($groups['group_name'],32).'</option>';
            }
            else {
                $form.='<option value="" disabled>'.cut_string($groups['group_name'],32).' --- '.TEXT_GROUP_INVITED.'</option>';
            }
        }

        $form.='</select>
        <input type="hidden" name="invite_to_group_'.$_SESSION['user']['secure_spam_key'].'" value="1"><br>
    <br>

        <button  class="btn btn-primary" type="submit"><i class="fa fa-users"></i> '.TEXT_GROUP_INVITE.'</button>
        </form>';

        $output = '
            <a class="list-group-item" data-toggle="modal" data-target="#group_modal"><i class="fa fa-users"></i> '.TEXT_GROUP_INVITE.'</a>
             <div class="modal fade" id="group_modal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                         <header class="list-group-item start_register_header"><strong>'.TEXT_GROUP_INVITE.'</strong>: </header>

                    </div> 

                    <div class="modal-body">
                    '.TEXT_GROUP_INVITE_FORM_INTRO.'<br>
        <br>
                        '.$form.'
                    </div>

                </div>
           </div>
        </div>';	

        return $output; 
    }
}
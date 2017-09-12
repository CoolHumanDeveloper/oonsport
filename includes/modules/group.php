<?php
is_user();

$sql = "	SELECT 
			g.*,
			u.user_id 
		FROM 
			groups g
			LEFT JOIN user_to_groups utg ON g.group_id = utg.group_id
			LEFT JOIN user u ON u.user_id = g.group_admin_user_id
		WHERE
			MD5(CONCAT(g.group_id, g.group_name, g.group_created)) = '".$_GET['content_value']."' AND
			u.user_status = 1			
		LIMIT 1";
$query = $DB->prepare($sql);
$query->execute();
$group = $query->fetch();


if(is_group_member($group['group_id'],$_SESSION['user']['user_id']) === false && is_group_invited($group['group_id'],$_SESSION['user']['user_id']) === false) {
	$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_GROUP_ERROR_NO_ACCESS);
	header("Location: ".SITE_URL."me/friends/groups/");
    die();
}


// CONFRIM 
if(isset($_POST['confirm_group_membership_'.$_SESSION['user']['secure_spam_key']]) && $_POST['confirm_group_membership_'.$_SESSION['user']['secure_spam_key']] == 1) {
		$sql = "UPDATE user_to_groups SET group_user_status='1' WHERE user_id ='".$_SESSION['user']['user_id']."'  AND group_id = '".$group['group_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
			$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_GROUP_WELCOME.' <strong>'.$group['group_name'].'</strong>');
			header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
            die();
		}
		else {
			$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_GROUP_WELCOME_ERROR);
			header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
            die();
		}
}

// DECLINE 
if(isset($_POST['decline_group_membership_'.$_SESSION['user']['secure_spam_key']]) && $_POST['decline_group_membership_'.$_SESSION['user']['secure_spam_key']] == 1) {
		if($_POST['decline_group_membership_finaly_'.$_SESSION['user']['secure_spam_key']] == 1) {
			$sql = "UPDATE user_to_groups SET group_user_status='2' WHERE user_id ='".$_SESSION['user']['user_id']."'  AND group_id = '".$group['group_id']."'";
			$add_text = " ".TEXT_GROUP_DECLINE_FINALY_SUCCESS;
		}
		else {
			$sql = "DELETE FROM user_to_groups WHERE user_id ='".$_SESSION['user']['user_id']."'  AND group_id = '".$group['group_id']."'";
			$add_text = "";
		}
		
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
			$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_GROUP_DECLINE_SUCCESS.$add_text);
			header("Location: ".SITE_URL.'me/friends/groups/');
            die();
		}
		else {
			$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_GROUP_DECLINE_ERROR);
			header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
            die();
		}
}

//LEAVE
if(isset($_POST['leave_group_'.$_SESSION['user']['secure_spam_key']]) && $_POST['leave_group_'.$_SESSION['user']['secure_spam_key']] == 1) {
		if($_POST['leave_group_finaly_'.$_SESSION['user']['secure_spam_key']] == 1) {
			$sql = "UPDATE user_to_groups SET group_user_status='2' WHERE user_id ='".$_SESSION['user']['user_id']."'  AND group_id = '".$group['group_id']."'";
			$add_text = " ".TEXT_GROUP_LEAVE_FINALY_SUCCESS;
		}
		else {
			$sql = "DELETE FROM user_to_groups WHERE user_id ='".$_SESSION['user']['user_id']."'  AND group_id = '".$group['group_id']."'";
			$add_text = "";
		}
		
		$query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
			$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_GROUP_LEAVE_SUCCESS.$add_text);
			header("Location: ".SITE_URL.'me/friends/groups/');
            die();
		}
		else {
			$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_GROUP_LEAVE_ERROR);
			header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
            die();
		}
}


// DELETE GROUP POST 
if(isset($_POST['group_message_key_'.$_SESSION['user']['secure_spam_key']]) && strlen($_POST['group_message_key_'.$_SESSION['user']['secure_spam_key']])==32) {
	
	if($_POST['delete_group_message_key_'.$_SESSION['user']['secure_spam_key']]==1) {
	
		$sql = "DELETE FROM `groups_message` WHERE MD5(CONCAT(group_message_id, group_message_user_id, group_message_date))='".$_POST['group_message_key_'.$_SESSION['user']['secure_spam_key']]."' AND group_message_user_id = '".$_SESSION['user']['user_id']."' ";
		
		$query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
			
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_GROUP_MESSAGE_DELETE_SUCCESS);		

            build_history_log($_SESSION['user']['user_id'],"removed_group_message",'');

            header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
            die();
		
		}
		else {
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_SHOUT_BOX_SUCCESS_REMOVED_ERROR);
            header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
            die();
		
		}
		
	}
}

if(isset($_POST['add_group_message_text_'.$_SESSION['user']['secure_spam_key']])) {
	if(strlen($_POST['add_group_message_text_'.$_SESSION['user']['secure_spam_key']]) > 255) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_SHOUT_BOX_ERROR_TEXT_LENGTH);
		$group_message_renew_message = $_POST['add_group_message_text_'.$_SESSION['user']['secure_spam_key']];
	}
	else 
	if(strlen($_POST['add_group_message_text_'.$_SESSION['user']['secure_spam_key']]) === 0) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_SHOUT_BOX_ERROR_TEXT_LENGTH_SHORT);
		$group_message_renew_message = $_POST['add_group_message_text_'.$_SESSION['user']['secure_spam_key']];
	}
	else {
		$sql = "INSERT INTO `groups_message` (group_message_user_id, group_message_group_id, `group_message_date`, `group_message_value`) VALUES ('".$_SESSION['user']['user_id']."', '".$group['group_id']."',  NOW(), '".$_POST['add_group_message_text_'.$_SESSION['user']['secure_spam_key']]."');";
		$query = $DB->prepare($sql);
        $query->execute();
		
		$_SESSION['system_temp_message'] .= set_system_message("success",TEXT_GROUP_MESSAGE_ADD_SUCCESS);
		header("Location: ".SITE_URL.'group/'.$_GET['content_value'].'');
        die();
	}
}

// Start Output
$output = '<div class="col-md-9 col-sm-12 content-box-right">
    <h4 class="profile">' . TEXT_GLOBAL_GROUP.': '.$group['group_name'].'</h4>
	'.$group['group_description'].'<hr>';

if(is_group_member($group['group_id'],$_SESSION['user']['user_id']) === true) {

$output .= '
    <div class="col-md-12">

<br>
<br>

<h4 class="profile">' . TEXT_GROUP_CREATE_MESSAGE.':</h4>
	<form method="post" action="#SITE_URL#group/'.$_GET['content_value'].'">
		<div class="col-md-12">
				<textarea class="form-control" name="add_group_message_text_'.$_SESSION['user']['secure_spam_key'].'" rows="4" maxlength="255" placeholder="' . TEXT_GROUP_MESSAGE_PLACEHOLDER.'"></textarea><br>
				 
				<input type="submit" value="' . TEXT_GROUP_WRITE.'" class="btn btn-sm btn-primary form-control"><br>
				<br>
				<br>
				<br>
		</div>
	
	</form>

	</div>
<div class="col-md-12">
'.build_group_feed($_SESSION['user']['user_id'],$group['group_id']).'
</div>';

}
else {
$output .= '<br>
<br><div class="col-md-12">
'.set_system_message("error", TEXT_GROUP_NOT_CONFIRMED).'

</div>
<div class="col-md-6">
<br>

<form method="post" action="#SITE_URL#group/'.$_GET['content_value'].'">
<input type="hidden" name="confirm_group_membership_'.$_SESSION['user']['secure_spam_key'].'" value="1">
<button class="btn btn-primary">' . TEXT_GROUP_ENTER.'</button>
</form><br><br>
<br>


</div>

<div class="col-md-6 text-right">
<form method="post" action="#SITE_URL#group/'.$_GET['content_value'].'">
<input type="checkbox" name="decline_group_membership_finaly_'.$_SESSION['user']['secure_spam_key'].'" value="1"> ' . TEXT_GROUP_DECLINE_FINALY.'<br>
<input type="hidden" name="decline_group_membership_'.$_SESSION['user']['secure_spam_key'].'" value="1">
<button class="btn btn-danger">' . TEXT_GROUP_DECLINE.'</button><br>
<br>
</form>
</div>
';	
}

if(is_group_admin($group['group_id'],$_SESSION['user']['user_id']) === false && is_group_member($group['group_id'],$_SESSION['user']['user_id']) === true) {
$output .= '	<div class="col-md-12">
				<form method="post" action="#SITE_URL#group/'.$_GET['content_value'].'">
					<input type="checkbox" name="leave_group_finaly'.$_SESSION['user']['secure_spam_key'].'" value="1"> ' . TEXT_GROUP_DECLINE_FINALY.'<br>
					<input type="hidden" name="leave_group_'.$_SESSION['user']['secure_spam_key'].'" value="1">
					<button class="btn btn-danger">' . TEXT_GROUP_LEAVE.'</button><br>
					<br>
				</form>
			</div>
';
}
$output .= '</div>
<br>
<br>';

$subnav=' <div class="list-group sub_nav">
  <a href="#SITE_URL#me/friends/friends/" class="list-group-item"><i class="fa fa-user"></i> ' . TEXT_PROFILE_FRIENDS_FRIENDS.' 
</a>
  <a href="#SITE_URL#me/friends/groups/" class="list-group-item"><i class="fa fa-users"></i> ' . TEXT_PROFILE_FRIENDS_GROUPS.' 
</a>
  <a href="#SITE_URL#me/friends/request/" class="list-group-item"><i class="fa fa-user-plus"></i> ' . TEXT_PROFILE_FRIENDS_REQUESTS.' 
</a>
<a href="#SITE_URL#me/friends/watch/" class="list-group-item"><i class="fa fa-eye"></i> ' . TEXT_PROFILE_FRIENDS_WATCHLIST.' 
</a>
  </div>';


$sidebar='<div class="col-md-3 col-sm-12">
<div class="side_bar">


<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="' . TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> ' . TEXT_PROFILE_FRIENDS_FRIENDS.'
</li>
  </ul>
 '.$subnav.'
	</div>
	</div>
';

$sub_sidebar='<div class="col-md-3 col-sm-12">
<div class="sub_side_bar">
 '.$subnav.'
	</div>
	</div>
';

$content_output = array('TITLE' => TEXT_PROFILE_FRIENDS_FRIENDS.' - ' . TEXT_PROFILE_FRIENDS_GROUPS,
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
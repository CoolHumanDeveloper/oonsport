<?php 
is_user();

if(isset($_POST['delete_totaly_message_'.$_SESSION['user']['secure_spam_key']])) {
	$sql = "SELECT * FROM 
	message m
	LEFT JOIN user_to_messages AS utm ON m.message_id = utm.message_id WHERE
	m.message_key = '".$_POST['message_id_'.$_SESSION['user']['secure_spam_key']]."' AND 
	(m.message_from_user_id = '".$_SESSION['user']['user_id']."' OR m.message_to_user_id = '".$_SESSION['user']['user_id']."') AND
	utm.user_id = '".$_SESSION['user']['user_id']."' LIMIT 1";
	
	$query = $DB->prepare($sql);
    $query->execute();
    $message = $query->fetch();

	
	if(!isset($message['message_id'])) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_NOT_FOUND_DELETE);
	}
	else {
		$sql = "DELETE FROM user_to_messages  WHERE message_box='trash' AND user_id = '".$_SESSION['user']['user_id']."' AND message_id = '".$message['message_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
		
		if($query->rowCount() == 1) {
			$_SESSION['system_message'] .= set_system_message("success",TEXT_MESSAGES_MESSAGES_SUCCESS_DELETE_TOTALY);
			
			
			// Nachricht asu dem System löschen, wenn kein Postfach mehr verknüpft ist,
			$sql = "SELECT * FROM user_to_messages WHERE message_id = '".$message['message_id']."' LIMIT 1";
            $query = $DB->prepare($sql);
            $query->execute();

            if($query->rowCount() < 1) {
				$sql = "DELETE FROM message WHERE message_id = '".$message['message_id']."'";
				$query = $DB->prepare($sql);
                $query->execute();
			}
			
		}
		else { 
			$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_DELETE_TOTALY);
		}
	
	}

}


if(isset($_POST['delete_message_'.$_SESSION['user']['secure_spam_key']])) {
	$sql = "SELECT * FROM 
	message m
	LEFT JOIN user_to_messages AS utm ON m.message_id = utm.message_id WHERE
	m.message_key = '".$_POST['message_id_'.$_SESSION['user']['secure_spam_key']]."' AND 
	(m.message_from_user_id = '".$_SESSION['user']['user_id']."' OR m.message_to_user_id = '".$_SESSION['user']['user_id']."') AND
	utm.user_id = '".$_SESSION['user']['user_id']."' LIMIT 1";
	
	$query = $DB->prepare($sql);
    $query->execute();
    $message = $query->fetch();
	
	if(!isset($message['message_id'])) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_NOT_FOUND_DELETE);
	}
	else {
		
		if($message['message_readed'] == 0 && $message['message_to_user_id'] === $_SESSION['user']['user_id']){
			$sql = "UPDATE message SET message_readed=1 WHERE message_id = '".$message['message_id']."'";
			$query = $DB->prepare($sql);
            $query->execute();
        }
		
		
		$sql = "UPDATE user_to_messages SET message_box='trash', message_action_date = NOW() WHERE user_id = '".$_SESSION['user']['user_id']."' AND message_id = '".$message['message_id']."'";
        $query = $DB->prepare($sql);
        $query->execute();
		
		if($query->rowCount() == 1) {
			$_SESSION['system_message'] .= set_system_message("success",TEXT_MESSAGES_MESSAGES_SUCCESS_DELETE);
		}
		else { 
			$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_DELETE);
		}
	
	}

}

//  Widerherstellen, in den Posteingang

if(isset($_POST['recover_message_'.$_SESSION['user']['secure_spam_key']])) {
	
	
	$sql = "SELECT * FROM 
	message m
	LEFT JOIN user_to_messages AS utm ON m.message_id = utm.message_id WHERE
	m.message_key = '".$_POST['message_id_'.$_SESSION['user']['secure_spam_key']]."' AND 
	(m.message_from_user_id = '".$_SESSION['user']['user_id']."' OR m.message_to_user_id = '".$_SESSION['user']['user_id']."') AND
	utm.user_id = '".$_SESSION['user']['user_id']."'";
	
	$query = $DB->prepare($sql);
    $query->execute();
    $message = $query->fetch();
	
	if(!isset($message['message_id'])) {
		$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_NOT_FOUND_DELETE);
	}
	else {
		if($message['message_type'] == 'from') {
			$sql = "UPDATE user_to_messages SET message_box='send' WHERE user_id = '".$_SESSION['user']['user_id']."' AND message_id = '".$message['message_id']."'";
			$query = $DB->prepare($sql);
            $query->execute();
			
			if($query->rowCount() == 1) {
				$_SESSION['system_message'] .= set_system_message("success",TEXT_MESSAGES_MESSAGES_SUCCESS_RECOVER_SEND);
			}
			else { 
				$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_RECOVER_SEND);
			}
		} 
        else {
			$sql = "UPDATE user_to_messages SET message_box='inbox' WHERE user_id = '".$_SESSION['user']['user_id']."' AND message_id = '".$message['message_id']."'";
			$query = $DB->prepare($sql);
            $query->execute();
			
			if($query->rowCount() == 1) {
				$_SESSION['system_message'] .= set_system_message("success",TEXT_MESSAGES_MESSAGES_SUCCESS_RECOVER_INBOX);
			}
			else { 
				$_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_MESSAGES_ERROR_RECOVER_INBOX);
			}
		}
	}

}

$subnav=' <div class="list-group sub_nav">
  <a href="#SITE_URL#me/messages/inbox/" class="list-group-item"><i class="fa fa-envelope"></i> '.TEXT_MESSAGES_INBOX.' </a>
  <a href="#SITE_URL#me/messages/send/" class="list-group-item"><i class="fa fa-paper-plane"></i> '.TEXT_MESSAGES_SEND.'</a>
  <a href="#SITE_URL#me/messages/trash/" class="list-group-item"><i class="fa fa-trash"></i>
 '.TEXT_MESSAGES_TRASH.'</a>
  </div>
   <div class="list-group sub_nav">
   <a href="#SITE_URL#me/messages/blocked/" class="list-group-item"><i class="fa fa-ban"></i> '.TEXT_MESSAGES_BLOCKED.'</a>
  </div>';

$sidebar='

<div class="col-md-3 col-sm-12">

<div class="side_bar">
<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> '.TEXT_MESSAGES_MESSAGES.'
</li>
  </ul>
  '.$subnav.'
</div>
 '.build_banner('profile','xs','',0,'').'
'.build_banner('profile','sm','',0,'').'
'.build_banner('profile','md','',0,'').'
'.build_banner('profile','lg','',0,'').'

	</div>
';

$sub_sidebar='<div class="col-md-3 col-sm-12">
<div class="sub_side_bar">
 '.$subnav.'
	</div>
	</div>
';

$sidebar = str_replace('messages/'.$_GET['sub_content_value'].'/" class="', 'messages/'.$_GET['sub_content_value'].'/" class="active ',$sidebar);
$sub_sidebar = str_replace('messages/'.$_GET['sub_content_value'].'/" class="', 'messages/'.$_GET['sub_content_value'].'/" class="active ',$sub_sidebar);

switch($_GET['sub_content_value']){
    case ("create"):
        require(MODULE_PATH . "me/messages/create.php");
    break;

    case ("inbox"):
        require(MODULE_PATH . "me/messages/inbox.php");
    break;

    case ("send"):
        require(MODULE_PATH . "me/messages/send.php");
    break;

    case ("trash"):
        require(MODULE_PATH . "me/messages/trash.php");
    break;

    case ("read"):
        require(MODULE_PATH . "me/messages/read.php");
    break;

    case ("archive"):
        require(MODULE_PATH . "me/messages/archive.php");
    break;

    case ("blocked"):
        require(MODULE_PATH . "me/messages/blocked.php");
    break;

    default:
        header("location: ".SITE_URL."me/messages/inbox/");
    break;
}
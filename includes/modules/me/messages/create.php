<?php 

if(isset($_POST['message_to_user_id_'.$_SESSION['user']['secure_spam_key']]) && strlen($_POST['message_to_user_id_'.$_SESSION['user']['secure_spam_key']])==32) {
	$sql = "SELECT ud.user_nickname, u.user_id, u.user_email, u.user_sub_of, ud.user_firstname FROM user u, user_details ud WHERE MD5(CONCAT(u.user_id,ud.user_nickname))='".$_POST['message_to_user_id_'.$_SESSION['user']['secure_spam_key']]."' AND u.user_id = ud.user_id LIMIT 1";
	  
    $query = $DB->prepare($sql);
    $query->execute();
    $message_user = $query->fetch();

	if($_SESSION['user']['user_id']==$message_user['user_id']) {
		header("location: ".SITE_URL."profile/".md5($_SESSION['user']['user_id'].$_SESSION['user']['user_nickname']));
        die();
	}
	
	if($message_user['user_sub_of'] > 0) {
		$message_user['user_email'] = getParentEmail($message_user['user_sub_of']);
	}
		
	if(action_blockedlist_exists($_SESSION['user']['user_id'],$message_user['user_id']) === true) {
		$_SESSION['system_temp_message'] .= set_system_message("error", TEXT_MESSAGES_FAILED_BLOCKED);
		header("Location: ".SITE_URL."me/messages/inbox/");
		die();
	}


    if(isset($_POST['message_subject_'.$_SESSION['user']['secure_spam_key']])) {
        if($_POST['message_reply_'.$_SESSION['user']['secure_spam_key']]==1) {	
            $message_subject="RE: ".$_POST['message_subject_'.$_SESSION['user']['secure_spam_key']];
            $message_content="\n\r\n\r---------- ".TEXT_MESSAGES_CREATE_PREVIOUS_MESSAGE." ---------\n\r".$_POST['message_content_'.$_SESSION['user']['secure_spam_key']];
        }
        else {
           $form_error=0;
            $message_subject = $_POST['message_subject_'.$_SESSION['user']['secure_spam_key']];

            if($message_subject=="") {
                $_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_CREATE_ERROR_SUBJECT);
                $form_error=1;
            }

            $message_content = $_POST['message_content_'.$_SESSION['user']['secure_spam_key']];

            if($message_content=="") {
                $_SESSION['system_message'] .= set_system_message("error", TEXT_MESSAGES_CREATE_ERROR_CONTENT);
                $form_error=1;
            }

            if($form_error == 0) {
                $subject = $_POST['message_subject_'.$_SESSION['user']['secure_spam_key']];
                $content = $_POST['message_content_'.$_SESSION['user']['secure_spam_key']];

                $systemlink="me/message/inbox/";
              if(have_permission("emailcopy_on_message",$message_user['user_id'])) {
                    echo "RECHTE SIND DA";
                    $email_content_array = array
                    (
                    "NAME" => $message_user['user_firstname'],
                    "FROM_USER_NAME" => $_SESSION['user']['user_nickname'],
                    "FROM_USER_IMAGE" => build_default_image($_SESSION['user']['user_id'],"115x115","plain"),
                    "FROM_USER_LINK" => $systemlink,
                    "FROM_USER_DETAILS" => get_city_name($_SESSION['user']['user_city_id'],$_SESSION['user']['user_country']).
                    "<br>".get_user_main_sport($_SESSION['user']['user_id'])
                    );

                    $email_content_template=email_content_to_template("message-new",$email_content_array,"");
                    $alt_content="";

                    if(sending_email($message_user['user_email'],$message_user['user_firstname'],TEXT_MESSAGES_SUBJECT_FROM." ".$_SESSION['user']['user_nickname']." : ".substr($subject,0,64),$email_content_template,$alt_content,0) === false) {
                        error_log("Fehler: >Emailversand< Template=friendship-request, To-User: ".$message_user['user_email']." ");
                    }	

              }

                send_message_online($message_user['user_id'], $_SESSION['user']['user_id'], $subject, $content, '', 0);


                $_SESSION['system_temp_message'] .= set_system_message("success", TEXT_MESSAGES_CREATE_SEND_SUCCESS);
                header("Location: ".SITE_URL."me/messages/send/");
                die();
            }
        }
    }

}
else {
	echo "ERROR- Falsche Anfrage";
}
 $output = '
		<div class="col-md-9 col-sm-12 content-box-right">
     <h4 class="profile">'.TEXT_MESSAGES_NEW.'</h4><br>
	
	<form method="post" action="#SITE_URL#me/messages/create/" id="my_message"  class="form">
        
		<div class="row form_line">
	
		<div class="col-md-12">
		'.TEXT_MESSAGE_TO.':'.$message_user['user_nickname'].'
		</div></div>
		
		
		<div class="row form_line">
	
		<div class="col-md-12">
		
		<div class="col-md-3">
        	<label>'.TEXT_MESSAGE_SUBJECT.':</label>
		</div>
		<div class="col-md-9">
			<input type="text" value="'.$message_subject.'" name="message_subject_#SPAM_KEY#" class="form-control" required>
		</div>
			<div class="col-md-3">
        	<label>'.TEXT_MESSAGES_CONTENT.':</label>
		</div>
		<div class="col-md-9">
			<textarea class="form-control" rows="10" name="message_content_#SPAM_KEY#" required>'.$message_content.'</textarea>
			</div>
			<div class="col-md-3">
        	<label></label>
		</div>
		<div class="col-md-9">
			<input type="submit" value="'.TEXT_SUB_NAV_WRITE_MESSAGE.'" class="btn btn-sm btn-primary form-control">
		</div>
		
		
		</div>
		</div>
		
		<input type="hidden" name="message_to_user_id_#SPAM_KEY#" value="'.$_POST['message_to_user_id_'.$_SESSION['user']['secure_spam_key']].'">
		</form>
	</div>
';
	
	
	$content_output = array('TITLE' => TEXT_MESSAGES_MESSAGES.' -> '.TEXT_MESSAGES_INBOX,
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
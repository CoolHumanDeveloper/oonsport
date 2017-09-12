<?php 

if(isset($_POST['message_id_'.$_SESSION['user']['secure_spam_key']]) && strlen($_POST['message_id_'.$_SESSION['user']['secure_spam_key']])==32) {


    $sql = "SELECT * FROM message m, user_to_messages utm WHERE utm.user_id='".$_SESSION['user']['user_id']."' AND m.message_id = utm.message_id AND m.message_key = '".$_POST['message_id_'.$_SESSION['user']['secure_spam_key']]."' ORDER BY m.message_date ASC";
	  
    $query = $DB->prepare($sql);
    $query->execute();
    $message = $query->fetch();
	  
    if($message['message_type']=="from") {
      $message_user=get_user_details($message['message_to_user_id']);
      $message_type="<i class=\"fa fa-share\"></i>";
    }
    else {
        $message_user=get_user_details($message['message_from_user_id']);
        $message_type="";
    }

    if($message['message_readed'] == 0 
       && $message['message_to_user_id'] === $_SESSION['user']['user_id']) {
        $sql = "UPDATE message SET message_readed=1 WHERE message_key = '".$_POST['message_id_'.$_SESSION['user']['secure_spam_key']]."'";
        $query = $DB->prepare($sql);
        $query->execute();
    }

 $output = '<div class="col-md-9 col-sm-12 content-box-right">
       
		<div class="row form_line">
	
		<div class="col-md-12">
		'.$message_type.$message_user['user_nickname'].'
		</div></div>

		<div class="row form_line">
		  <div class="col-md-3">
				<label>'.TEXT_MESSAGES_SUBJECT.':</label>
			</div>
			<div class="col-md-9">
				'.$message['message_subject'].'
			</div>
		</div>
		<div class="row form_line">
			<div class="col-md-3">
				<label>'.TEXT_MESSAGES_CONTENT.':</label>
			</div>
			<div class="col-md-9">
				'.nl2br($message['message_content']).'
				</div>
				
		
		</div>
		<div class="row form_line">
				<div class="col-md-3">
						<label></label>
					</div>
				<div class="col-md-9">';
				
				
    if($message['message_link'] != '') {
        $output .= '<a class="btn btn-sm btn-primary" href="#SITE_URL#'.$message['message_link'].'">'.TEXT_MESSAGES_LINK_WATCH_NOW.'</a>';
    }
    else {
        $output .= '<form method="post" action="#SITE_URL#me/messages/create/">
    <button class="btn btn-sm btn-primary form-control" type="submit">'.TEXT_MESSAGES_REPLY.' <i class="fa fa-reply"></i></button>
    <input type="hidden" name="message_to_user_id_#SPAM_KEY#" value="'.md5($message_user['user_id'].$message_user['user_nickname']).'">
    <input type="hidden" name="message_reply_#SPAM_KEY#" value="1">
    <input type="hidden" name="message_subject_#SPAM_KEY#" value="'.$message['message_subject'].'">
    <input type="hidden" name="message_content_#SPAM_KEY#" value="'.$message['message_content'].'">
    </form>';
    }

    $output .= '</div>
    </div>
		</div>';
	
	
	$content_output = array('TITLE' => TEXT_MESSAGES_FROM_HEADER.' '.$message_user['user_nickname'].'',
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
	
	}
	else {
		header("location: ".SITE_URL."me/messages/inbox/");
        die();
	}
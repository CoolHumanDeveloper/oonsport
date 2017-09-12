<?php

function email_listing($box_type) {
    global $DB;
    $output = '';
    $sql = "SELECT * FROM message m, user_to_messages utm WHERE utm.user_id='".$_SESSION['user']['user_id']."' AND m.message_id = utm.message_id AND utm.message_box = '".$box_type."' ORDER BY m.message_date DESC";
	  
    $query = $DB->prepare($sql);
    $query->execute();
    $get_messages = $query->fetchAll();


    foreach ($get_messages as $messages) {
        if($messages['message_type']=="from") {
          $messages_user=get_user_details($messages['message_to_user_id']);
          $message_type="<i class=\"fa fa-share\"></i>";
        }
        else {
            $messages_user=get_user_details($messages['message_from_user_id']);
            $message_type="";
        }
        $message_readed="";
        if($messages['message_readed'] == 0 && $messages['message_to_user_id'] === $_SESSION['user']['user_id']){
            $message_readed='<span class="badge badge-error"> neu </span>';
        }

        // Freundschaftsanfrage
        if($messages['message_spam'] == 999) {
            $message_type="<i class=\"fa fa-user-plus\"></i> ";
        }
	  
        $output .= '
		<div class="row message_listing_element">
		<div class="col-xs-10 col-sm-10 col-md-10">
		<form method="post" action="#SITE_URL#me/messages/read/">
            <input type="hidden" name="message_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.$messages['message_key'].'">
                  <button class="list-group-item" type="submit">
                  <div class="hidden-xs hidden-sm col-md-3">
                  '.build_default_image($messages_user['user_id'],"50x50",'').'
                  </div>
                  <div class="col-xs-8 col-sm-8 col-md-5">
                  <strong>'.$message_type.$messages_user['user_nickname'].'</strong><br>'.$message_readed.' '.$messages['message_subject'].' 
                </div>
                <div class="col-xs-4 col-sm-4 col-md-4">
                '. date("d.m.y. H:i",strtotime($messages['message_date'])).'<br>
                 </div></button>
          </form>
              </div>
             <div class="col-xs-2 col-sm-2 col-md-2">

             ';

        if($box_type == "trash") {
          $output .= ' <form method="post" action="#SITE_URL#me/messages/'.$box_type.'/">
            <button  class="btn btn-xs btn-danger" title="'.TEXT_GLOBAL_DELETE_TOTALY.'"><i class="fa fa-trash"></i></button>
            <input type="hidden" name="message_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.$messages['message_key'].'">
            <input type="hidden" name="delete_totaly_message_'.$_SESSION['user']['secure_spam_key'].'" value="1">
            </form>';
        }
        else {
         $output .= ' <form method="post" action="#SITE_URL#me/messages/'.$box_type.'/">
            <button  class="btn btn-xs btn-danger" title="'.TEXT_GLOBAL_DELETE.'"><i class="fa fa-trash"></i></button>
            <input type="hidden" name="message_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.$messages['message_key'].'">
            <input type="hidden" name="delete_message_'.$_SESSION['user']['secure_spam_key'].'" value="1">
            </form>';
        }
        $reply_symbol='<i class="fa fa-reply"></i>';

        if($box_type == "send") {
         $reply_symbol='<i class="fa fa-envelope"></i>';
        }

        if($box_type == "trash") {
          $output .= '<form method="post" action="#SITE_URL#me/messages/'.$box_type.'/">
            <button  class="btn btn-xs btn-primary message_bottom_button" title="'.TEXT_GLOBAL_RECOVER.'"><i class="fa fa-undo"></i></button>
            <input type="hidden" name="message_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.$messages['message_key'].'">
            <input type="hidden" name="recover_message_'.$_SESSION['user']['secure_spam_key'].'" value="1">
            </form>';

        }
        else {

         $output .= ' 
          <form method="post" action="#SITE_URL#me/messages/create/">
          <input type="hidden" name="message_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.$messages['message_key'].'">
          <input type="hidden" name="message_reply_#SPAM_KEY#" value="1">
          <input type="hidden" name="message_reply_list_#SPAM_KEY#" value="1">
          <input type="hidden" name="message_to_user_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.md5($messages_user['user_id'].$messages_user['user_nickname']).'">
         <button  class="btn btn-xs btn-primary message_bottom_button" title="'.TEXT_GLOBAL_REPLY.'">'.$reply_symbol.'</button>
         </form>';
        }

        $output .= '</div></div>';
		
	  }
	  
	  if($output === '') {
          $output = set_system_message("error", TEXT_MESSAGES_NO_EMAILS);
      }

	return $output;
}

function get_unreaded_messages() {
	global $DB;
    $sql = "SELECT COUNT(m.message_id) AS count_messages FROM message m, user_to_messages utm WHERE utm.user_id='".$_SESSION['user']['user_id']."' AND m.message_id = utm.message_id AND m.message_readed=0 AND utm.message_type='to' ORDER BY m.message_date ASC";
    
    $query = $DB->prepare($sql);
    $query->execute();
    $count = $query->fetch();
	
	if($count['count_messages'] > 0) {
	   return '<span class="badge badge-error">'.$count['count_messages'].'</span>';
	}
	else {
		return "";
	}
}

function send_message_online($to_user_id,$from_user_id,$subject,$content,$systemlink,$systemMail) {
    global $DB;
    $spam = 0;

// Absender message_spam = 999 -> Systemnachricht, kann nicht beantwortet werden
    $message_key=md5($_SESSION['user']['user_id'].time().$to_user_id);

    if($systemMail==1) {
        $spam = "999";
    }


    $sql = "INSERT INTO `message` (`message_key`, `message_content`, `message_subject`, `message_date`, `message_readed`, `message_spam`, message_from_user_id, message_to_user_id, message_link) VALUES ('".$message_key."', '".strip_tags($content)."', '".$subject."', NOW(), 0, '".$spam."', '".$from_user_id."','".$to_user_id."', '".$systemlink."');";
    $query = $DB->prepare($sql);
    $query->execute();
    
    $last_message_id = $DB->lastInsertId();

    $sql = "INSERT INTO `user_to_messages` (`message_id`, `user_id`, `message_box`, `message_type`) VALUES ('".$last_message_id."', '".$to_user_id."', 'inbox', 'to')";
    if($systemMail == 0) {
        $sql .= ", ('".$last_message_id."', '".$from_user_id."', 'send', 'from')";
    }
    $query = $DB->prepare($sql);
    $query->execute();
}


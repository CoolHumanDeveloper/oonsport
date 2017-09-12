<?php 

 $output = '
	
	<div class="col-md-9 col-sm-12 content-box-right">
    <h4 class="profile">'.TEXT_MESSAGES_SEND.'</h4><br>
'.email_listing($_GET['sub_content_value']).'
	</div>';
	
	
	$content_output = array('TITLE' => TEXT_MESSAGES_MESSAGES. ' -> '.TEXT_MESSAGES_SEND,
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
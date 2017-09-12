<?php

is_user();

$sidebar='<div class="side_bar">

<div class="col-md-3 col-sm-12">
<ul class="list-group">
  <li class="list-group-item active start_register_header"> <a class="history_back" href="javascript:history.back();" title="'.TEXT_GLOBAL_BACK.'"><i class="fa fa-chevron-left"></i>
</a> Gespeicherte Suchen 
</li>
  </ul>
 <div class="list-group">
  <a href="#SITE_URL#me/search/default/" class="list-group-item">test <i class="fa fa-user"></i>
</a>
 
  </div>
   
	</div>
';


$output = $sidebar.'';
$content_output = array('TITLE' => 'Profile',
 'CONTENT' => $output,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
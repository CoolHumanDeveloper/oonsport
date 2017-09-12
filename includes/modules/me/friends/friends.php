<?php

$page = $_GET['page'] ? $_GET['page']-1 : 0;

if($_GET['page'] > 0) {
	$search_url_page_string="&page=".$_GET['page'];
}

$search_lat = $_SESSION['user']['user_lat'];
$search_lng = $_SESSION['user']['user_lng'];

// ACCEPT ODER DECLINE REQUEST 

if(isset($_POST['friendship_key_'.$_SESSION['user']['secure_spam_key']]) && strlen($_POST['friendship_key_'.$_SESSION['user']['secure_spam_key']])==32) {

    $sql = "SELECT ud.user_nickname, u.user_id, u.user_email, ud.user_firstname FROM user u, user_details ud WHERE MD5(CONCAT(u.user_id,ud.user_nickname))='".$_POST['friendship_user_id_'.$_SESSION['user']['secure_spam_key']]."' AND u.user_id = ud.user_id LIMIT 1";
    $query = $DB->prepare($sql);
    $query->execute();
    $friendship_user = $query->fetch();


	if($_SESSION['user']['user_id'] === $friendship_user['user_id']) {
		$_SESSION['system_temp_message'] .= set_system_message("error","Anfrage nicht möglich!");
		header("Location: ".SITE_URL."me/friends/friends/");
		die();
	}
	
	if($_POST['deletefriendship_'.$_SESSION['user']['secure_spam_key']]==1) {
		$sql = "DELETE FROM `user_friendship` WHERE 
		(MD5(CONCAT(user_id,friendship_user_id,friendship_date))='".$_POST['friendship_key_'.$_SESSION['user']['secure_spam_key']]."' AND user_id = '".$_SESSION['user']['user_id']."')
		OR
		(MD5(CONCAT(user_id,friendship_user_id,friendship_date))='".$_POST['friendship_key_'.$_SESSION['user']['secure_spam_key']]."' AND friendship_user_id = '".$_SESSION['user']['user_id']."')";
        $query = $DB->prepare($sql);
        $query->execute();
		if($query->rowCount() == 1) {
            $_SESSION['system_temp_message'] .= set_system_message("success",TEXT_PROFILE_FRIENDS_REQUESTS_SUCCESS_DELETED);		

            build_history_log($_SESSION['user']['user_id'],"friendship_pre_deleted",$friendship_user['user_id']);

            header("Location: ".SITE_URL."me/friends/friends/");
            die();

		}
		else {
            $_SESSION['system_temp_message'] .= set_system_message("error", TEXT_PROFILE_FRIENDS_REQUESTS_ERROR_DELETED);
            header("Location: ".SITE_URL."me/friends/friends/");
            die();
		
		}
		
	}
}

if(isset($_GET['view'])) {
	$_SESSION['user']['default_view'] = $_GET['view'];
	switch ($_GET['view']) {
		  
		  case ("grid"):
		  $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
		  $result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");
		  $_SESSION['user']['default_view']="grid";
		  break;
		  
			case ("list"):
		  $result_grid=file_get_contents(SERVER_PATH . "template/modules/search/list.html");
		  $result_item=file_get_contents(SERVER_PATH . "template/modules/search/list-item.html");
		  $_SESSION['user']['default_view']="list";
		  break;
		  
		  default:
			$result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
			$result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");
		  break;
	
	}
}
else {
			  if($_SESSION['user']['default_view'] == 'list')
		  {
			$result_grid=file_get_contents(SERVER_PATH . "template/modules/search/list.html");
			$result_item=file_get_contents(SERVER_PATH . "template/modules/search/list-item.html");
		  }
		  else
		  {
			
			$result_grid=file_get_contents(SERVER_PATH . "template/modules/search/grid.html");
			$result_item=file_get_contents(SERVER_PATH . "template/modules/search/grid-item.html");
		  }
}

$sql = "SELECT u.*, ud.*, uf.friendship_date, uf.friendship_user_id AS friend_id, uf.user_id AS request_id,
(acos(sin(RADIANS(ud.user_lat))*sin(RADIANS(".$search_lat."))+cos(RADIANS(ud.user_lat))*cos(RADIANS(".$search_lat."))*cos(RADIANS(ud.user_lng) - RADIANS(".$search_lng.")))*".RADIUS.") AS DISTANCE

 FROM 

 user u
 LEFT JOIN user_details AS ud ON u.user_id=ud.user_id
 LEFT JOIN user_friendship AS uf ON u.user_id = uf.user_id OR u.user_id = uf.friendship_user_id

 WHERE 
 uf.friendship_confirmed = 1 AND
u.user_id!='".$_SESSION['user']['user_id']."'  AND
(uf.user_id = '".$_SESSION['user']['user_id']."' OR uf.friendship_user_id = '".$_SESSION['user']['user_id']."') AND
u.user_status=1 
";

$query = $DB->prepare($sql);
$query->execute();
$total=$query->rowCount();

$sql.="
  ORDER BY uf.friendship_date DESC LIMIT ".$page*VIEW_PER_PAGE.",".VIEW_PER_PAGE."
 ";

if($total > 0) {
 $view_box='
<ul class="pagination view_box"><li><a href="?view=grid'.$search_url_page_string.'" aria-label="Grid">
<span class="glyphicon glyphicon-th" aria-hidden="true"></span></a></li><li><a href="?view=list'.$search_url_page_string.'" aria-label="Grid">
<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span></a></li></ul>
';
$pagination=build_site_pagination("me/friends/friends/",$total,$_GET['page'],VIEW_PER_PAGE,$search_url_terms_string,$view_box);
$pagination_footer=build_site_pagination("me/friends/friends/",$total,$_GET['page'],VIEW_PER_PAGE,$search_url_terms_string,"");


$result="";
$result.=$pagination.'
<div class="row">';
    
$query = $DB->prepare($sql);
$query->execute();
$get_search = $query->fetchAll();
foreach ($get_search as $search){
	
	$search_item=$result_item; // TEMPLATE SETZEN
	$search_item = str_replace("#SEARCH_ID#",md5($search['user_id'].$search['user_nickname']),$search_item);
	$search_item = str_replace("#SEARCH_IMAGE_GRID#",build_default_image($search['user_id'],"115x100","grid_image"),$search_item);
	$search_item = str_replace("#SEARCH_IMAGE_LIST#",build_default_image($search['user_id'],"100x100","list_image"),$search_item);
	$search_item = str_replace("#SEARCH_NAME#",$search['user_nickname'],$search_item);
	$search_item = str_replace("#SEARCH_COUNTRY#",strtoupper($search['user_country'])." - ",$search_item);
	//$search_item = str_replace("#SEARCH_ZIPCODE#",$search['user_zipcode'],$search_item);
	$search_item = str_replace("#SEARCH_CITY#",get_city_name($search['user_geo_city_id'],$search['user_country']),$search_item);
    $search_item = str_replace("#DISTANCE#","",$search_item);
	
	$friendship_status='<form method="post" action="#SITE_URL#me/friends/friends/" >		
        <input name="friendship_key_'.$_SESSION['user']['secure_spam_key'].'" value="'.md5($search['request_id'].$search['friend_id'].$search['friendship_date']).'" type="hidden">
        <input name="friendship_user_id_'.$_SESSION['user']['secure_spam_key'].'" value="'.md5($search['user_id'].$search['user_nickname']).'" type="hidden">
        <button name="deletefriendship_'.$_SESSION['user']['secure_spam_key'].'" class="btn btn-xs btn-danger" type="submit" value="1" ><i class="fa fa-trash"></i></button>
        </form>
		';
	$search_item = str_replace("#SPORTS#",$friendship_status,$search_item);
	$result.=$search_item;
	
}
$result.='</div>'.$pagination_footer;
$output = '<div class="col-md-9 col-sm-12 content-box-right main_content">
<h4 class="profile">'.TEXT_PROFILE_FRIENDS_FRIENDS.' </h4><br>
'.$result.'

</div>';
}
else {
	$output = '<div class="col-md-9 col-sm-12 content-box-right main_content">'.set_system_message("error", TEXT_PROFILE_FRIENDS_NO_FRIENDS).'</div>';
}
	

$content_output = array('TITLE' => TEXT_PROFILE_FRIENDS_FRIENDS,
 'CONTENT' => $sidebar.$output.$sub_sidebar,
 'HEADER_EXT' => '',
  'FOOTER_EXT' => '');
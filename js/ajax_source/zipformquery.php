<?php
function debug($obj){
    $fp = fopen("debug.txt", 'a');
    fputs($fp, print_r($obj, true) ."\n");
    fclose($fp);
}
debug("========================".__LINE__);
include ("../../includes/config.php");

debug("========================".__LINE__);
debug($_GET['geozone']);
debug("========================".__LINE__);
// if the 'term' variable is not sent with the request, exit
if ( !isset($_REQUEST['term']) ) exit;
	if(strlen($_REQUEST['term']) < 4) {
        $limit = "LIMIT  0,20";
    }
	if(!is_numeric($_REQUEST['term'])) {
		$sql="SELECT DISTINCT(place), `geodb_world`.`".$_GET['geozone']."`.* FROM `geodb_world`.`".$_GET['geozone']."` WHERE `place` LIKE '". $_REQUEST['term'] ."%' AND pobox=0 GROUP BY place ORDER BY postalcode ASC " . $limit;
	}
	else {
        $sql="SELECT * FROM `geodb_world`.`".$_GET['geozone']."` WHERE `postalcode` LIKE '". $_REQUEST['term'] ."%' AND pobox=0 " . $limit;;
	}

debug("========================".__LINE__);
debug($sql);
$query = $DB->prepare($sql);
$query->execute();
$result = $query->fetchAll();
debug($result);

// loop through each zipcode returned and format the response for jQuery
$data = array();
if ( $result && $query->rowCount() > 0 ) {
	foreach ($result as $row) {
        if(!is_numeric($_REQUEST['term'])) {
            $label_string=$row['place'];
        }
        else {
            $label_string=$row['postalcode'] .' '. $row['place'] .' '. $row['place4'];
        }

        $data[] = array(
            'label' => $label_string ,
            'value' => $row['postalcode'],
            'city_id' => $row['city_id']
        );
	}
}
// jQuery wants JSON data
echo json_encode($data);
flush();
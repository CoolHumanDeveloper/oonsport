<?php
require("../../includes/config.php");

if(isset($_GET['video'])) {
   $sql = "SELECT m.media_url FROM user_media m, user_to_media um WHERE m.media_file='" . $_GET['video'] . "' AND um.media_id=m.media_id LIMIT 1";

$query = $DB->prepare( $sql );
$query->execute();
$videoFile = $query->fetch(); 
}
?><!doctype html>

<head>

   <!-- player skin -->
   <link rel="stylesheet" href="<?php echo SITE_URL;?>js/flowplayer/skin/skin.css">

   <!-- site specific styling -->
   <style>
   body { font: 12px "Myriad Pro", "Lucida Grande", sans-serif; text-align: center; padding: 0;}
   .flowplayer { width: 100%; }
   </style>

   <!-- for video tag based installs flowplayer depends on jQuery 1.7.2+ -->
   <script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>

   <!-- include flowplayer -->
   <script src="<?php echo SITE_URL;?>js/flowplayer/flowplayer.min.js"></script>

</head>

<body>
   <!-- the player -->
   <div class="flowplayer" data-swf="<?php echo SITE_URL;?>js/flowplayer/flowplayerhls.swf" data-ratio="0.4167">
      <video>
         <source type="video/mp4" src="<?php echo SITE_IMAGE_URL.'user/'.$videoFile['media_url'];?>">
      </video>
   </div>

</body>

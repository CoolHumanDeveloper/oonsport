<?php
session_start();

function debug1($obj){
    $fp = fopen("debug.txt", 'a');
    fputs($fp, print_r($obj, true) . "\n");
    fclose($fp);
}

require("includes/config.php");

///////////////////////////////////////////
//     copyright 2015					///
//     comfort design - medienagntur	///
//     Carsten Ahlers					///
//     Idee oon-sport.de				///
///////////////////////////////////////////



// Ausgabe der Seite 
echo load_template();

mysql_close(DB_LINKED);
 ?>
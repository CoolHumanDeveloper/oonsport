<?php
function debug($obj){
    if (!DEBUG_MODE) return;
    $fp = fopen("debug.txt", "a");
    fputs($fp, print_r($obj, true) . "\n");
    fclose($fp);
}
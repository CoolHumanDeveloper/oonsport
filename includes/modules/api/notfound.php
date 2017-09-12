<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/5/2017
 * Time: 7:14 AM
 */
header(HEADER_NOTFOUND, true, HEADER_NOTFOUND_CODE);
$response['error'] = MISSING_API;
die(json_encode($response));
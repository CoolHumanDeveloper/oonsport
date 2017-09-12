<?php
if (!isset($nickname)) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

if (check_user_nickname($nickname) == true) {
    header(HEADER_SERVERERR);
    $response['code'] = DUPLICATED_NICKNAME;
    die(json_encode($response));
}

if(strlen($nickname) < 3) {
    header(HEADER_SERVERERR);
    $response['code'] = SHORT_NICKNAME;
    die(json_encode($response));
}
?>
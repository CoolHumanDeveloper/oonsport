<?php
if (!isset($email)) {
    header(HEADER_SERVERERR);
    $response['code'] = MISSING_PARAMETER;
    die(json_encode($response));
}

if (check_email($email) == true) {
    if (check_user_email($email)){
        header(HEADER_SERVERERR);
        $response['code'] = DUPLICATED_EMAIL;
        die(json_encode($response));
    }
} else {
    header(HEADER_SERVERERR);
    $response['code'] = INVALID_EMAIL;
    die(json_encode($response));
}
?>
<?php
/**
 * Created by PhpStorm.
 * User: R888
 * Date: 9/5/2017
 * Time: 7:12 AM
 */

define('JWT_KEY', "ABCDEFGHIJKLMN0PORSTUVWXYZ");
define('JWT_ALG', "HS512");

define('HEADER_UNAUTHORIZED', 'HTTP/1.1 401 Unauthorized');
define('HEADER_UNAUTHORIZED_CODE', 401);
define('HEADER_FORBIDDEN', 'HTTP/1.1 403 Forbidden');
define('HEADER_FORBIDDEN_CODE', 403);
define('HEADER_NOTFOUND', 'HTTP/1.1 404 Not Found');
define('HEADER_NOTFOUND_CODE', 404);
define('HEADER_SERVERERR', 'HTTP/1.1 500 Internal Server Error');
define('HEADER_SERVERERR_CODE', 500);

// XYZ
// X: API Error (5)
// Y: Error Kind (0: missing, 1: invalid, 2: duplicate)
// Z: Error Module (0: global, 1: user, 2: )
define('MISSING_API', 500);
define('MISSING_PARAMETER', 501);
define('MISSING_TOKEN', 502);
define('INVALID_TOKEN', 503);
define('INVALID_EMAIL', 510);
define('INVALID_PASSWORD', 511);
define('INVALID_STATUS', 512);
define('DUPLICATED_EMAIL', 520);
define('DUPLICATED_NICKNAME', 521);
define('SHORT_NICKNAME', 531);
define('FAIL_USER', 541);
define('FAIL_SENDMAIL', 542);
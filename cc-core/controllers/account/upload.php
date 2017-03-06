<?php

/**
 * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use upload_info instead
 */
header("HTTP/1.1 301 Moved Permanently");
header('Location: ' . HOST . '/account/upload/video/');
exit();

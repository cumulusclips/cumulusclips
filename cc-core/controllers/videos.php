<?php

/**
 * @deprecated Deprecated in 2.5.0, removed in 2.6.0. Use browse instead
 */
header("HTTP/1.1 301 Moved Permanently");
header('Location: ' . HOST . preg_replace('/^\/videos/i', '/browse', $_SERVER['REQUEST_URI']));
exit();
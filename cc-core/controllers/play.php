<?php

header("HTTP/1.1 301 Moved Permanently");
header('Location: ' . HOST . preg_replace('/^\/videos/i', '/watch', $_SERVER['REQUEST_URI']));
exit();
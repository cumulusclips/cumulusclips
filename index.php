<?php

$value = 'a:10:{i:0;s:31:"FeedBurner_FeedSmith_Plugin.php";i:1;s:19:"akismet/akismet.php";i:2;s:43:"all-in-one-seo-pack/all_in_one_seo_pack.php";i:3;s:32:"disqus-comment-system/disqus.php";i:4;s:50:"google-analytics-for-wordpress/googleanalytics.php";i:5;s:36:"google-sitemap-generator/sitemap.php";i:6;s:17:"no-self-pings.php";i:7;s:39:"syntaxhighlighter/syntaxhighlighter.php";i:8;s:19:"wptouch/wptouch.php";i:9;s:27:"ylsy_permalink_redirect.php";}';




$active_plugins = array (
    'HelloWorld'
);



echo serialize($active_plugins);

//echo '<pre>', print_r(unserialize($value),true), '</pre>';

?>
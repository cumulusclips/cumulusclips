<?php

header ("Content-Type: text/xml");
$xml_header = '<?xml version="1.0" encoding="UTF-8"?>';
$xml_root = '<urlset></urlset>';
$xml_frame = $xml_header . $xml_root;


$xml = new SimpleXMLElement ($xml_frame);
$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
echo $xml->asXML()

?>
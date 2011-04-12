<?php

$limit = 7;
$tag1 = 0;
$tag2 = 0;
$tags = array();
$print = array();



// Retrieve most viewed videos
$query = "SELECT * FROM videos WHERE status = 6 ORDER BY views desc LIMIT $limit";
$result = $db->Query ($query);
while ($row = mysql_fetch_array ($result)) {
	$single_tags = explode (" ", $row['tags']);
	$tags = array_merge ($tags, $single_tags);		
}



$count = count ($tags);
$per_size = floor ($count/3);


foreach ($tags as $tag_word) {

	if ($tag1 <= $per_size) {
		$tag1++;
		$print[] = '<li><a class="tag1" href="' . HOST . '/search/?keyword=' . $tag_word . '" title="' . $tag_word . '">' . $tag_word . "</a></li>\n";
			
	} elseif ($tag2 <= $per_size) {
		$tag2++;
		$print[] = '<li><a class="tag2" href="' . HOST . '/search/?keyword=' . $tag_word . '" title="' . $tag_word . '">' . $tag_word . "</a></li>\n";
		
	} else {
		$print[] = '<li><a class="tag3" href="' . HOST . '/search/?keyword=' . $tag_word . '" title="' . $tag_word . '">' . $tag_word . "</a></li>\n";
	}
		
}

shuffle ($print);

foreach ($print as $value) {
	echo $value;
}


?>
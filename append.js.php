<?php

header('Content-type: application/javascript; charset=utf-8');

if ( empty($_GET['source']) ) {
	exit("alert('Need `source`.');");
}

$source = trim($_GET['source']);
if ( !preg_match('#^[^ /]+ [^ /]+ https?://[^ ]+$#', $source) ) {
	exit("alert('Invalid `source`. Must be type + date/title + url.');");
}

$queue = trim(file_get_contents('.queue'));
$queue .= "\n$source\n";
$saved = file_put_contents('.queue', ltrim($queue));

if ( !$saved ) {
	exit("prompt('Failed to save to `.queue`. Copy the following', '" . addslashes($source) . "');");
}

exit("alert('Saved!');");

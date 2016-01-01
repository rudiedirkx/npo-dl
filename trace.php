<?php

$url = 'http://www.npo.nl/floortje-naar-het-einde-van-de-wereld/15-01-2015/BNN_101372381';
$prid = basename($url);

// Download player JS for version
$playerJS = file_get_contents('http://npoplayer.omroep.nl/csjs/npoplayer-min.js');
if ( !preg_match('#npoplayer.version\s*=\s*[\'"]([^\'"]+)[\'"];#', $playerJS, $match) ) {
	exit("\nInvalid Player JS?\n");
}
$version = $match[1];
var_dump($version);

// Download JS token
$tokenJS = file_get_contents('http://ida.omroep.nl/npoplayer/i.js?s=' . urlencode($url));
if ( !preg_match('#npoplayer.token\s*=\s*[\'"]([^\'"]+)[\'"];#', $tokenJS, $match) ) {
	exit("\nInvalid Token JS?\n");
}
$token = $match[1];
var_dump($token);

// Download stream info # 1
$callback = 'jQuery18308337543795350939_1448812643015';
$jsonp = file_get_contents('http://ida.omroep.nl/odi/?prid=' . urlencode($prid) . '&puboptions=adaptive,h264_bb,h264_sb,h264_std&adaptive=yes&part=1&token=' . urlencode($token) . '&callback=' . $callback . '&version=' . urlencode($version) . '&_=' . (time() * 1000));
if ( !preg_match('#' . $callback . '\((.+)\)#', $jsonp, $match) ) {
	exit("\nInvalid stream info # 1?\n");
}
$json = $match[1];
echo $json . "\n\n";

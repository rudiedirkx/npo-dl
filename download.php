<?php

$cacheDir = __DIR__ . '/cache/';
$regexes = array(
	'tegenlicht' => '#(\d+\-)(\d+)(\.ts)#',	// Tegenlicht
	'dwdd' => '#(\d+\-)(\d+)(\.ts)#',		// De Wereld Draait Door
	'dailymotion' => '#(frag\()(\d+)(\))#',	// DailyMotion
	'fevdw' => '#(\d+\-)(\d+)(\.ts)#',		// Floortje naar het Einde Van De Wereld
	'fvila' => '#(\d+\-)(\d+)(\.ts)#',		// Freek Vonk In Latijns-Amerika
);

$type = @$_SERVER['argv'][1];
$name = @$_SERVER['argv'][2];
$base = @$_SERVER['argv'][3];

echo "\n";

if ( !$type || !$name || !$base || !isset($regexes[$type]) || !preg_match('#https?://#', $base) ) {
	echo "Call: `php download.php TYPE FILENAME URL`\n\n";
	exit;
}

$base = preg_replace($regexes[$type], '$1%%%$3', $base);
echo "$base\n\n";

$ua = @$_SERVER['HTTP_USER_AGENT'] ?: 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36';

$chunkCacheDir = $cacheDir . sha1($base) . '/';
if ( !is_dir($chunkCacheDir) && !@mkdir($chunkCacheDir) ) {
	exit("Can't create chunk cache dir\n");
}

$chunks = [];
for ( $i = 1; $i <= 500; $i++ ) {
	$url = str_replace('%%%', $i, $base);
	$cacheFile = sha1($url) . '.ts';

	$status = 'cached';
	if ( !file_exists($chunkCacheDir . $cacheFile) ) {
		$_time = microtime(1);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $ua,
		));
		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if ( $info['http_code'] != 200 ) {
			echo "\nDone downloading?\n\n";
			break;
		}

		if ( !@file_put_contents($chunkCacheDir . $cacheFile, $data) ) {
			exit("Can't cache chunk # $i\n");
		}

		$_took = microtime(1) - $_time;
		$status = 'downloaded in ' . round($_took * 1000) . ' ms';

		usleep(250000);
	}

	echo "$i - $status\n";

	$chunks[] = $cacheFile;

	flush();
}

echo "\n\nCombining into one .ts file...\n\n";

$videoFile = preg_replace('#\.ts$#', '', $name) . '.ts';
$progress = '';
foreach ($chunks as $i => $chunkFile) {
	file_put_contents($cacheDir . $videoFile, file_get_contents($chunkCacheDir . $chunkFile), FILE_APPEND);

	// Backspace old progress
	echo str_repeat(chr(8), strlen($progress));

	// Print new progress
	$done = round(($i+1) / count($chunks)* 100);
	echo $progress = 'Compiling... ' . sprintf('% 3d %%', $done);
}

echo "\n\nREADY: $videoFile\n\n";

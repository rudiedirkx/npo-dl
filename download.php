<?php

$cacheDir = __DIR__ . '/cache/';
$regexes = array(
	'dailymotion' => '#(frag\()(\d+)(\))#',   // DailyMotion
	'tegenlicht'  => '#(\d+\-)(\d+)(\.ts)#',  // Tegenlicht
	'dwdd'        => '#(\d+\-)(\d+)(\.ts)#',  // De Wereld Draait Door
	'fevdw'       => '#(\d+\-)(\d+)(\.ts)#',  // Floortje naar het Einde Van De Wereld
	'fvila'       => '#(\d+\-)(\d+)(\.ts)#',  // Freek Vonk In Latijns-Amerika
	'hunt'        => '#(\d+\-)(\d+)(\.ts)#',  // The Hunt
	'yangtze'     => '#(\d+\-)(\d+)(\.ts)#',  // Langs de oevers van de Yangtze
	'zembla'      => '#(\d+\-)(\d+)(\.ts)#',  // Zembla
	'katjas'      => '#(\d+\-)(\d+)(\.ts)#',  // Katja's bodyscan
	'noordzuid'   => '#(\d+\-)(\d+)(\.ts)#',  // Noord-Zuid
	'demol'       => '#(\d+\-)(\d+)(\.ts)#',  // Wie Is De Mol?
    'fstropers'   => '#(\d+\-)(\d+)(\.ts)#', // Freek tegen Stropers
);

$args = @$_SERVER['argv'] ?: array();

$type = @$args[1];
$name = @$args[2];
$base = @$args[3];

echo "\n";

if ( !$type || !$name || !$base || !isset($regexes[$type]) || !preg_match('#https?://#', $base) ) {
	echo "Call: `php download.php TYPE FILENAME URL`\n\n";
	echo "TYPE:      " . implode(', ', array_keys($regexes)) . "\n";
	echo "FILENAME:  Any valid filename, *without* the extension (.ts)\n";
	echo "URL:       Full .ts URL of any part. Use the bookmarklet to get it\n";
	echo "\n";
	exit(1);
}

$base = preg_replace($regexes[$type], '$1%%%$3', $base);
echo "$base\n\n";

$ua = @$_SERVER['HTTP_USER_AGENT'] ?: 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36';

$chunkCacheDir = $cacheDir . sha1($base) . '/';
if ( !is_dir($chunkCacheDir) && !@mkdir($chunkCacheDir) ) {
	exit("Can't create chunk cache dir\n");
}

echo $chunkCacheDir . "\n\n";

$videoFile = preg_replace('#\.ts$#', '', $type . '-' . $name) . '.ts';
echo "Downloading into $videoFile\n\n";

$chunks = [];
for ( $i = 1; $i <= 500; $i++ ) {
	$url = str_replace('%%%', $i, $base);
	$_file = sha1($url) . '.ts';
	$cacheFile = sprintf('%04d_%s', $i, $_file);

	// Rewrite old filename to new filename
	if ( file_exists($chunkCacheDir . $_file) ) {
		rename($chunkCacheDir . $_file, $chunkCacheDir . $cacheFile);
	}

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

echo "\n\nCombining into $videoFile ...\n\n";

$_start = microtime(1);

// Use file_(get|put)_contents()
// $progress = '';
// foreach ($chunks as $i => $chunkFile) {
// 	file_put_contents($cacheDir . $videoFile, file_get_contents($chunkCacheDir . $chunkFile), FILE_APPEND);
// 	// Backspace old progress
// 	echo str_repeat(chr(8), strlen($progress));
// 	// Print new progress
// 	$done = round(($i+1) / count($chunks)* 100);
// 	echo $progress = 'Compiling... ' . sprintf('% 3d %%', $done);
// }

// Use commandline `cat`
// $infiles = implode(' ', array_map(function($chunkFile) use ($chunkCacheDir) {
// 	return $chunkCacheDir . $chunkFile;
// }, $chunks));
// $outfile = $cacheDir . $videoFile;
// $cmd = 'cat ' . $infiles . ' > ' . $outfile;
// exec($cmd);

// Use commandline `cat` smarter, with filename order
$outfile = $cacheDir . $videoFile;
$cmd = 'cat ' . $chunkCacheDir . '/*.ts > ' . $outfile;
exec($cmd);

echo "\n\nCompiled in " . round(microtime(1) - $_start) . " sec.\n\n";

echo "\n\nREADY: $videoFile\n\n";

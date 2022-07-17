<?php
// basic path definitions
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(__FILE__) . "/..");

//loading default settings if exist
if (!file_exists(ROOT . DS . "inc" . DS . "config.inc.php")) {
	exit("Rename /inc/example.config.inc.php to /inc/config.inc.php first!");
}
include_once ROOT . DS . "inc" . DS . "config.inc.php";

//loading core and controllers
include_once ROOT . DS . "inc" . DS . "core.php";

if ($_REQUEST["ip"] == "pls") {
	exit(getUserIP());
}

loadAllContentControllers();

$shafile = ROOT . DS . "data" . DS . "sha1.csv";

if (!file_exists($shafile)) {
	exit(json_encode(["status" => "err", "reason" => "File not found"]));
}

$handle = fopen($shafile, "r");
if ($handle) {
	$data = [];

	while (($line = fgets($handle)) !== false) {
		$hash = trim(substr($line, 41));
		$data[] = [
			"hash" => $hash,
			"url" => URL . $hash,
		];
	}

	fclose($handle);

	$answer = ["data" => $data];
	$answer["status"] = "ok";

	exit(json_encode($answer));
} else {
	exit(json_encode(["status" => "err", "reason" => "Can't open file"]));
}

function getInfoAboutHash($hash)
{
	$file = ROOT . DS . "data" . DS . $hash . DS . $hash;
	if (!file_exists($file)) {
		return [
			"status" => "err",
			"reason" => "File not found",
			"hash" => $hash,
		];
	}
	$size = filesize($file);
	$size_hr = renderSize($size);
	$content_type = exec("file -bi " . escapeshellarg($file));
	if (
		$content_type &&
		strpos($content_type, "/") !== false &&
		strpos($content_type, ";") !== false
	) {
		$type = $content_type;
		$c = explode(";", $type);
		$type = $c[0];
	}

	return [
		"hash" => $hash,
		"url" => URL . $hash,
		"size_bytes" => $size,
		"size_interpreted" => $size_hr,
		"type" => $type,
		"type_interpreted" => getTypeOfFile($file),
	];
}

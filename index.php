<?php
// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Get the requested path
$get = $_GET['get'] ?? '';

if (!$get) {
    http_response_code(400);
    echo "Missing 'get' parameter.";
    exit();
}

// Build the remote URL
$mpdUrl = 'https://in-mc-fdlive.fancode.com/' . $get;

// Set headers for fetching remote content
$mpdheads = [
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36\r\n",
        'follow_location' => 1,
        'timeout' => 5
    ]
];
$context = stream_context_create($mpdheads);

// Fetch the content
$res = @file_get_contents($mpdUrl, false, $context);

if ($res === false) {
    http_response_code(502);
    echo "Failed to fetch resource from $mpdUrl";
    exit();
}

// Detect file extension for content-type
$ext = strtolower(pathinfo($get, PATHINFO_EXTENSION));
switch ($ext) {
    case 'mpd':
        header("Content-Type: application/dash+xml");
        break;
    case 'm3u8':
        header("Content-Type: application/vnd.apple.mpegurl");
        break;
    case 'ts':
        header("Content-Type: video/MP2T");
        break;
    default:
        header("Content-Type: application/octet-stream");
        break;
}

// Output the fetched content
echo $res;
?>

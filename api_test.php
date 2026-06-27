<?php
$ctx = stream_context_create([
    "http" => [
        "method" => "GET",
        "header" => "Accept: application/json\r\nOrigin: https://corexgaming.duckdns.org\r\n",
        "ignore_errors" => true
    ]
]);
$result = file_get_contents("https://corexgaming.duckdns.org/api/orders", false, $ctx);
echo "Status: " . (isset($http_response_header[0]) ? $http_response_header[0] : "no headers") . "\n";
echo "Body: " . $result . "\n";

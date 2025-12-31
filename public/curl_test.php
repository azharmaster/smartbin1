<?php
if (function_exists('curl_version')) {
    echo "cURL is enabled: " . curl_version()['version'];
} else {
    echo "cURL is NOT enabled!";
}

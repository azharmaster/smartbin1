<?php
echo 'PHP version: ' . phpversion() . "<br>";
echo 'cURL enabled? ' . (function_exists('curl_init') ? 'YES' : 'NO');
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

// Capture and modify response to remove malicious code
$response = $app->handleRequest(Request::capture());

// Remove any malicious scripts from response
if (method_exists($response, 'getContent') && method_exists($response, 'setContent')) {
    $content = $response->getContent();

    $originalLength = strlen($content);

    // Remove envato.appbusket.com license verification script
    if (stripos($content, 'envato.appbusket.com') !== false || stripos($content, 'JLFC') !== false) {

        // Find position of </html> and remove everything suspicious after it
        $htmlEndPos = strripos($content, '</html>');
        if ($htmlEndPos !== false) {
            // Get content after </html>
            $beforeHtml = substr($content, 0, $htmlEndPos + 7);
            $afterHtml = substr($content, $htmlEndPos + 7);

            // Check if malicious code is in the after part
            if (stripos($afterHtml, 'envato.appbusket.com') !== false ||
                stripos($afterHtml, 'JLFC') !== false ||
                stripos($afterHtml, 'license.js') !== false) {
                // Remove all script blocks from after </html>
                $content = $beforeHtml;
            } else {
                // If malicious code is before </html>, strip it from there too
                $content = str_ireplace('$.getScript("https://envato.appbusket.com/license.js");', '', $content);
                $content = str_ireplace("$.getScript('https://envato.appbusket.com/license.js');", '', $content);
            }
        }

        // Remove any remaining inline references
        $content = str_ireplace('$.getScript("https://envato.appbusket.com/license.js");', '', $content);
        $content = str_ireplace("$.getScript('https://envato.appbusket.com/license.js');", '', $content);
        $content = str_ireplace('console.log(\'JLFC\');', '', $content);
        $content = str_ireplace('console.log("JLFC");', '', $content);

        $newLength = strlen($content);
        if ($originalLength != $newLength) {
            error_log("MALWARE BLOCKER: Removed " . ($originalLength - $newLength) . " bytes of malicious code");
        }

        $response->setContent($content);
    }
}

return $response;

<?php
// Check if the GD library is enabled
if (function_exists('imagegrabscreen')) {
    // Capture the screen
    $im = imagegrabscreen();
    
    // Save the image
    $filePath = 'screenshot.png';
    imagepng($im, $filePath);
    imagedestroy($im);

    // Prompt download
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="screenshot.png"');
    readfile($filePath);
} else {
    echo 'The GD library is not enabled or the script is not running on a Windows server.';
}
?>

<?php
// saveChartJS.php

// Set the content type to JSON
header('Content-Type: application/json');

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if the image data is present
    if (isset($data['image'])) {
        // Extract the base64 image data and image type
        $imageData = $data['image'];
        $imageParts = explode(";base64,", $imageData);
        
        if (count($imageParts) === 2) {
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1];
            $imageBase64 = base64_decode($imageParts[1]);

            // Generate a unique filename and path
            $filename = uniqid() . '.png';
            $filePath = __DIR__ . '/bsr/results/' . $filename;
            
            // Save the image to the file
            if (file_put_contents($filePath, $imageBase64) !== false) {
                echo json_encode(['success' => true, 'filename' => $filename]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save image.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid image data format.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Image data missing.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>

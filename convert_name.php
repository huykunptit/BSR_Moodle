<?php
// Get the current date in the desired format (e.g., DDMMYY)
$currentDate = date('dmy');

// Construct the filename with the desired format
$filename = $currentDate . '_Filemau_NhapNguoidung.csv';

// Path to the original CSV file
$filepath ='//blocks//iomad_company_admin//BSR_User_Import_Template.csv';

// Check if the file exists
if (file_exists($filepath)) {
    // Set headers to prompt download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Read the file and output its contents
    readfile($filepath);
    exit;
} else {
    echo "File not found.";
}
?>

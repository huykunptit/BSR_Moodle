<?php

/**
 * Cocoon Form Builder integration for Moodle
 *
 * @package    cocoon_form_builder
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once('../../config.php');
global $USER, $DB, $CFG;
require_login();
require_sesskey(); // CSRF Protection

$PAGE->set_url('/local/cocoon_form_builder/updateinsert.php');
$PAGE->set_context(context_system::instance());

$_RESTREQUEST = file_get_contents("php://input");
$_POST = json_decode($_RESTREQUEST, true);

$attachments = array();
if (isset($_POST['attachments']) && $_POST['attachments'] != '') {
    $allowedfileExtensions = array(
        "image/png", "image/jpeg", "image/bmp", "image/vnd.microsoft.icon", 
        "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", 
        "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-powerpoint", "application/vnd.openxmlformats-officedocument.presentationml.presentation", 
        "text/csv", "application/zip"
    );

    foreach ($_POST['attachments'] as $key => $value) {
        define('UPLOAD_DIR', $CFG->dataroot);
        $image_parts = explode(";base64,", $value);

        if (count($image_parts) > 1) {
            $file_type_aux = explode(":", $image_parts[0]);
            $file_type = $file_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            // Determine file extension based on MIME type
            $fileExtension = get_file_extension($file_type, $allowedfileExtensions);
            if ($fileExtension) {
                $fileName = uniqid() . '.' . $fileExtension;
                $file = UPLOAD_DIR . "/" . $fileName;

                // Save the file to disk
                if (file_put_contents($file, $image_base64)) {
                    // Create a base64-encoded version for storage if needed
                    $fData = base64_encode(file_get_contents($file));
                    $src = 'data:' . mime_content_type($file) . ';base64,' . $fData;
                    $attachments[] = $src;
                } else {
                    // Handle error during file save
                    echo json_encode(['status' => 'error', 'message' => 'File could not be saved']);
                    exit;
                }
            } else {
                // Handle invalid file type
                echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
                exit;
            }
        } else {
            $attachments[] = $value; // If not base64, add directly
        }
    }
}

// Prepare email reply object
$reply = new stdClass();
$reply->subject = $_POST['email_subject'];
$reply->message = $_POST['email_message'];
$reply->attachments = $attachments;

// Prepare form data
$obj = new stdClass();
$obj->title = isset($_POST['title']) ? $_POST['title'] : '';
$obj->json = $_POST['json'];
$obj->data = json_encode($reply);
$obj->url = $_POST['url'];
$obj->confirm_message = $_POST['confirm_message'];
$obj->recipients = $_POST['recipients'];
$obj->status = $_POST['status'];
$obj->ajax = $_POST['ajax'];

// Generate title if empty
if (empty(trim($obj->title))) {
    $nextId = $DB->get_field_sql("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_NAME = 'mdl_cocoon_form_builder_forms'");
    $obj->title = "Form #" . $nextId;
}

// Insert or update form data
try {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update record
        $obj->id = $_POST['id'];
        $DB->update_record('cocoon_form_builder_forms', $obj);
        echo json_encode(['status' => 'success', 'message' => 'Form updated successfully']);
    } else {
        // Insert new record
        $resultId = $DB->insert_record('cocoon_form_builder_forms', $obj, true, false);
        echo json_encode(['status' => 'success', 'message' => 'Form created successfully', 'form_id' => $resultId]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

/**
 * Get the file extension based on MIME type.
 *
 * @param string $file_type The MIME type of the file.
 * @param array $allowedfileExtensions The list of allowed MIME types.
 * @return string|null The corresponding file extension or null if not valid.
 */
function get_file_extension($file_type, $allowedfileExtensions) {
    $extensions = [
        "image/png" => "png", "image/jpeg" => "jpg", "image/bmp" => "bmp", "application/pdf" => "pdf",
        "application/msword" => "doc", "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
        "application/vnd.ms-excel" => "xls", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",
        "application/vnd.ms-powerpoint" => "ppt", "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx",
        "text/csv" => "csv", "application/zip" => "zip"
    ];
    return isset($extensions[$file_type]) && in_array($file_type, $allowedfileExtensions) ? $extensions[$file_type] : null;
}

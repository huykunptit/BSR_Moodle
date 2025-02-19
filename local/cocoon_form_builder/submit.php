<?php

/**
 * Cocoon Form Builder integration for Moodle
 *
 * @package    cocoon_form_builder
 */

require(__DIR__.'/../../config.php');
require(__DIR__.'/../../blocks/cocoon_form/phpmailer/class.custommailer.php');
require_login();
defined('MOODLE_INTERNAL') || die();
global $DB, $USER;

// Verify the sesskey to prevent CSRF attacks
require_sesskey();

// Get REST request data
$_RESTREQUEST = file_get_contents("php://input");
$_POST = json_decode($_RESTREQUEST, true);

// Check if ID is provided
if (isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    // Secure SQL query with Moodle's get_record_sql and parameter binding
    $sql = "SELECT * FROM {cocoon_form_builder_forms} WHERE id = :id";
    $form = $DB->get_record_sql($sql, array('id' => $id));

    // Check if the form exists
    if (!$form) {
        echo json_encode(['status' => 'error', 'message' => 'Form not found']);
        exit;
    }

    $json = json_decode($form->json);
    $emails = explode(";", $form->recipients);
    $confirmMessage = !empty($form->confirm_message) ? $form->confirm_message : "Thank you! Your message has been sent!";
    $autoRepEmails = [];
    $autoRepData = json_decode($form->data);

    // Process form data
    $data = explode('&', $_POST["data"]);
    $obj = new stdClass();
    foreach ($data as $key => $value) {
        $item = explode('=', $value);
        $decodedValue = urldecode($item[1]);
        $obj->{$item[0]} = isset($obj->{$item[0]}) ? $obj->{$item[0]} . ", " . $decodedValue : $decodedValue;
    }

    // Start building the HTML message
    $htmlmessage = "Content: " . "<br />";
    $fileUpload = $fileExtension = [];
    foreach ($json as $key => $value) {
        if (in_array($value->type, ["text", "textarea", "autocomplete", "date", "number", "radio-group", "select"])) {
            if (isset($obj->{$value->name})) {
                $htmlmessage .= $value->label . ": " . processData($obj->{$value->name}) . "<br />";

                if (checkemail($obj->{$value->name})) {
                    $autoRepEmails[] = $obj->{$value->name};
                }
            }
        }
        // Handle checkboxes
        if ($value->type == "checkbox-group" || $value->type == "checkbox") {
            $machine_name = $value->name . '%5B%5D';
            if (isset($obj->{$machine_name})) {
                $htmlmessage .= $value->label . ": " . $obj->{$machine_name} . "<br />";
            }
        }

        // File validation and processing
        $allowedfileExtensions = array("image/png", "image/jpeg", "image/bmp", "application/pdf", "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.ms-powerpoint", 
            "application/vnd.openxmlformats-officedocument.presentationml.presentation", "text/csv", "application/zip");

        if ($value->type == "file") {
            if (isset($_POST["file"]) && count($_POST["file"]) > 0) {
                foreach ($_POST["file"] as $file) {
                    $image_parts = explode(";base64,", $file[$value->name]);
                    if (count($image_parts) > 1) {
                        $file_type = trim(explode(":", $image_parts[0])[1]);

                        if (in_array($file_type, $allowedfileExtensions)) {
                            $fileUpload[] = base64_decode(str_replace(" ", "+", substr($file[$value->name], strpos($file[$value->name], ","))));
                            $fileExtension[] = getFileExtension($file_type);
                        }
                    }
                }
            }
        }
    }

    $htmlmessage = urldecode($htmlmessage);
    $from = makeemailuser('email@domain.com', 'Moodle Admin', 2);
    $subject = "Email Notification";

    // Send email to recipients
    foreach ($emails as $recipientEmail) {
        if (checkemail($recipientEmail)) {
            $to = makeemailuser($recipientEmail, $recipientEmail);
            $mail = new CustomMailer();
            $status = $mail->email_to_user_custom(true, $to, $from, $subject, html_to_text($htmlmessage), $htmlmessage, $fileUpload, '', $fileExtension, true);
        }
    }

    // Send auto-reply emails
    foreach ($autoRepEmails as $autoEmail) {
        $to = makeemailuser($autoEmail);
        $replyMail = new CustomMailer();
        $replyMail->email_to_user_custom(true, $to, $from, $subject, html_to_text($autoRepData->message), $autoRepData->message, $fileUpload, '', $fileExtension, true);
    }

    if ($status) {
        echo json_encode(['status' => 'success', 'message' => $confirmMessage]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sorry! Your message could not be sent. Please contact the administrator!']);
    }
}

function processData($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function makeemailuser($email, $name = '', $id = -99) {
    $emailuser = new stdClass();
    $emailuser->email = $email;
    $emailuser->firstname = format_text($name, FORMAT_PLAIN);
    $emailuser->lastname = '';
    $emailuser->id = $id;
    return $emailuser;
}

function checkemail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function getFileExtension($file_type) {
    $extensions = [
        "image/png" => "png", "image/jpeg" => "jpg", "image/bmp" => "bmp", "application/pdf" => "pdf",
        "application/msword" => "doc", "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
        "application/vnd.ms-excel" => "xls", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",
        "application/vnd.ms-powerpoint" => "ppt", "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx",
        "text/csv" => "csv", "application/zip" => "zip"
    ];
    return $extensions[$file_type] ?? '';
}


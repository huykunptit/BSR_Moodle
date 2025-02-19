<?php

/**
 * Cocoon Form Builder integration for Moodle
 *
 * @package    cocoon_form_builder
 * @copyright  ©2021 Cocoon, XTRA Enterprises Ltd.
 * @author     Cocoon
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once('../../config.php');
global $USER, $DB, $CFG;
require_login();
$id = optional_param('id', 0, PARAM_INT); // Safely get the 'id' parameter from the URL

$PAGE->set_url('/local/cocoon_form_builder/getformdetail.php?id=' . $id);
$PAGE->set_context(context_system::instance());

if ($id > 0) {
    // Use parameterized query to avoid SQL injection
    $sql = "SELECT * FROM {cocoon_form_builder_forms} WHERE id = :id";
    $forms = $DB->get_records_sql($sql, ['id' => $id]);

    $data = array();

    foreach ($forms as $form) {
        $x = [
            "id" => $form->id,
            "title" => $form->title,
            "json" => $form->json,
            "reply" => $form->data,
            "url" => $form->url,
            "confirm_message" => $form->confirm_message,
            "recipients" => $form->recipients,
            "status" => $form->status,
            "ajax" => $form->ajax,
        ];

        $data[] = $x;
    }

    echo json_encode($data);
} else {
    // Handle case where no valid id is provided
    echo json_encode(['error' => 'Invalid form ID']);
}

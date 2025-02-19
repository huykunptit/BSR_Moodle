<?php
include_once 'Sample_Header.php';

// Template processor instance creation
echo date('H:i:s'), ' Creating new TemplateProcessor instance...', EOL;
$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('resources/BSR-HRM-PRO-002-F-004-2 Ket qua dao tao Rev 7.docx');


// Table with a spanned cell
$values = array(
    array(
        'manv'        => 'BS01971',
        'hoten' => 'James',
    ),
    array(
        'manv'        => 'BS01971',
        'hoten' => 'Robert',
    ),
    array(
        'manv'        => 'BS01971',
        'hoten' => 'Michael',
    ),
);

$templateProcessor->cloneRowAndSetValues('manv', $values);

//this is equivalent to cloning and settings values with cloneRowAndSetValues
// $templateProcessor->cloneRow('userId', 3);

// $templateProcessor->setValue('userId#1', '1');
// $templateProcessor->setValue('userFirstName#1', 'James');
// $templateProcessor->setValue('userName#1', 'Taylor');
// $templateProcessor->setValue('userPhone#1', '+1 428 889 773');

// $templateProcessor->setValue('userId#2', '2');
// $templateProcessor->setValue('userFirstName#2', 'Robert');
// $templateProcessor->setValue('userName#2', 'Bell');
// $templateProcessor->setValue('userPhone#2', '+1 428 889 774');

// $templateProcessor->setValue('userId#3', '3');
// $templateProcessor->setValue('userFirstName#3', 'Michael');
// $templateProcessor->setValue('userName#3', 'Ray');
// $templateProcessor->setValue('userPhone#3', '+1 428 889 775');

echo date('H:i:s'), ' Saving the result document...', EOL;
$templateProcessor->saveAs('results/BSR-HRM-PRO-002-F-004-2 Ket qua dao tao Rev 7.docx');

echo getEndingNotes(array('Word2007' => 'docx'), 'BSR-HRM-PRO-002-F-004-2 Ket qua dao tao Rev 7.docx');
if (!CLI) {
    include_once 'Sample_Footer.php';
}

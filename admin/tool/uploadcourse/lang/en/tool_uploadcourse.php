<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'tool_uploadcourse'.
 *
 * @package    tool_uploadcourse
 * @copyright  2011 Piers Harding
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowdeletes'] = 'Allow deletes';
$string['allowdeletes_help'] = 'Whether the delete field is accepted or not.';
$string['allowrenames'] = 'Allow renames';
$string['allowrenames_help'] = 'Whether the rename field is accepted or not.';
$string['allowresets'] = 'Allow resets';
$string['allowresets_help'] = 'Whether the reset field is accepted or not.';
$string['cachedef_helper'] = 'Helper caching';
$string['cannotdeletecoursenotexist'] = 'Cannot delete a course that does not exist';
$string['cannotforcelang'] = 'No permission to force language for this course';
$string['cannotgenerateshortnameupdatemode'] = 'Cannot generate a shortname when updates are allowed';
$string['cannotreadbackupfile'] = 'Cannot read the backup file';
$string['cannotrenamecoursenotexist'] = 'Cannot rename a course that does not exist';
$string['cannotrenameidnumberconflict'] = 'Cannot rename the course, the ID number conflicts with an existing course';
$string['cannotrenameshortnamealreadyinuse'] = 'Cannot rename the course, the shortname is already used';
$string['cannotupdatefrontpage'] = 'You are not allowed to change the site home.';
$string['canonlyrenameinupdatemode'] = 'Can only rename a course when update is allowed';
$string['canonlyresetcourseinupdatemode'] = 'Can only reset a course in update mode';
$string['couldnotresolvecatgorybyid'] = 'Could not resolve category by ID';
$string['couldnotresolvecatgorybyidnumber'] = 'Could not resolve category by ID number';
$string['couldnotresolvecatgorybypath'] = 'Could not resolve category by path';
$string['coursecreated'] = 'Quiz created';
$string['coursedeleted'] = 'Quiz deleted';
$string['coursedeletionnotallowed'] = 'Quiz deletion is not allowed';
$string['coursedoesnotexistandcreatenotallowed'] = 'The course does not exist and creating course is not allowed';
$string['courseexistsanduploadnotallowed'] = 'The course exists and update is not allowed';
$string['coursefile'] = 'File';
$string['coursefile_help'] = 'This file must be a CSV file.';
$string['courseidnumberincremented'] = 'Quiz ID number incremented {$a->from} -> {$a->to}';
$string['courseprocess'] = 'Quiz process';
$string['courserenamed'] = 'Quiz renamed';
$string['courserenamingnotallowed'] = 'Quiz renaming is not allowed';
$string['coursereset'] = 'Quiz reset';
$string['courseresetnotallowed'] = 'Quiz reset now allowed';
$string['courserestored'] = 'Quiz restored';
$string['coursestotal'] = 'Quiz total: {$a}';
$string['coursescreated'] = 'Quiz created: {$a}';
$string['coursesupdated'] = 'Quiz updated: {$a}';
$string['coursesdeleted'] = 'Quiz deleted: {$a}';
$string['courseserrors'] = 'Quiz errors: {$a}';
$string['courseshortnameincremented'] = 'Quiz shortname incremented {$a->from} -> {$a->to}';
$string['courseshortnamegenerated'] = 'Quiz shortname generated: {$a}';
$string['coursetemplatename'] = 'Restore from this course after upload';
$string['coursetemplatename_help'] = 'Enter an existing course shortname to use as a template for the creation of all courses.';
$string['coursetorestorefromdoesnotexist'] = 'The course to restore from does not exist';
$string['courseupdated'] = 'Quiz updated';
$string['createall'] = 'Create all, increment shortname if needed';
$string['createnew'] = 'Create new courses only, skip existing ones';
$string['createorupdate'] = 'Create new courses, or update existing ones';
$string['csvdelimiter'] = 'CSV separator';
$string['csvdelimiter_help'] = 'The character separating the series of data in each record.';
$string['csvfileerror'] = 'There is something wrong with the format of the CSV file. Please check the number of headings and columns match, and that the separator and file encoding are correct. {$a}';
$string['csvline'] = 'Line';
$string['defaultvalues'] = 'Default course values';
$string['defaultvaluescustomfieldcategory'] = 'Default values for \'{$a}\'';
$string['downloadcontentnotallowed'] = 'Configuring download of course content not allowed';
$string['encoding'] = 'Encoding';
$string['encoding_help'] = 'Encoding of the CSV file.';
$string['errorcannotcreateorupdateenrolment'] = 'Cannot create or update enrolment method \'{$a}\'';
$string['errorcannotdeleteenrolment'] = 'Cannot delete enrolment method \'{$a}\'';
$string['errorcannotdisableenrolment'] = 'Cannot disable enrolment method \'{$a}\'';
$string['errorwhilerestoringcourse'] = 'Error while restoring the course';
$string['errorwhiledeletingcourse'] = 'Error while deleting the course';
$string['generatedshortnameinvalid'] = 'The generated shortname is invalid';
$string['generatedshortnamealreadyinuse'] = 'The generated shortname is already in use';
$string['id'] = 'ID';
$string['importoptions'] = 'Import options';
$string['idnumberalreadyinuse'] = 'ID number already used by a course';
$string['invalidbackupfile'] = 'Invalid backup file';
$string['invalidcourseformat'] = 'Invalid course format';
$string['invalidcsvfile'] = 'Invalid input CSV file';
$string['invaliddownloadcontent'] = 'Invalid download of course content value';
$string['invalidencoding'] = 'Invalid encoding';
$string['invalidmode'] = 'Invalid mode selected';
$string['invalideupdatemode'] = 'Invalid update mode selected';
$string['invalidvisibilitymode'] = 'Invalid visible mode';
$string['invalidroles'] = 'Invalid role names: {$a}';
$string['invalidshortname'] = 'Invalid shortname';
$string['invalidfullnametoolong'] = 'The fullname field is limited to {$a} characters';
$string['invalidshortnametoolong'] = 'The shortname field is limited to {$a} characters';
$string['missingmandatoryfields'] = 'Missing value for mandatory fields: {$a}';
$string['missingshortnamenotemplate'] = 'Missing shortname and shortname template not set';
$string['mode'] = 'Upload mode';
$string['mode_help'] = 'This allows you to specify if courses can be created and/or updated.';
$string['nochanges'] = 'No changes';
$string['pluginname'] = 'Quiz upload';
$string['preview'] = 'Preview';
$string['customfieldinvalid'] = 'Custom field \'{$a}\' is empty or contains invalid data';
$string['reset'] = 'Reset course after upload';
$string['reset_help'] = 'Whether to reset the course after creating/updating it.';
$string['result'] = 'Result';
$string['restoreafterimport'] = 'Restore after import';
$string['rowpreviewnum'] = 'Preview rows';
$string['rowpreviewnum_help'] = 'Number of rows from the CSV file that will be previewed on the following page. This option is for limiting the size of the following page.';
$string['shortnametemplate'] = 'Template to generate a shortname';
$string['shortnametemplate_help'] = 'The short name of the course is displayed in the navigation. You may use template syntax here (%f = fullname, %i = idnumber), or enter an initial value that is incremented.';
$string['templatefile'] = 'Restore from this file after upload';
$string['templatefile_help'] = 'Select a file to use as a template for the creation of all courses.';
$string['unknownimportmode'] = 'Unknown import mode';
$string['updatemissing'] = 'Fill in missing items from CSV data and defaults';
$string['updatemode'] = 'Update mode';
$string['updatemode_help'] = 'If you allow courses to be updated, you also have to tell the tool what to update the courses with.';
$string['updatemodedoessettonothing'] = 'Update mode does not allow anything to be updated';
$string['updateonly'] = 'Only update existing courses';
$string['updatewithdataordefaults'] = 'Update with CSV data and defaults';
$string['updatewithdataonly'] = 'Update with CSV data only';
$string['uploadcourses'] = 'Upload courses';
$string['uploadcourses_help'] = 'Quiz may be uploaded via text file. The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by the selected separator
* The first record contains a list of fieldnames defining the format of the rest of the file
* Required fieldnames are shortname, fullname, and category';
$string['uploadcoursespreview'] = 'Upload courses preview';
$string['uploadcoursesresult'] = 'Upload courses results';
$string['privacy:metadata'] = 'The Course upload plugin does not store any personal data.';

<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'sqlsrv';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '127.0.0.1';
$CFG->dbname    = 'bsr_4.2';
$CFG->dbuser    = 'sa';
$CFG->dbpass    = 'Demo@1234';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://localhost';
$CFG->dataroot  = 'C:\\inetpub\\data_bsr_4.2';
$CFG->admin     = 'admin';
$CFG->cachedir     = 'C:\\inetpub\\customdata\\cache';
$CFG->localcachedir     = 'C:\inetpub\customdata\localcache';
$CFG->tempdir     = 'C:\inetpub\customdata\temp';
$CFG->theme = 'edumy';
$CFG->directorypermissions = 0777;
$CFG->maxusersperpage = 1000;
$CFG->mathjaxpath = 'http://localhost/lib/mathjax/MathJax.js';

$CFG->iomad_allow_username = true;
define("MAX_USERS_PER_PAGE", 5000);
$CFG->maxusersperpage = 1000;
$CFG->enroladminnewcourse = false;
$CFG->iomad_allow_username = true;
$CFG->iomad_max_select_users = 5000;
$CFG->mathjaxpath = 'http://localhost/lib/mathjax/MathJax.js';

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
//
// @error_reporting(E_ALL | E_STRICT);   // NOT FOR PRODUCTION SERVERS!
// @ini_set('display_errors', '1');         // NOT FOR PRODUCTION SERVERS!
// $CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
// $CFG->debugdisplay = 1;              // NOT FOR PRODUCTION SERVERS!
if (isset($_GET['bui_editid']) && isset($_GET['cocoon_live_customizer'])) {
  echo '<!DOCTYPE html>';
}

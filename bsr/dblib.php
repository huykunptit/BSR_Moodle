<?php
require __DIR__.'/vendor/autoload.php'; 

defined('MOODLE_INTERNAL') || die();

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

function thongtincbcnv() {
	global $DB;
	$data = array();
	// 
	$sql = "SELECT u.id,
			       u.id AS userid,
			       u.idnumber,
			       u.email,
			       CAST(u.firstname AS NVARCHAR(255)) + CAST(' ' AS NVARCHAR(255)) + CAST(u.lastname AS NVARCHAR(255)) AS fullname,
			       pa.organisationid ,
			       pa.positionid ,
			       pos.fullname AS posname ,
			       CASE
			           WHEN org.id IS NULL THEN NULL
			           WHEN parentorg.id IS NULL THEN org.fullname
			           WHEN grandorg.id IS NULL THEN parentorg.fullname
			           ELSE grandorg.fullname
			       END AS deptname ,
			       CASE
			           WHEN parentorg.id IS NULL THEN NULL
			           WHEN grandorg.id IS NULL THEN org.fullname
			           ELSE parentorg.fullname
			       END AS teamname ,
			       CASE
			           WHEN grandorg.id IS NULL THEN NULL
			           ELSE org.fullname
			       END AS groupname
			FROM {user} u
			JOIN {pos_assignment} pa ON u.id= pa.userid
			JOIN {pos} pos ON pos.id= pa.positionid
			JOIN {org} org ON org.id = pa.organisationid
			LEFT JOIN {org} parentorg ON parentorg.id = org.parentid
			LEFT JOIN {org} grandorg ON grandorg.id = parentorg.parentid
			where u.deleted = 0";

	$output = $DB->get_records_sql($sql);

	// $capsule = new Capsule;
	// $capsule->addConnection([
	//     'driver'    => 'sqlsrv',
	//     'host'      => 'localhost',
	//     'database'  => 'CoreUis',
	//     'username'  => 'sa',
	//     'password'  => 'uisteam@psc.com',
	//     'charset'   => 'utf8',
	//     'collation' => 'utf8_general_ci',
	//     'prefix'    => '',
	// ]);

	// /* 1: Try to connect Server Chính quy (DB=CoreUis, table=psc_StudentInfo)*/
	// $studentCq = Capsule::table('psc_StudentInfo')->where('StudentID', $usr)->where('PW', $pwd)->first();
	// if (!is_null($studentCq)) {
	// 	return true;
	// }

	return $output;
}

?>
<?php
/**
  * get-attendance-ical.php
  *
  * An example script showing the use of the class.studentsachieve.php
  * class for interacting with the StudentsAchieve system.
  *
  * Logs in to StudentsAchieve, retrieves attendance data for a student
  * and outputs an iCal file.
  *
  * =~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=
  * IMPORTANT NOTE - IMPORTANT NOTE - IMPORTANT NOTE - IMPORTANT NOTE
  * =~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=
  * This isn't a "crack" of StudentsAchieve. You need a valid parent
  * username and password. All that's happening here is that the
  * script is automating the EXACT SAME PROCESS that you use when you
  * login to StudentsAchieve using a web browser.
  * =~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=
  *
  * Requirements: 
  *	 - class.studentsachieve.php
  *  - PHP (http://www.php.net) with cURL support
  * 
  * @version 0.1, 20 September 2013
  * @author Peter Rukavina <peter@rukavina.net> 
  * @copyright Reinvented Inc., 2013
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */
  
$username = $argv[1];
$password = $argv[2];

if (!$username or !$password) {
	die("Usage: get-attendance-ical.php username password\n");
}
  
require_once("class.studentsachieve.php");

print "Logging in to StudentsAchieve...\n";

$s = new StudentsAchieve($username,$password);
$s->login();

print "Getting attendance data...\n";

$s->getMultiAttendance("2013-09-06","2013-09-19");

print "Making an iCal file...\n";

$ical = "";
$ical .= "BEGIN:VCALENDAR\n";
$ical .= "CALSCALE:GREGORIAN\n";
$ical .= "PRODID:-//Students Achieve Processor //StudentsAchieve 1.1//EN\n";
$ical .= "VERSION:2.0\n";
$ical .= "METHOD:PUBLISH\n";

foreach($s->attendance as $date => $item) {
	foreach($item->times as $key => $time) {
		$ical .= "BEGIN:VEVENT\n";
		$ical .= "TRANSP:TRANSPARENT\n";
		$ical .= "STATUS:CONFIRMED\n";
		$ical .= "SUMMARY:" . $s->attendance[$date]->status[$key]  . "\n";
		$ical .= "LOCATION:" . $s->attendance[$date]->classes[$key] . " at " . $time . "\n";
		$ical .= "DTSTART;VALUE=DATE:" . strftime("%Y%m%d", strtotime($date)) . "\n";
		$ical .= "DTEND;VALUE=DATE:" . strftime("%Y%m%d", strtotime($date)) . "\n";
		$ical .= "END:VEVENT\n";
	}
}

$ical .= "END:VCALENDAR\n";

$filename = str_replace(" ","",$s->school) . ".ics";
$fp = fopen($filename,"w");
fwrite($fp,$ical);
fclose($fp);

print "Saved an iCal file called '$filename'.\nLoad this into your calendar.\n";
print "Done";

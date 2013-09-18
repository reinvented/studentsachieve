<?php
/**
  * get-teacher-vcard.php
  *
  * An example script showing the use of the class.studentsachieve.php
  * class for interacting with the StudentsAchieve system.
  *
  * Logs in to StudentsAchieve, retrieves a list of classes and
  * teachers and their email addresses, and prepares a vCard file 
  * suitable for import into an address book application.
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
  * @version 0.1, 18 September 2013
  * @author Peter Rukavina <peter@rukavina.net> 
  * @copyright Reinvented Inc., 2013
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */
  
$username = $argv[1];
$password = $argv[2];

if (!$username or !$password) {
	die("Usage: get-teacher-vcard.php username password\n");
}
  
require_once("class.studentsachieve.php");

print "Logging in to StudentsAchieve...\n";

$s = new StudentsAchieve($username,$password);
$s->login();

print "Making a vCard file...\n";

$vcard = "";
foreach($s->teachers->names as $key => $t) {
	$vcard .= "BEGIN:VCARD\n";
	$vcard .= "VERSION:3.0\n";
	$vcard .= "PRODID:-//Apple Inc.//Mac OS X 10.8.4//EN\n";
	list($fname, $lname) = split(' ', $t,2);
	$vcard .= "N:$lname;$fname;;;\n";
	$vcard .= "FN:" . $t . "\n";
	$vcard .= "ORG:" . $s->school . "\n";
	$vcard .= "TITLE:Teacher, " . $s->teachers->classes[$key] . "\n";
	$vcard .= "EMAIL;type=INTERNET;type=WORK;type=pref:" . $s->teachers->emails[$key] . "\n";
	$vcard .= "END:VCARD\n";
}

$filename = str_replace(" ","",$s->school) . ".vcf";
$fp = fopen($filename,"w");
fwrite($fp,$vcard);
fclose($fp);

print "Saved a vCard file called '$filename'.\nLoad this into your address book.\n";
print "Done";

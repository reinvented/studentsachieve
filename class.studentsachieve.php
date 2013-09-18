<?php
/**
  * class.studentsachieve.php
  *
  * A PHP script to login to a StudentsAchieve online student records system
  * and retrieve information about a student's classes, attendance, and marks.
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
  *  - PHP (http://www.php.net) with cURL support
  * 
  * @version 0.1, 18 September 2013
  * @author Peter Rukavina <peter@rukavina.net> 
  * @copyright Reinvented Inc., 2013
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

class StudentsAchieve {

    /**
      * Construct a new StudentsAchieve object.
      * @param string $username your StudentsAchieve parent username
      * @param string $password your StudentsAchieve parent password
      */
    function __construct($username,$password) {
        $this->username     = $username;
        $this->password     = $password;
        $this->homepage		= "https://sas.edu.pe.ca/SASPublicWeb/";
        $this->loginform	= "https://sas.edu.pe.ca/SASPublicWeb/Forms/Login/Login.aspx?Logout=true";
        $this->summarypage	= "https://sas.edu.pe.ca/SASPublicWeb/Pages/Entities/Summary/ViewStudentSummaryData.aspx";
	}

    /**
      * Login to StudentsAchieve. Retrieve class information.
      */
	function login() {

		// Remove temporary file used to store cookies.
		unlink("/tmp/studentsachieve-cookies.txt");

		// Visit the "home page" of Students Achieve, which serves simply to get some initial cookies and
		// the values for VIEWSTATE and EVENTVALIDATION. 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $this->homepage); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_COOKIESESSION, true); 
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/studentsachieve-cookies.txt"); 
		curl_setopt($ch, CURLOPT_COOKIEJAR,  "/tmp/studentsachieve-cookies.txt"); 
		$result = curl_exec($ch); 

		// Need to extract two hidden variables and POST these with the username and password.
		// <input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="/wEPDwUKMTEwNjQ4NzMxNw9kFgICAQ9kFgICAw9kFgQCAg8QZGQWAGQCBQ8PFgQeBFRleHQFBUxvZ2luHgdUb29sVGlwBQ5DbGljayB0byBMb2dpbmRkZH1P3ePF9xhDOyhNx5DWNzMYtanI" />
		// <input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="/wEWBgK/pKPGBgKRmJP/BALlrpX5DQLr5L72DgLkmuGZDQKwvcDtDSyO2JntdDFm0MYIl3eKDLx/oWOp" /> 

		$regex = '/id="__VIEWSTATE"\\ value="(.{0,})"\\ \/\\>/';
		preg_match($regex, $result, $matches);
		$VIEWSTATE = ($matches[1]);

		$regex = '/id="__EVENTVALIDATION"\\ value="(.{0,})"\\ \/\\>\\ /';
		preg_match($regex, $result, $matches);
		$EVENTVALIDATION = ($matches[1]);

		// These are the fields we'll HTTP POST to the login form.
		// The VIEWSTATE and EVENTVALIDATION are required and must be sent as received in the last step.
		$postfields = array(
			'modLogin$txUserLogin' => $this->username,
			'modLogin$txUserPassword' => $this->password,
			"__VIEWSTATE" => $VIEWSTATE,
			"__EVENTVALIDATION" => $EVENTVALIDATION,
			'modLogin$hidScreenWidth' => 2560,
			'modLogin$hidScreenHeight' => 1440,
			'modLogin$btLogin' => "Login");
		
		$postfields = http_build_query($postfields);

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $this->loginform); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); 
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/studentsachieve-cookies.txt"); 
		curl_setopt($ch, CURLOPT_COOKIEJAR,  "/tmp/studentsachieve-cookies.txt"); 
		$result = curl_exec($ch); 

		$regex = '/(?:(?U)\\<font\\ face=\'Arial\'\\ Size=\'5\'\\ color=\'Maroon\'\\>(.{0,})\\<\/font\\>)/';
		preg_match($regex, $result, $matches);
		$this->school = ($matches[1]);

		// Using a regular expression we can parse out the names of the student's classes.
		$regex = '/ctl00\',\'\'\\)"\\>(.{0,})\\<\/a\\>/';
		preg_match_all($regex, $result, $matches);
		$this->teachers->classes = $matches[1];
		
		// Using a regular expression we can parse out the URLs that lead to more information about each teacher.
		$regex = '/popupWindowCenter\\(\'(.{0,})\',\'ViewTeacherDetails\',\'ViewTeacherDetails\'/';
		preg_match_all($regex, $result, $matches);
		$this->teachers->urls = $matches[1];
		
		// We then cycle through each teacher and pull out their name and email address.
		foreach($this->teachers->urls as $key => $teacherurl) {
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, $teacherurl); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/studentsachieve-cookies.txt"); 
			curl_setopt($ch, CURLOPT_COOKIEJAR,  "/tmp/studentsachieve-cookies.txt"); 
			$result = curl_exec($ch); 		

			$regex = '/(?:(?Us)TASK_NAME"\\ class="TaskForm"\\ MaxLength="100"\\ style="display:inline\\-block;"\\>(.{0,})\\<\/span\\>.{0,}EMAIL"\\ class="TaskForm"\\ TextMode="MultiLine"\\ rows="6"\\ style="display:inline\\-block;"\\>(.{0,})\\<\/span\\>)/';
			preg_match_all($regex, $result, $matches);
			$this->teachers->names[$key] = $matches[1][0];
			$this->teachers->emails[$key] = $matches[2][0];
		}
	}
} 

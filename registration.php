<?php
require_once("appconnect.php");
############################################

class validators extends Db
{
    var $errval = "";
//////////////////////////////////////////
   function textvalid ($uvalu, $errmsg) {
	   
    // Validate Text
        $uvalu = stripslashes(strip_tags($uvalu));
        if (strlen($uvalu) < 3 || !eregi('([a-zA-Z0-9])', $uvalu))
        {
          //$this->errval = $errmsg;
          //echo $this->errval;
          return false;
        }
        else
		
        {
          //$this->errval ='';
          //echo $this->errval;
          return true;
        }
    // Validate Text
    }

//////////////////////////////////////////
    function emailvalid($emvalu)
    // Validate Email
    {
		  if (!ereg('([a-z0-9.])'.'@'.'([a-z0-9.])'.'.'.'([a-z])', $emvalu) || !ereg('([a-z0-9.])'.'@'.'([a-z0-9.])'.'.'.'([a-z])'.'.'.'([a-z])', $emvalu))
		  {
			return false;
		  }
		  else
		  {
			//$this->errval = "OK";
			//echo $this->errval;
			return true;
		  }
    // Validate Email
    }
//////////////////////////////////////////	
	function chmailava ($usrmail)
	{ 
		// Validate Email by Availability
		$conn = $this->dbconect();
		$sqlq = "SELECT `email` FROM `members` WHERE `email` = '$usrmail'";
		$rq = mysqli_query($conn, $sqlq);
		if (mysqli_num_rows($rq) > 0)
		{ mysqli_close($conn); return false; }
		else {  mysqli_close($conn); return true; }
	}
//////////////////////////////////////////

//////////////////////////////////////////	
	function checkphoneduplicate($phone, $col)
	{ 
		// Validate Email by Availability
		$conn = $this->dbconect();
		$sqlq = "SELECT * FROM `members` WHERE `". $col ."` = '$phone'";
		$rq = mysqli_query($conn, $sqlq);
		if (mysqli_num_rows($rq) > 0)
		{ mysqli_close($conn); return false; }
		else { mysqli_close($conn); return true; }
	}
//////////////////////////////////////////	
	function phonevalid ($phvalu)
    // Validate Phone
    {
      if (strlen($phvalu) < 11 || strlen($phvalu) > 11 || ereg("[a-zA-Z<>.!@#$%^&*()_'=+|?;:~` ]", $phvalu)) {
			return false;
	  }
      else  {
         	return true;
      }
    // Validate Phone
    }
//////////////////////////////////////////	
	function numbervalid ($numeric)
    // Validate Number
    {
      if (ereg("[a-zA-Z<>.!@#$%^&*()_'=+|?;:~` ]", $numeric)) {
			return false;
	  }
      else  {
         	return true;
      }
    // Validate Number
    }
//////////////////////////////////////////	
    function pwdvalid($pvalu)
    // Validates Password
    {
        $pvalu = strip_tags(stripslashes($pvalu));
        if (strlen($pvalu) < 6 || !ereg('([a-zA-Z0-9!@#$%^&*?])', $pvalu)) {
          //$this->errval = 'Password must be minimum of 6 characters';
          //echo $this->errval;
          return false;
        }
        else {
          //$this->errval ='';
          //echo $this->errval;
          return true;
        }
    // Validate Password
    }

//////////////////////////////////////////
	function repwdf ($uinput, $repwd)
	{
		// Retype Password
		if ($uinput != $repwd)
		{
			echo  'Passwords do not match';
			return false;
		}
		elseif (empty($repwd))
		{
			echo  'No data entered';
			return false;
		}
		else
		{
			echo '';
			return true;
		}
	}
//////////////////////////////////////////
    function formatval($urvalu)
    {
        $revalu = strip_tags(stripslashes(trim($urvalu)));
        return $revalu;
    }
//////////////////////////////////////
	function checkagree ($chvalu)
	{
		if ( $chvalu != 'yes' )
		{
			$this->errval = 'You must agree with our TOS';
			echo $this->errval;
			return false;
		}
		else
		{
			$this->errval = '';
			echo $this->errval;
			return true;
		}
	}	

// End of Validators Class
###########################################
}

###########################################

class memreg extends validators {
/* Initialization OF a CLASS */
// Registration and Validation
/////////////////////////////////////////
public function register($fname, $lname, $sex, $email, $phone, $address, $password)
{

	// Register User
	$response = ""; // Return Variable for Everything in this method
	$fname = $this->formatval($fname);
	$lname = $this->formatval($lname);
	$sex = $this->formatval($sex);
	$email = $this->formatval($email);
	$phone = $phone;
	$password = $password;
	
	// Validations
	if(empty($fname))
	{
		$response = "Firstname is blank";
		return $response;
	}
	
	if(empty($lname))
	{
		$response = "Lastname is blank";
		return $response;
	}
	
	if(empty($sex))
	{
		$response = "Sex is blank";
		return $response;
	}
	
	if(empty($email))
	{
		$response = "Email is blank";
		return $response;
	}
	
	if(!$this->emailvalid($email))
	{
		$response = "Invalid Email Format";
		return $response;
	}
	
	if(!$this->chmailava($email))
	{
		$response = "Email has been used";
		return $response;
	}
	
	if(empty($phone))
	{
		$response = "Phone Number is blank";
		return $response;
	}
	
	if(!$this->phonevalid($phone))
	{
		$response = "Invalid Phone Number Format";
		return $response;
	}
	
	if(!$this->checkphoneduplicate($phone, "phone"))
	{
		$response = "Phone Number has been used";
		return $response;
	}
	
	if(empty($address))
	{
		$response = "Address is blank";
		return $response;
	}
	
	// Validations
	
	// Password Salting
	$pwdsalt = time().mt_rand();
	$saltedpword = $password . $pwdsalt;
	$authcode = mt_rand(1000,9999999);
	$insquery = sprintf("INSERT INTO `members` (`firstname`, `lastname`, `sex`, `email`, `phone`, `address`, `password`, `salt`) VALUES ('%s', '%s', '%s','%s','%s','%s',SHA1('%s'),'%s')", $fname, $lname, $sex, $email, $phone, $address, $saltedpword, $pwdsalt);
	$res = $this->dbconect();
	$runq = mysqli_query($res, $insquery) or $this->ErrorLog("cmd=reg->".mysqli_error($res));
	
	// Audit Trail
	$this->audittrail($phone, "reg", "success", "web", "");
	
	// Auth Email
	//$this->regauthMail("abc", $email);
	
	if($runq) {	
		$response = "OK";
	}
	else {
		mysqli_close($res);
		$response = "System Error";
	}
	return $response;
}
/////////////////////////////////////////
function regauthMail($vericode, $email)
{
	$enc = base64_encode($email);
	$message = "<h1>".$this->projectname."</h1>";
	$message .= "Thank you for registering on ".$this->projectname." To complete your register please click on <a href='http://dunniessentials.com/valid.php?e=$enc'>this link</a> <br />";
	$message .= "<p></p>";
	$message .= "<p></p>";
	$message .= "<p></p>";
	$message .= "<p></p>";
	$message .= "<p>For questions and inquiries contact: dunniessentials.com</p>";
	$message .= "<p>(c)".date('Y'). $this->projectname."</p>";
	$this->mailnotifier($email,"Email Verification", $message);
}
/////////////////////////////////////////

function regauthSMS($vericode, $to)
{
	$from = $this->projectname;
	$message = "Your authentication code is: ". $vericode;
$out = file("http://smsc.xwireless.net/API/WebSMS/Http/v3.1/index.php?username=wicee&password=tarsus01&sender=".$this->projectname."&to=". $to ."&message=". urlencode($message) ."&reqid=1&format=text");
}

/////////////////////////////////////////
function validateRegistration($vericode, $data, $agent)
	// Verify Phone Number
{
	$res = $this->dbconect();
	$validq = "SELECT * FROM `members` WHERE (`phone` = '$data' OR `email` = '$data') AND authcode = '$vericode'";
	$rq = mysqli_query($res, $validq) or ("cmd=validreg->".mysqli_error($res));
	if (mysqli_num_rows($rq) > 0){ 
		//mysqli_close($conn); 
		if ($agent == "Web") {
			$update = mysqli_query($res, "UPDATE `members` SET `validmail` = 1 WHERE (`phone` = '$data' OR `email` = '$data')");
		}
		else {
			$update = mysqli_query($res, "UPDATE `members` SET `validphone` = 1 WHERE (`phone` = '$data' OR `email` = '$data')");
		}
		return "OK"; 
	}
	else { mysqli_close($conn); return "Validation Error"; }
}
/////////////////////////////////////////
function mailnotifier($tomem, $subj, $msg)
{
 /// Email Notifier ///         
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0'."\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: Dunni Essential <no-reply@dunniessentials.com>' . "\r\n";
  	//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
	//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
	//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";
	mail($tomem, $subj, $msg, $headers);
}
/////////////////////////////////////////
function smsnotifier($to, $msg)
{
 /// SMS Notifier ///         
	$from = "Dunni Essentials";
	// Send SMS
	
	// Send SMS
}

/////////////////////////////////////////
public function forgot($username) 
// Check for Forgotten Password
{
	$res = $this->dbconect();
	// Check if the user Exists
	$qryemail = "SELECT email FROM `members` WHERE (email ='$username' OR phone='$username')";
	$runqryemail = mysqli_query($res, $qryemail) or ("cmd=forgotpwd->".mysqli_error($res));
	$userexists = mysqli_num_rows($runqryemail);
	if($userexists > 0)  {
		$mememail = mysqli_fetch_array($runqryemail);
		$uemail = $mememail['email']; // Email
		$pwdsalt = $mememail['salt']; // Password Salt
	} else {
		return "Not a registered Member";
	}
	// Generate New Password
	$genpswd = $this->generateGuid(false);
	$newpswd = substr($genpswd, 0 , 7);
	$saltedpwd = $newpswd.$pwdsalt;
	
	// Change to New Password
	$chngpaswdqry = sprintf("UPDATE `members` SET password = SHA1('%s')  WHERE `email`='$uemail'", $saltedpwd);
	$runpwdchange = mysqli_query($res, $chngpaswdqry) or ("cmd=chngpwd->".mysqli_error($res));
	
	// Email the New Password
	$msg = "Your new ". $this->projectname . " password is: ".$newpswd;
	$this->mailnotifier($uemail, "Request for New Password", $msg);
	return "OK";
}
// Check for Forgotten Password
//////////////////////////////////////////

}
// Registration and Validation methods
############################################
?>

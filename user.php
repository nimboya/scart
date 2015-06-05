<?php
/* Import Classes */
include_once("registration.php");
/* myisusu project
Author: Ewere Diagboya
Company: Wicee Solutions
Date/Time: 2014-08-09 9:52PM
Location: My Father's Palor

Description: Profiling Functionalities
*/
#########################################
class usersession extends memreg {
/* INSTANCE OF CLASS */
// Login, Logout, Guest methods
////////////////////////////////////////////
function SessionAuth($username, $logintok)
{
	// Connect to DB
	$res = $this->dbconect();
	// Check if User is Logged In and Using the Right Token
	$runtokqry = mysqli_query($res,"SELECT id FROM `members` WHERE (`email` = '$username' OR `phone` = '$username') AND logintok = '$logintok'") or $this->ErrorLog("cmd=sessionauth->".mysqli_error($res));
	if(!$runtokqry)
	{
		mysqli_close($res);
		$response = "System Error";
		return $response;	
	}
	
	$sessionactive = mysqli_num_rows($runtokqry);
	if($sessionactive == 1) {
		mysqli_close($res);
		$response = true;
		return $response;	
	}
	else {
		mysqli_close($res);
		$response = false;
		return $response;	
	}
	
}
////////////////////////////////////////////
function memlogin($username, $upwd)
# LogIn User
{
	$response = "";
	$res = $this->dbconect();
	#Email
	$usrname = strip_tags($username);	
	#Password
	$usrpwd = strip_tags($upwd);
	
	// Get Salt
	$saltqry = mysqli_query($res, sprintf("SELECT salt FROM `members` WHERE `email` = '%s' OR `phone` = '%s'", $usrname, $usrname));
	$saltarray = mysqli_fetch_array($saltqry);
	$pwdsalt = $saltarray['salt'];
	
	// Concatenate Salt With Password
	$orpwd = $usrpwd.$pwdsalt;
	
	// Real Authentication
	$loginqry = sprintf("SELECT * FROM `members` WHERE (`email` = '%s' OR `phone` = '%s') AND `password` = SHA1('%s')", $usrname, $usrname,$orpwd);
	$runlogin = mysqli_query($res, $loginqry) or $this->ErrorLog("cmd=loginauth->".mysqli_error($res));
	$accessauth = mysqli_num_rows($runlogin);
	if($accessauth > 0)
	{
		// Generate LoginToken
		$logintok = md5(time().mt_rand());
		$updatelogintok = mysqli_query($res, "UPDATE `members` SET logintok = '$logintok' WHERE (`email` = '$usrname' OR `phone` = '$usrname')") or $this->ErrorLog("cmd=memlogin->".mysqli_error($res));
		if(!$updatelogintok)
		{
			$response = "System Error";
			return $response;
		}
		
		$this->audittrail($usrname,"login","success","web",$logintok);
		$response = "OK:".$logintok;
		return $response;
	}
	else {
		$response = "Wrong Login Details";
		$this->audittrail($usrname,"login","failed:".$response,"web","");
		return $response;		
	}
	
}

function memlogout($username, $logintok, $agent)
# Log Out User
{
	// Session Authenticator
	$authresp = $this->SessionAuth($username, $logintok);
	if(!$authresp)
	{
		$response = "Invalid, Not Logged In";
		return $response;	
	}
	
	$res = $this->dbconect();
	$logoutqry = mysqli_query($res, "UPDATE `members` SET `logintok` = '' WHERE `logintok` = '$logintok'") or $this->ErrorLog("cmd=memlogout->".mysqli_error($res));
        $_SESSION['curuser'] = NULL;
        $_SESSION['logintok'] = NULL;
	
	if($logoutqry)
	{
		$this->audittrail($username,"logout","success","web",$logintok);
		mysqli_close($res);
		return "OK";
	}
	else {
		$this->audittrail($username,"logout","failed:".$response,"web",$logintok);
		mysqli_close($res);
		return "System Error";
	}
}

}


// Login, Logout, Guest procedures
############################################

############################################
class useroperations extends usersession
{
/* ABSTRACT OF CLASS */
// transactions, mail admin

////////////////////////////////////////////
function mnotifier($to, $subj, $msg)
# Email Notifier
{
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: talkdeygo <info@talkdeygo.com>' . "\r\n";
	$this->dbconect();
	$q = mysql_query("select * from member where tadeyid='$to'");
	$u = mysql_fetch_array($q); 
	mail($u[email], $subj, $msg, $headers);
}

////////////////////////////////////////////
// Photo Upload
function photo_upload($cur_user) {
# Perform Upload	
	$filetype = $_FILES[userfile][type];
	$file_size = $_FILES[userfile][size] / 1024;
	$maxsize = "3000000";
	#echo $file_size . "KB";
	
	# Random Number
	$rnd = uniqid(rand(),true);
	
	#Check file size and File Type before upload
	if($filetype != "image/png" && $filetype != "image/jpeg" && $filetype != "image/gif" && $filetype != "image/pjpeg")
	{
		echo "<b><font color='red'>File selected is not a an Image/JPEG File</b>"; 
	}
	elseif ($file_size > $maxsize)
	{
		echo "File size larger than 3MB";	
	}
	else {
		$folda = "memphotos/";
		if(!file_exists($folda)) mkdir($folda);
		$newname = $folda . $rnd . $cur_user . ".jpg";
		move_uploaded_file($_FILES[userfile][tmp_name], $newname);
		if(file_exists($_POST[prevphoto]) && $_POST[prevphoto] != "memphotos/user.png") unlink($_POST[prevphoto]);
		#echo $_POST[prevphoto];
		$sqlup = "UPDATE member SET myphoto='$newname' WHERE tadeyid='$cur_user'";
		mysql_query($sqlup) or die("Contact Administrator for error <a href='www.talkdeygo.com'>Back to Home</a>: ". mysql_error());
		echo "<font color='green'>Upload successful</font>";
	
		$dat = date('d-M-y');
		$tim = date('g:i:s a');
		$this->dropyarns($cur_user, "changed profile picture", $dat, $tim,"u");
	}
}
// Photo Upload
////////////////////////////////////////////


////////////////////////////////////////////
function delmessage($msgid)
{
/* Delete Message */
$this->dbconect();
$sqldel = "DELETE FROM msgs WHERE id='$msgid'";
$rundel = mysql_query($sqldel) or die("Error in Operation, contact Administrator: " . mysql_error());
echo "<center><font color='green'><b>Message Deleted Successfully ! </b></font></center>";
/* Delete Message */
}
////////////////////////////////////////////

function updateprof($uname, $logintok, $firstname, $lastname, $address)
# Update Profile
{
	// Session Authenticator
	$authresp = $this->SessionAuth($uname, $logintok);
	if(!$authresp)
	{
		$response = "Invalid, Not Logged In";
		return $response;	
	}
	$response = "";
	$res = $this->dbconect();
	$sqlup = "UPDATE  `members` SET `firstname`= '$firstname', `lastname`= '$lastname' WHERE (`email`='$uname' OR `phone`='$uname')";
	$runq = mysqli_query($res, $sqlup) or $this->ErrorLog("cmd=updateprof->".mysqli_error($res));
	if($runq) {
		$this->audittrail($uname,"profileupdate","success","web",$logintok);
		$response = "OK";
	}
	else {
		$this->audittrail($uname,"profileupdate","failed","web",$logintok);
		$response = "Update Failed";
	}
	return $response;

}
# Update Profile
/////////////////////////////////////////////

function changepwd($usrname, $logintok, $opass, $npass) {
// Change Password

	// Session Authenticator
	$authresp = $this->SessionAuth($usrname, $logintok);
	if(!$authresp)
	{
		$response = "Invalid, Not Logged In";
		return $response;	
	}
	
	$response="";
	$res = $this->dbconect();
	
	// Get Salt
	$saltqry = mysqli_query($res, sprintf("SELECT salt FROM `members` WHERE `email` = '%s' OR `phone` = '%s'", $usrname, $usrname)) or die(mysqli_error($res));
	$saltarray = mysqli_fetch_array($saltqry);
	$pwdsalt = $saltarray['salt'];
	
	$saltedopass = $opass.$pwdsalt;
	
	// Check if old password Matches
	$check_opass = "SELECT * FROM `members` WHERE (`email` = '$usrname' OR `phone` = '$usrname') AND password=SHA1('$saltedopass')";
	$runq = mysqli_query($res, $check_opass) or $this->ErrorLog("cmd=changepwd|checkold->".mysqli_error($res));
	$r1 =  mysqli_num_rows($runq); 
	// If Query Failes
	if(!$runq) {
		$response = "System Error";
		$this->audittrail($usrname,"changepwd","failed",$agent,$logintok);
		return $response;
	}
	if($r1 > 0) {
		// Perform Actual password change
		//$newsalt = time().mt_rand();
		$saltednpass = $npass.$pwdsalt;
		$chpassqry = "UPDATE `members` SET password=SHA1('$saltednpass') WHERE (`email` = '$usrname' OR `phone` = '$usrname')";
		$chngpassword = mysqli_query($res,$chpassqry) or $this->ErrorLog("cmd=changepwd|updatenew->".mysqli_error($res));
		// If Query Failes
		if(!$chngpassword) {
			$response = "System Error";
			$this->audittrail($usrname,"changepwd","failed:".mysqli_error($res),$agent,$logintok);
			mysqli_close($res);
			return $response;
		}
		$this->audittrail($usrname,"changepwd","success",$agent,$logintok);
		mysqli_close($res);
		$response = "OK";
	}
	else {
		// Cant Perform Change because old password did not match
		$this->audittrail($usrname,"changepwd","failed:Invalid old password","web",$logintok);
		$response = "Old password Invalid";
	}
	return $response;
}
////////////////////////////////////////////
}
// transactions, mail admin
############################################

?>

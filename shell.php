<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}
/* Inclusions */
include_once("registration.php");
include_once("user.php");
include_once("cart.php");	
/* Inclusions */

// Response Display
$regactions = new memreg;  
$useractions = new usersession; 
$useropts = new useroperations; 
$cartopts = new Cart;

// Process Login
if(isset($_POST['cmd']) && $_POST['cmd'] == "login")
{
	$loginres = NULL;
	$ret = $useractions->memlogin($_POST['username'], $_POST['password']);	
	$logintok = substr($ret, 3);
	if(strstr($ret, "OK")) {
		$_SESSION['curuser'] = $_POST['username'];
		$_SESSION['logintok'] = $logintok;
		$loginres = "1";
		if(isset($_POST['returl']) && $_POST['returl'] == "vcart.php") {
			$returl = "vcart.php";
		}
		else {
			$returl = "myaccount.php";
		}
		header("Location: ". $returl);
	}
	else {
		$errorurl = "../login.php?return=".$ret."&returl=product_summary.php";
		echo "Please Wait...";
		echo '<meta http-equiv="refresh" content="0;URL='.$errorurl.'" />';
	}	
}

// Process Register
if(isset($_POST['cmd']) && $_POST['cmd'] == "reg")
{
	$ret = $regactions->register($_POST['firstname'], $_POST['lastname'], $_POST['sex'], $_POST['email'], $_POST['phone'], $_POST['address'], $_POST['password']);
	
	if(strstr($ret, "OK")) {
		//header("Location: ../login.php?response=Registration Successful");
		$reta = $useractions->memlogin($_POST['email'], $_POST['password']);	
		if(strstr($reta, "OK")) {
			$logintok = substr($reta, 3);
			$_SESSION['curuser'] = $_POST['email'];
			$_SESSION['logintok'] = $logintok;
			
			if(isset($_POST['returl']) && $_POST['returl'] == "product_summary.php") {
				$returl = "../product_summary.php";	
			}
			else { 
				$returl = "../product_summary.php";
			}
			header("Location: ". $returl);
		}
	}
	
	else {
		header("Location: ../register.php?return=". $ret);	
	}
}

// Get New Password
if(isset($_POST['cmd']) && $_POST['cmd'] == "respwd") {
	$ret = $regactions->forgot($_POST['email']);
	if(strstr($ret, "OK")) {
		header("Location: ../pwreset.php?return=".  urlencode("New Password Sent to Email"));
	}
	else {
		header("Location: ../pwreset.php?return=". $ret);
	}
}
?>

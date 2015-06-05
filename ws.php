<?php
session_start();
/* Inclusions */
include_once("registration.php");
include_once("user.php");
include_once("cart.php");
/* Inclusions */
/* myisusu project
Author: Ewere Diagboya
Company: Wicee Solutions
Date/Time: 2014-09-09 6:14PM
Location: Ose's Room

Description: Main API Req/Response Interface

Response Codes:
	Sucess: OK
	Failure: ERR: Description
*/
header("Content-type: application/json");
$resp = array('0'=>"OK", '1'=>"ERR: ");
// Response Display
function SysResponse($processor)
{
	$resp = array('0'=>"OK", '1'=>"ERR: ");
	if($processor == "TRUE") {
		$res = json_encode($resp[0]);
		echo $res;
	}
	else if($processor == "OK") {
		$res = json_encode($resp[0]);
		echo $res;
	}
	else if(strstr($processor, "OK")) {
		$res = json_encode($processor);
		echo $res;
	}
	else {
		$res = json_encode($resp[1] . $processor);
		echo $res;
	}	
}
// Response Display
$regactions = new memreg;  
$useractions = new usersession; 
$useropts = new useroperations; 
$cartopts = new Cart;
switch($_GET['cmd'])
{
	case "reg":
		$processor = $regactions->register($_GET['firstname'], $_GET['lastname'], $_GET['sex'], $_GET['email'], $_GET['phone'], $_GET['address'], $_GET['password']);
		SysResponse($processor);
	break;
	
	case "login":
		$processor = $useractions->memlogin($_GET['username'], $_GET['password']); // Process Request Login
		SysResponse($processor);
	break;
	
	case "logout":
		$processor= $useractions->memlogout($_GET['username'], $_GET['logintok'], "web");
		
	break;
	
	case "update":
		$processor = $useropts->updateprof($_GET['username'], $_GET['logintok'], "web", $_GET['firstname'], $_GET['lastname'], $_GET['address']);
		SysResponse($processor);
	break;
	
	case "changepwd":
		$processor = $useropts->changepwd($_GET['username'], $_GET['logintok'], $_GET['oldpass'], $_GET['newpass']);
		SysResponse($processor);
	break;
	
	case "addtocart":
		$processor = $cartopts->AddtoCart($_GET['itemname'], $_GET['qty'], $_GET['price']);
		SysResponse($processor);
		
	break;
	
	case "deletefromcart":
		$processor = $cartopts->DeleteFromCart($_GET['cartid'], $_GET['itemid']);
		SysResponse($processor);
	break;
	
	case "updateqty":
		$processor = $cartopts->UpdateQty($_GET['cartid'], $_GET['itemid'], $_GET['qty'], $_GET['price']);
		SysResponse($processor);
	break;
	
	case "addusertocart":
		$processor = $cartopts->JoinusertoCart($_GET['cartid'], $_GET['userid']);
		SysResponse($processor);
	break;
	
	case "getitems":
		$processor = $cartopts->CartData($_GET['cartid']);
		SysResponse($processor);
	break;
	
	case "checkout":
		$processor = $cartopts->Checkout($_GET['cartid']);
		SysResponse($processor);
	break;
}
?>

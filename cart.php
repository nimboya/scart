<?php
/* Import Classes */
include_once("user.php");
/* General Shopping Cart
Author: Ewere Diagboya
Company: Wicee Solutions
Date/Time: 2014-09-08 9:52PM
Location: My Brother's Room

Description: Shopping Cart Functionalities
*/
class Cart extends useroperations {
 	// Add Item to Shopping Cart
	function AddtoCart($itemname, $desc, $qty, $price)
	{
		session_start();
		if(!isset($_SESSION['cartid']) && $_SESSION['cartid'] == '')
		{
			$_SESSION['cartid'] = date('YmdHis').mt_rand(0,9);
		}
		$total = $qty * $price;
		$tablename = "cart";
		$fields = array('cartid','itemname','desc','qty', 'unitprice','total');
		$values = array($_SESSION['cartid'],$itemname, $desc, $qty, $price, $total);
		$response = $this->InsertOpt($tablename, $fields, $values);
		return $response; 
	}
	// Delete Item from Shopping Cart
	function DeleteFromCart($cartid, $id)
	{
		// Delete Item from Cart
		$response = $this->Delete("WHERE `cartid` = '$cartid' AND `id`='$id'", "cart");
		return $response;
	}

	function UpdateQty($cartid, $id, $qty, $price)
	{
		// Update Stock Qty
		$tablename = "cart";
		$newtotal = $qty * $price;
		$response = $this->UpdateDB("SET `qty`='$qty', `total`='$newtotal' WHERE `cartid`='$cartid' AND `id`='$id'", $tablename);
		return $response;
	}

	function Checkout($cartid,$totalamount,$userid,$status="pending") 
	{
		// Perform Checkout
		$tablename = "cart";
		$response = $this->UpdateDB("SET `checkout`='1' WHERE `cartid`='$cartid'", $tablename);
		$fields = array('cartid','totalamount','userid','status');
		$values = array($cartid,$totalamount,$userid,$status);
		$this->InsertOpt("cartsum", $fields, $values);
		return $response;
	}
	
	function JoinusertoCart($cartid, $userid)
	{
		// Add User to Cart
		$res = $this->dbconect();
		$tablename = "cart";
		
		// Get Details
		$getuserqry = mysqli_query($res, "SELECT id, firstname, lastname, phone, email FROM members WHERE email='$userid'") or die(mysqli_error($res));
		$user = mysqli_fetch_array($getuserqry);
		
		$query_rsCart = sprintf("SELECT cart.id, content.title, content.photo,  cart.desc, cart.qty, cart.unitprice, cart.total FROM content, cart WHERE cartid = '%s' AND cart.itemname = content.id", trim($cartid));
		$rsCart = mysqli_query($res, $query_rsCart) or die(mysqli_error($res));
		$row_rsCart = mysqli_fetch_array($rsCart);
		//$totalRows_rsCart = mysqli_num_rows($rsCart);
		
		$query_rsTotalCart = sprintf("SELECT SUM(cart.total) sumtotal FROM content, cart WHERE cartid = '%s' AND cart.itemname = content.id", trim($cartid));
		$rsTotalCart = mysqli_query($res, $query_rsTotalCart) or die(mysqli_error($res));
		$row_rsTotalCart = mysqli_fetch_array($rsTotalCart);
		//$totalRows_rsTotalCart = mysqli_num_rows($rsTotalCart);
		
		// Send Mail
		$subj = "Your order confirmation";
		$msg = '<img src="http://www.dunniessentials.com/images/dunni2.png" alt=" Logo"  width="300" height="28" ><br/>';
		$msg .= "Dear ".$user['lastname'].",<br/>";
		$msg .= "Your order is succesful. Your order number is: ".$cartid."<br/>";
		$msg .= "For payment please Call: +2347034027550";
		$msg .= "<p></p>";
		$msg .='<table width="100%" border="0" cellpadding="0" cellspacing="0">';
        $msg .='<tr>';
        $msg .='<td width="37%" bgcolor="#A8196D">';
		$msg .='<font face="Calibri" color="#FFFFFF"><strong>Item Name </strong>';
		$msg .='</font></td>';
        $msg .='<td width="23%" bgcolor="#A8196D">';
		$msg .='<font face="Calibri" color="#FFFFFF"><strong>Qty</strong></font></td>';
        $msg .='<td width="20%" bgcolor="#A8196D">';
		$msg .='<font face="Calibri" color="#FFFFFF"><strong>Price</strong></font></td>';
        $msg .='<td width="20%" bgcolor="#A8196D">';
		$msg .='<font face="Calibri" color="#FFFFFF"><strong>Total</strong></font></td>';
        $msg .='<td width="20%" bgcolor="#A8196D">&nbsp;</td>';
        $msg .='</tr>';
        while ($row_rsCart = mysqli_fetch_array($rsCart,MYSQLI_ASSOC)) {
        $msg .='<tr>';
        $msg .="<td><font face='Calibri'>".$row_rsCart['title']."(".$row_rsCart['desc'].")</font></td>";
        $msg .="<td><font face='Calibri'>".$row_rsCart['qty']."</font></td>";
        $msg .="<td><font face='Calibri'>N".number_format($row_rsCart[unitprice],2)."</font></td>";
        $msg .="<td><font face='Calibri'>N".number_format($row_rsCart[total],2)."</font></td>";
	    $msg .="</tr>";
        } 
        $msg .='</table>';
		$msg .='<table width="200" border="0" align="right">';
        $msg .='<tr>';
        $msg .='<td width="69" bgcolor="#A8196D">';
		$msg .='<font face="Calibri" color="#FFFFFF">Total :</font></td>';
        $msg .="<td width='115'><font face='Calibri' color='#A2126E'><strong>N".number_format($row_rsTotalCart["sumtotal"],2)."</strong></font></td>";
        $msg .='</tr>';
        $msg .='</table>';
		$msg .= '<br><br>';
		$msg .= 'Thank you for shopping at Dunni Essentials';
		$msg .= '<p></p>';
		$msg .= '<p></p>';
		$msg .= '<p></p>';
		$msg .= 'For Enquiries<br />';
		$msg .= 'Dunni Essentials<br />';
		$msg .= 'Call: +2347034027550<br />';
		$msg .= 'Email: info@dunniessentials.com<br />';
		$this->mailnotifier($user['email'], $subj, $msg);
		
		// Send SMS
		/*
		$message = "Dear ".$user['lastname'].", Your order number is: ".$cartid.". and Your Total Price is : ".$totalamount." Thank you for using PhilHallmark Online";
		$send = file("http://smsc.xwireless.net/API/WebSMS/Http/v3.1/index.php?username=wicee&password=tarsus01&sender=Hallmark&to=".$user['phone']."&message=".urlencode($message)."&reqid=1&format=text");
		*/
		
		// Add User to Cart Checkout
		$response = $this->UpdateDB("SET `userid`='$userid', `checkout`='1'  WHERE `cartid`='$cartid'", $tablename);
		$_SESSION['cartid'] = NULL;
		return $response;
	}
	
	function CartData($cartid)
	{
		$getcartdata = "SELECT * FROM cart";
	}

}
?>

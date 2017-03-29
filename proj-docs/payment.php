<?php include "base.php"; ?>
<DOCTYPE html>
<html>  
<meta>  
<title>Order Payment</title>
</head>  
<body>

<p> My Open Orders: </p>
<?php

function printResult($chef_result) { //prints results from a select 
     echo "<table><tr>
     <th>Order_id</th>
     <th>Price</th>
     <th>Username</th>
     <th></th>
     </tr>";
     // output data of each row
     while($row = $chef_result->fetch_assoc()) {
         echo "<tr>";
         echo "<td>" . $row["order_id"] . "</td>";
         echo "<td>" . $row["price"] . "</td>";
         echo "<td>" . $row["customer_userName"] . "</td>";
         echo '<td><form name="frmDelete" action="" method="post"></td>';
         echo '<td><input type="hidden" name= "itemid" value="'.$row['order_id'].'"></td>';
         echo '<td><input type="hidden" name= "price" value="'.$row["price"].'"></td>';
         echo '<td><select name="ptype"><option value="credit">Credit</option><option value="cash">Cash</option></select></td>';
         echo '<td><input type="submit" name="payBtn" value="Pay" /></form></td>';
         echo "</tr>";
    	 }	 
     echo "</table>";
}

//Order query, if a single item has been ordered more or equal to 2 times, discount of 10% will apply
$order_sql = "SELECT o.order_id, SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 ELSE m.price * c.qty END) as price, o.customer_userName FROM orders o, contains c, menuitem m WHERE o.order_id = c.order_id AND c.name = m.name AND o.status != 'paid' AND o.customer_userName = '".$_SESSION['Username']."' GROUP BY o.order_id";
$order_result = $conn->query($order_sql);

if(isset($_POST['payBtn'])){
	$id = $_POST['itemid'];
	$price = $_POST['price'];
	$ptype = $_POST['ptype'];
	//check if there are enough qty in menuitems for purchase
	$check_avai = $conn->query("SELECT m.name FROM menuitem m, contains c WHERE m.name = c.name AND c.order_id = '".$id."' AND m.quantity - c.qty >= 0");

	//updates qty in menuitems
	$del_qty_sql = ("UPDATE menuitem m, contains c SET m.quantity= m.quantity - c.qty WHERE m.name = c.name AND c.order_id = '".$id."' AND m.quantity - c.qty >= 0");

	//create invoice
	$create_invoice_sql = "INSERT INTO Invoice VALUES ('".$id."', '".$_SESSION['Username']."', '".$price."', now(), '".$ptype."')";
	//invoice splitting

	//deletes order from order table
	$del_order_sql = "UPDATE Orders SET status = 'paid' WHERE order_id = '".$id."'";

	//check if qty available, then update qty
	if($check_avai->num_rows > 0) {
	$del_order_query = $conn->query($del_order_sql);
		if ($del_order_query){
			$create_invoice_query = $conn->query($create_invoice_sql);
			if ($create_invoice_query){
				$del_qty_query = $conn->query($del_qty_sql);
				$del_order_query = $conn->query($del_order_sql);

				if ($del_qty_query){
					echo "<script type='text/javascript'>alert('Order was paid for')</script>";
				}
				else{ echo "<script type='text/javascript'>alert('Order was not paid for')</script>";
				}
			}
			else {
				echo "<script type='text/javascript'>alert('Error creating invoice')</script>";
			}
		}
		else echo "<script type='text/javascript'>alert('Error deleting Order')</script>";
	}
	else{ echo "<script type='text/javascript'>alert('Not enough quantity, please change your order')</script>";
	}
}

printResult($order_result);

//echo "<p>".$_SESSION['Username']."</p>";

//echo "<p>".$_SESSION['Name']."</p>";

//echo "<p>".$_SESSION['Type']."</p>";


?>




</body>
</html>
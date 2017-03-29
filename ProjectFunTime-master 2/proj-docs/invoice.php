<?php include "base.php"; ?>

<DOCTYPE html>
  <html>  
  <meta>  
  <title>Invoice</title>
</head>  
<body>

  <p> <font size = "20" color = maroon >  Order</font></p>

  <?php

  $uName = ($_SESSION['Username']);

  $oid_sql = "SELECT order_id FROM Orders WHERE customer_userName = '".$uName."' AND paymentStatus = 'paid'";
  $oid_result = $conn->query($oid_sql);
  $row = $oid_result->fetch_array(MYSQLI_ASSOC);
  $orderid = $row['order_id'];

  function printResult($invoice_result) { 
    echo "<table><tr>
    <th>OrderID</th>
    <th>Cost</th>
    <th>Date Created</th>
    <th>Payment Type</th>
  </tr>";
  // output data of each row
  while($row = $invoice_result->fetch_assoc()) {
   echo "<tr>
   <td>" . $row["order_id"] . "</td>
   <td>" . $row["cost"] . "</td>
   <td>" . $row["createdate"] . "</td>
   <td>" . $row["paymentType"] . "</td>
 </tr>";
}  
echo "</table>";
}

echo "Your Invoices";
    // Query database for all Menu Items
$invoice_sql = "SELECT * FROM Invoice WHERE customer_userName = '".$uName."'";
$invoice_result = $conn->query($invoice_sql);
printResult($invoice_result);

?>

<a href="index.php"> Back to control panel

</body>
</html>
<?php include "base.php"; ?>

<DOCTYPE html>
  <html>  
  <meta>  
  <title>Order</title>
</head>  
<body>

  <p> <font size = "20" color = maroon >  Order</font></p>

  <?php

  $uName = ($_SESSION['Username']);

  $openorderexist = $conn->query("
    SELECT *
    FROM Orders
    WHERE customer_username = '".$uName."' AND paymentStatus = 'open'");

  if ($openorderexist->num_rows == 0) {
    // Case 1: CREATE NEW ORDER
    echo "No Open Orders"; 
    echo '<form action="" method="post"><input type="submit" name="NewOrder" value="Create New Order" /></form></td>';
    
    if(isset($_POST['NewOrder'])) {
      $mName = $_POST["NewOrder"];

      $makeneworder = $conn->query("INSERT INTO Orders (customer_userName, chef_userName, orderdate, cookeddate, paymentStatus, cookedStatus) VALUES('".$uName."', 'chef1', now(), NULL, 'open', 'open')");

      if ($makeneworder) {
        echo "<script type='text/javascript'>alert('New Order opened')</script>";
        header( "refresh:0;" );
      }
      else {
       echo "<script type='text/javascript'>alert('Failed to create new Order')</script>";
     }
   }
 }
 else {
    // Case 2: SELECT AN ORDER ID

  $oid_sql = "SELECT order_id FROM Orders WHERE customer_userName = '".$uName."' AND paymentStatus = 'open'";
  $oid_result = $conn->query($oid_sql);
 // $orderid = mysql_fetch_assoc($oid_sql);
  $row = $oid_result->fetch_array(MYSQLI_ASSOC);
  $orderid = $row['order_id'];

  function printResult($contains_result) { //prints results from a select 
    echo "<table><tr>
    <th>OrderID</th>
    <th>Item Name</th>
    <th>Quantity</th>
  </tr>";
  // output data of each row
  while($row = $contains_result->fetch_assoc()) {
   echo "<tr>
   <td>" . $row["order_id"] . "</td>
   <td>" . $row["name"] . "</td>
   <td>" . $row["qty"] . "</td>
 </tr>";
}  
echo "</table>";
}

echo "Open Orders";
    // Query database for all Menu Items
$contains_sql = "SELECT * FROM Contains WHERE order_id = '".$orderid."'";
$contains_result = $conn->query($contains_sql);
printResult($contains_result);



function printMenuResult($result) { 

  echo "<table><tr>
  <th>Name</th>
  <th>Price</th>
  <th>Category</th>
  <th>Description</th>
  <thAdd</th>
</tr>";
while($row = $result->fetch_array(MYSQLI_ASSOC)) {
  echo "<tr>";
  echo "<td>" . $row['name'] . "</td>";
  echo "<td>" . $row['price'] . "</td>";
  echo "<td>" . $row['category'] . "</td>";
  echo "<td>" . $row['description'] . "</td>";
  echo '<td><form action="" method="post"><input type="hidden" name="mName" value="'.$row['name'].'"></td>';
  echo '<td><input type="submit" name="AddItem" value="Add" /></td>';
  echo '<td><input type="text" name="quantity" size="18" pattern="*[0-9]" placeholder="Quantity"></td>';
  echo '<td><input type="submit" name="UpdateItem" value="Update Quantity" /></td>';
  echo '<td><input type="submit" name="RemoveItem" value="Remove" /></form></td>';
  echo "</tr>";
}
echo "</table>";
}

if(isset($_POST['AddItem'])) {
// here comes your delete query: use $_POST['deleteItem'] as your id
// $delete = $_POST['deleteItem']
// $sql = "DELETE FROM `tablename` where `id` = '$delete'"; 
  $mName = $_POST['mName'];
  $add_sql = "INSERT INTO Contains (order_id, name, qty) VALUES('".$orderid."', '".$mName."', '1')";
  $add_result = $conn->query($add_sql);
  if ($add_result) {
    echo "<script type='text/javascript'>alert('Item added to Order!')</script>";
    header( "refresh:0;" );
  }
  else {
   echo "<script type='text/javascript'>alert('Item already in Order!')</script>";
 }
}

if(isset($_POST['UpdateItem'])) {

  $mName = $_POST['mName'];

  $iteminorder_sql = "SELECT * FROM Contains WHERE name = '".$mName."' AND order_id = '".$orderid."'";
  $iteminorder_result = $conn->query($iteminorder_sql);

  if ($iteminorder_result->num_rows == 0) {
    echo "<script type='text/javascript'>alert('Item not in Order, quantity cannot be changed!')</script>";
  }
  else {
    $newqty = $_POST['quantity'];
    $update_sql = "UPDATE Contains SET qty = '".$newqty."' WHERE order_id = '".$orderid."' AND name = '".$mName."'";
    $update_result = $conn->query($update_sql);
    if ($update_result) {
      echo "<script type='text/javascript'>alert('Item Quantity changed in Order!')</script>";
      header( "refresh:0;" );
    }
    else {
     echo "<script type='text/javascript'>alert('Item Quantity failed to change in Order!')</script>";
   }
 }
}

if(isset($_POST['RemoveItem'])) {

  $mName = $_POST['mName'];

  $iteminorder_sql = "SELECT * FROM Contains WHERE name = '".$mName."' AND order_id = '".$orderid."'";
  $iteminorder_result = $conn->query($iteminorder_sql);

  if ($iteminorder_result->num_rows == 0) {
   echo "<script type='text/javascript'>alert('Item not in Order!')</script>";
 }
 else {

  $remove_sql = "DELETE FROM Contains WHERE order_id = '".$orderid."' AND name = '".$mName."'";
  $remove_result = $conn->query($remove_sql);

  if ($remove_result) {
    echo "<script type='text/javascript'>alert('Item removed from Order!')</script>";
    header( "refresh:0;" );
  }
  else {
   echo "<script type='text/javascript'>alert('Item failed to be removed from Order!')</script>";
 }
}
}

echo "Available Items to Order";
    // Query database for all Menu Items
$menu_sql = "SELECT * FROM MenuItem WHERE m_deleted = 'F' ";
$menu_result = $conn->query($menu_sql);
printMenuResult($menu_result);

}

?>
<a href="index.php"> Back to control panel

</body>
</html>
<?php include "base.php"; ?>
<DOCTYPE html>
<html>  
<meta>  
<title>Funtime Bistro</title>
</head>  
<body>

<p> <font size = "20" color = maroon >  Manage Chef</font></p>

<?php


function printResult($chef_result) { //prints results from a select 
     echo "<table><tr>
     <th>userName</th>
     <th>type</th>
     <th>password</th>
     <th>name</th>
     <th>phone</th>
     <th>address</th>
     <th>createDate</th>
     <th>u_deleted</th>
     </tr>";
     // output data of each row
     while($row = $chef_result->fetch_assoc()) {
         echo "<tr>
         <td>" . $row["userName"] . "</td>
         <td>" . $row["type"] . "</td>
         <td>" . $row["password"] . "</td>
         <td>" . $row["name"] . "</td>		
		 <td>" . $row["phone"] . "</td>
         <td>" . $row["address"] . "</td>
         <td>" . $row["createDate"] . "</td>
         <td>" . $row["u_deleted"] . "</td>
         </tr>";
    	 }	
     echo "</table>";
}


if ($conn) {

	if (!empty($_POST['username']) && !empty($_POST['password'])) {

		$username = ($_POST['username']);
    	$password = ($_POST['password']);
    	$name = ($_POST['name']);
    	$phone = ($_POST['phone']);
        $address = ($_POST['address']);

    	$checkname = $conn->query("SELECT * FROM Users WHERE userName = '".$username."'");
      
	if($checkname->num_rows == 1) {
        echo "<script type='text/javascript'>alert('Username is taken')</script>";
     }

    else {
        	$inschefquery = $conn->query("INSERT INTO Users (userName, type, password, name, phone, address, createDate, u_deleted) VALUES('".$username."', 'chef', '".$password."', '".$name."', '".$phone."', '".$address."', now(), 'F' )");
        	if($inschefquery) {
        		echo "<script type='text/javascript'>alert('Chef was added')</script>";
        	}
        	else {
            	echo "<script type='text/javascript'>alert('Chef cannot be added')</script>";    
     		}       
     	}
	} else 	
		if (!empty($_POST['delName'])) {

			$username = ($_POST['delName']);
			$checkname = $conn->query("SELECT * FROM Users WHERE name = '".$username."' AND u_deleted = 'F' ");

			if($checkname->num_rows == 0) {
        		echo "<script type='text/javascript'>alert('Chef does not exist')</script>";
			}
			else {
				$delchefquery = $conn->query("UPDATE Users SET u_deleted = 'T' WHERE name = '".$username."'");

				if ($delchefquery) {
        			echo "<script type='text/javascript'>alert('Chef was deleted')</script>";
        		}
				else {
        			echo "<script type='text/javascript'>alert('Chef already deleted')</script>";  
				}
			}
		}

        if (!empty($_POST['edUser'])) {
            $username = ($_POST['edUser']);
            $password = ($_POST['edPassword']);
            $name = ($_POST['edName']);
            $phone = ($_POST['edPhone']);
            $address = ($_POST['edAddress']);
            $checkname = $conn->query("SELECT * FROM Users WHERE userName = '".$username."' AND u_deleted = 'F' ");
            if($checkname->num_rows == 0) {
                echo "<script type='text/javascript'>alert('Chef does not exist!')</script>";
            }
            else {
                $editchefquery = $conn->query("UPDATE Users SET password = '".$password."', name = '".$name."', phone = '".$phone."', address = '".$address."' WHERE userName = '".$username."'");
                if ($editchefquery) {
                    echo "<script type='text/javascript'>alert('Chef edited successfully!')</script>";
                }
                
            }
        }


    echo 'Chef Table';
	// Query database for all Menu Items
	$chef_sql = "SELECT * FROM Users WHERE type = 'chef'";
	$chef_result = $conn->query($chef_sql);
	printResult($chef_result);

	$conn->close();

} else {
	echo "cannot connect";
}
?>  

<p>Create a new Chef user below:</p>
<form method="POST" action="managechef.php">
<!--refresh page when submit-->
<!--Input box text change-->
   <p>
   <input type="text" name="username" size="18" placeholder="Username" required>
   <input type="text" name="password" size="18" placeholder="Password" required>
   <input type="text" name="name" size="18" placeholder="Name" required>
   <input type="text" name="phone" size="10" placeholder="Phone Number" required>
   <input type="text" name="address" size="18" placeholder="Address" required>
<!--define two variables to pass the value--> 
<input type="submit" value="insert" name="insertsubmit"></p>
</form>

<p> Edit Chef Information: </p>
<form method="POST" action="managechef.php">
<!--refresh page when submit-->
   <p><input type="text" name="edUser" size="18" placeholder="Name of Chef to edit">
   <input type="text" name="edPassword" placeholder="New Password" size="18">
   <input type="text" name="edName" placeholder="New Name" size="18">
   <input type="text" name="edPhone" placeholder="New Phone Number" size="18">
   <input type="text" name="edAddress" placeholder="New Address" size="18">
<!--define two variables to pass the value-->
<input type="submit" value="update" name="updatesubmit"></p>
</form>

<p> Delete a Chef from Database</p>
<form method="POST" action="managechef.php">
<!--refresh page when submit-->
   <p><input type="text" name="delName" size="18" placeholder="userName">
<!--define two variables to pass the value-->
<input type="submit" value="delete" name="deletesubmit"></p>
</form>

<a href="index.php"> Back to control panel

</body>
</html>
<DOCTYPE html>
	<html>  
	<meta>  
	<title>Funtime Restaurant</title>

	<style>
        table,tr,th,td
        {
            border: 1px solid black;
        }
    </style>
    <p> <font size = "20" color = maroon >  Edit Menu</font></p>
</head>  
<body>

	<?php

	require "base.php";

	//prints results from a select 
	function printResult($menu_result) { 
		echo "<table><tr>
		<th>MenuItem ID</th>
		<th>Name</th>
		<th>Price</th>
		<th>Category</th>
		<th>Description</th>
		<th>Quantity</th>
		<th>Deleted?</th>
	</tr>";

	 // output data of each row
	while($row = $menu_result->fetch_assoc()) {
		echo "<tr>
		<td>" . $row["menu_id"] . "</td>
		<td>" . $row["name"] . "</td>
		<td>" . $row["price"] . "</td>
		<td>" . $row["category"] . "</td>
		<td>" . $row["description"] . "</td>  
		<td>" . $row["quantity"] . "</td>        
		<td>" . $row["m_deleted"] . "</td>
	</tr>";
}  
echo "</table>";
}



// Create a new Menu Item 
	if(isset($_POST['create'])) { //check if form was submitted

		$name = trim($_POST['insName']); //check for spaces
		$price = ($_POST['insPrice']);
		$cat = ($_POST['insCat']);
		$desc = ($_POST['insDescr']);
		$qty = ($_POST['insqty']);

		if (ctype_alpha(preg_replace('/\s+/', '', $name)) && ctype_digit(trim ($price)) && ctype_digit(trim ($qty))) {//check for correct input
			
			$checkname = $conn->query("SELECT * FROM Menuitem WHERE name = '".$name."'");
			$checkdel = $conn->query("SELECT * FROM Menuitem WHERE name = '".$name."' AND m_deleted = 'T' ");

			if($checkname->num_rows == 1) {

				if ($checkdel->num_rows == 1) {
					$insmenuquery = $conn->query("UPDATE Menuitem SET price = '".$price."', category = '".$cat."', description = '".$desc."', quantity = '".$qty."', m_deleted = 'F' WHERE name = '".$name."'");
					echo "<script type='text/javascript'>alert('Menu Item created successfully!')</script>";
				}
				else {
					echo "<script type='text/javascript'>alert('Menu Item already added!')</script>";
				}
			}

			else {
				$insmenuquery = $conn->query("INSERT INTO Menuitem (name, price, category, description, quantity, m_deleted) VALUES('".$name."', '".$price."', '".$cat."', '".$desc."', '".$qty."', 'F' )");

				echo "<script type='text/javascript'>alert('Menu Item added successfully!')</script>";
			}
		} else {
			echo "<script type='text/javascript'>alert('Invalid fields, please try again')</script>";
		}
	}



//Delete an existing Menu Item 
	if(isset($_POST['delete'])) { //check if form was submitted

		$name = ($_POST['delName']);

		if (ctype_alpha(preg_replace('/\s+/', '', $name))) {//check for correct input

			$checkname = $conn->query("SELECT * FROM Menuitem WHERE name = '".$name."' AND m_deleted = 'F' ");

			if($checkname->num_rows == 0) {
				echo "<script type='text/javascript'>alert('Menu Item does not exist!')</script>";
			}

			else {
				$delmenuquery = $conn->query("UPDATE Menuitem SET m_deleted = 'T' WHERE name = '".$name."'");
				echo "<script type='text/javascript'>alert('Menu Item deleted successfully!')</script>";
			}
		} else {
			echo "<script type='text/javascript'>alert('Inputs are not valid, please try again')</script>";
		}
	}  



//Edit an existing Menu Item 
	if(isset($_POST['editevry'])) { //check if form was submitted

		$name = trim($_POST['edName']);
		$price = ($_POST['edPrice']);
		$cat = ($_POST['edCat']);
		$desc = ($_POST['edDescr']);
		$qty = ($_POST['edqty']);


		if (ctype_alpha(preg_replace('/\s+/', '', $name)) && ctype_digit(trim ($price)) && ctype_digit(trim ($qty))) {//check for correct input

			$checkname = $conn->query("SELECT * FROM Menuitem WHERE name = '".$name."' AND m_deleted = 'F' "); //can't edit a deleted menuitem

			if($checkname->num_rows == 0) {
				echo "<script type='text/javascript'>alert('Menu Item does not exist! Please try again.')</script>";
			}

			else {
				$editmenuquery = $conn->query("UPDATE Menuitem SET price = '".$price."', category = '".$cat."', description = '".$desc."', quantity = '".$qty."' WHERE name = '".$name."'");
				echo "<script type='text/javascript'>alert('Menu Item edited successfully!')</script>";
			}
		}else {
			echo "<script type='text/javascript'>alert('Inputs are not valid, please try again')</script>";
		}
	}

//Edit an existing Menu Item's name
	if(isset($_POST['editname'])) { //check if form was submitted

		$name = trim($_POST['oldname']); 
		$newname = trim($_POST['newname']);

		if (ctype_alpha(preg_replace('/\s+/', '', $name)) && ctype_alpha(preg_replace('/\s+/', '', $newname))) {//check for correct input

			$checkname = $conn->query("SELECT * FROM Menuitem WHERE name = '".$name."' AND m_deleted = 'F' "); //can't edit name of deleted menuitem
			$checkname2 = $conn->query("SELECT * FROM Menuitem WHERE name = '".$newname."' ");

			if($checkname->num_rows == 0) {
				echo "<script type='text/javascript'>alert('Menu Item does not exist!')</script>";
			}

			else if($checkname2->num_rows == 1) {
				echo "<script type='text/javascript'>alert('Menu Item already exist, choose another name!')</script>";
			}

			else {
				$editmenuquery = $conn->query("UPDATE Menuitem SET name = '".$newname."' WHERE name = '".$name."'");
				echo "<script type='text/javascript'>alert('Menu Item edited successfully!')</script>";
			}
		}else {
			echo "<script type='text/javascript'>alert('Inputs are not valid, please try again')</script>";
		}
	}

// Query database for all Menu Items
	$menu_sql = "SELECT * FROM Menuitem";
	$menu_result = $conn->query($menu_sql);
	printResult($menu_result);


	$conn->close();
	?>  



	<p>Create a new Menu Item below:</p>

	<form method="POST">
		<!--refresh page when submit-->
		<!--Input box text change-->
		<input type="text" name="insName" size="18" pattern="*[A-Za-z]" placeholder="Name" required>
		<input type="text" name="insPrice" size="18" pattern="*[0-9]" placeholder="Price" required>
		<input type="text" name="insCat" size="18" placeholder="Category">
		<input type="text" name="insDescr" size="18" pattern="*[A-Za-z]" placeholder="Description">
		<input type="text" name="insqty" size="18" pattern="*[0-9]" placeholder="Quantity">
		<!--define two variables to pass the value--> 	
		<input type="submit" value="insert" name="create"></p>
	</form>

	<p> Edit MenuItem Name: </p>
	<form method="POST">
		<!--refresh page when submit-->
		<p><input type="text" name="oldname"  size="18" pattern="*[A-Za-z]" placeholder="Name of Item to edit" required>
			<input type="text" name="newname" size="18" pattern="*[A-Za-z]" placeholder="New name" required>
			<!--define two variables to pass the value-->
			<input type="submit" value="edit" name="editname"></p>
		</form>

		<p> Edit MenuItem: </p>
		<form method="POST">
			<!--refresh page when submit-->
			<p><input type="text" name="edName"  size="18" pattern="*[A-Za-z]" placeholder="Name of Item to edit" required>
				<input type="text" name="edPrice" size="18" pattern="*[0-9]" placeholder="New Price" required>
				<input type="text" name="edCat" size="18" placeholder="New Category">
				<input type="text" name="edDescr" size="18" pattern="*[A-Za-z]" placeholder="New Description">
				<input type="text" name="edqty" size="18" pattern="*[0-9]" placeholder="Quantity">
				<!--define two variables to pass the value-->
				<input type="submit" value="update" name="editevry"></p>
			</form>

			<p> Delete a Menu Item</p>
			<form method="POST">
				<!--refresh page when submit-->
				<p><input type="text" name="delName" size="18" pattern="*[A-Za-z]" placeholder="Name" required>
					<!--define two variables to pass the value-->	
					<input type="submit" value="delete" name="delete"></p>
				</form>

				<a href="index.php"> Back to control panel

				</body>
				</html>

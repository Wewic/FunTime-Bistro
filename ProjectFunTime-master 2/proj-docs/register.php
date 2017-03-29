<?php include "base.php"; ?>
<!DOCTYPE html>
<html>  
<meta>  
<title>Funtime Restaurant</title>
</head>  
<body>

<?php
if(!empty($_POST['username']) && !empty($_POST['password']))
{
    $username = ($_POST['username']);
    $password = ($_POST['password']);
    $name = ($_POST['name']);
    $phone = ($_POST['phone']);
    $address = ($_POST['address']);
    $type = ($_POST['type']);
     
     $checkusername = $conn->query("SELECT * FROM users WHERE userName = '".$username."'");
      
     if(mysqli_num_rows($checkusername) == 1)
     {
        echo "<h1>Error</h1>";
        echo "<p>Sorry, that username is taken. Please go back and try again.</p>";
     }
     else
     {
        $registerquery = $conn->query("INSERT INTO users (userName, type, password, name, phone, address, createDate, u_deleted) VALUES('".$username."', '".$type."', '".$password."', '".$name."', '".$phone."', '".$address."', now(), 'F' )");
        if($registerquery)
        {
            echo "<h1>Success</h1>";
            echo "<p>Your account was successfully created. Please <a href=\"index.php\">click here to login</a>.</p>";
        }
        else
        {
            echo "<h1>Error</h1>";
            echo "<p>Sorry, your registration failed. Please go back and try again.</p>";    
        }       
     }
}
else
{
    ?>
     
   <h1>Register</h1>
     
   <p>Please enter your details below to register.</p>
     
    <form method="post" action="register.php" name="registerform" id="registerform">
    <fieldset>
        <label for="username">Username:</label><input type="text" name="username" id="username" required/><br />
        <label for="password">Password:</label><input type="text" name="password" id="password" required/><br />
        <label for="name">Your Name:</label><input type="text" name="name" id="name" required/><br />
        <label for="phone">Phone:</label><input type="text" name="phone" id="phone" required/><br />
        <label for="address">Address:</label><input type="text" name="address" id="address" required/><br />
        <label for="type">Select one of the following:</label> <br />
        	<input type="radio" name="type" value="customer" required/> Customer <br />
        	<input type="radio" name="type" value="chef" /> Chef <br />	
        	<input type="radio" name="type" value="admin" /> Admin <br />
        <input type="submit" name="register" id="register" value="Register" />
    </fieldset>
    </form>
     
    <?php
}
?>
 
</div>
</body>
</html>
<?php include "base.php"; ?>

<!DOCTYPE html>
<html>  
<head>  
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<title>Funtime Bistro</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>  
<body>  
<div id="main">

<?php
//check if logged in
if(!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username']))
{
	//customer view
	if ($_SESSION['Type'] == 'customer'){
    	?>
 		<h1>Dear Customer, Welcome to FunTime Bistro</h1>
    	<h2>Menu</h2>
    	<p>
    	Thanks for logging in! You are <code><?=$_SESSION['Username']?></code> and your Name is <code><?=$_SESSION['Name']?></code>.</p>
     	
     	<p><a href="payment.php?' .SID. '"> Order Payment </a></p>
        <p><a href="order.php?' .SID. '"> Order </a></p>
        <p><a href="invoice.php?' .SID. '"> Invoices </a></p>
    	<a href="logout.php"> Logout
    	<?php
 	}
 	//admin view
 	else if ($_SESSION['Type'] == 'admin'){
    	?>
    	<h1>FunTime Bistro</h1>
    	<h2>Admin Control Panel</h2>
    	<p>
    	Hi Admin! You are <code><?=$_SESSION['Username']?></code> and your Name is <code><?=$_SESSION['Name']?></code>.</p>
     	This is what you can do:
     	<p><a href="managechef.php"> Manage Chef</p>
     	<p><a href='admin_menu.php'> Edit Menu</p>
     	<p><a href="logout.php"> Logout</p>
    	<?php
 	}
 	// chef view
 	else if ($_SESSION['Type'] == 'chef'){
    	?>
 		<h1>FunTime Bistro</h1>
    	<h2>Hi Chef, this is your control panel</h2>
    	<p>
    	Thanks for logging in! You are <code><?=$_SESSION['Username']?></code> and your Name is <code><?=$_SESSION['Name']?></code>.</p>
     
    	<a href="logout.php"> Logout
    	<?php
 	}

}
//authentication check
elseif(!empty($_POST['username']) && !empty($_POST['password']))
{
    $username = ($_POST['username']);
    $password = ($_POST['password']);
    $checklogin = $conn->query("SELECT * FROM users WHERE userName = '".$username."' AND password = '".$password."' AND u_deleted = 'F'");

    if(mysqli_num_rows($checklogin) == 1)
    {
    	$row = mysqli_fetch_array($checklogin);
        $name = $row['name'];
        $type = $row['type'];

        $_SESSION['Username'] = $username;
        $_SESSION['Name'] = $name;
        $_SESSION['Type'] = $type;
        $_SESSION['LoggedIn'] = 1;

        echo "<h1>Success</h1>";
        echo "<p>We are now redirecting you to the member area.</p>";
        echo "<meta http-equiv='refresh' content='=1;index.php' />";
        ?>
        Click <a href="index.php">here</a> to redirect if page is not refreshing!
        <?php
    }
    else
    {
        echo "<h1>Error</h1>";
        echo "<p>Sorry, your account could not be found. Please <a href=\"index.php\">click here to try again</a>.</p>";
    }
}
else
{
    ?>
     
   <h1>Member Login</h1>
     
   <p>Thanks for visiting! Please either login below, or <a href="register.php">click here to register</a>.</p>
     
    <form method="post" action="index.php" name="loginform" id="loginform">
    <fieldset>
        <label for="username">Username:</label><input type="text" name="username" id="username" /><br />
        <label for="password">Password:</label><input type="password" name="password" id="password" /><br />
        <input type="submit" name="login" id="login" value="Login" />
    </fieldset>
    </form>
     
   <?php
}
?>
 
</div>
</body>
</html>
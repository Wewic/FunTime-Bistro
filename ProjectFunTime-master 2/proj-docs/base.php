<?php
//maintain login session
session_start();

// put <?php include "base.php"; in other files to use this connection file

//  // connection to oracle database
// $dbhost = "dbhost.ugrad.cs.ubc.ca:1522/ug"; // this will ususally be 'localhost', but can sometimes differ
// $dbname = "database"; // the name of the database that you are going to use for this project
// $dbuser = "ora_p5f0b"; // the username that you created, or were given, to access your database
// $dbpass = "a32029134"; // the password that you created, or were given, to access your database

// connection to mysql
$dbhost = "funtime.cue09nzmpyxl.us-west-2.rds.amazonaws.com:3306"; // this will ususally be 'localhost', but can sometimes differ
$dbname = "funtime"; // the name of the database that you are going to use for this project
$dbuser = "awsuser"; // the username that you created, or were given, to access your database
$dbpass = "mypassword"; // the password that you created, or were given, to access your database 

try{
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
} catch(Exception $e) {
    error($e->getMessage());
    exit();
}


// below is for oracle connection as well

// $success = True; //keep track of errors so it redirects the page only if there are no errors
// $db_conn = OCILogon($dbuser, $dbpass, $dbhost);

// function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
// 	//echo "<br>running ".$cmdstr."<br>";
// 	global $db_conn, $success;
// 	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

// 	if (!$statement) {
// 		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
// 		$e = OCI_Error($db_conn); // For OCIParse errors pass the       
// 		// connection handle
// 		echo htmlentities($e['message']);
// 		$success = False;
// 	}

// 	$r = OCIExecute($statement, OCI_DEFAULT);
// 	if (!$r) {
// 		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
// 		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
// 		echo htmlentities($e['message']);
// 		$success = False;
// 	} else {

// 	}
// 	return $statement;

// }

// function executeBoundSQL($cmdstr, $list) {
// 	 Sometimes a same statement will be excuted for severl times, only
// 	 the value of variables need to be changed.
// 	 In this case you don't need to create the statement several times; 
// 	 using bind variables can make the statement be shared and just 
// 	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used 

// 	global $db_conn, $success;
// 	$statement = OCIParse($db_conn, $cmdstr);

// 	if (!$statement) {
// 		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
// 		$e = OCI_Error($db_conn);
// 		echo htmlentities($e['message']);
// 		$success = False;
// 	}

// 	foreach ($list as $tuple) {
// 		foreach ($tuple as $bind => $val) {
// 			//echo $val;
// 			//echo "<br>".$bind."<br>";
// 			OCIBindByName($statement, $bind, $val);
// 			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

// 		}
// 		$r = OCIExecute($statement, OCI_DEFAULT);
// 		if (!$r) {
// 			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
// 			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
// 			echo htmlentities($e['message']);
// 			echo "<br>";
// 			$success = False;
// 		}
// 	}

// }
?>
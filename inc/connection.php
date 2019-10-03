<?php
try{ //try catch made to catch the error in connection
    $servername = "localhost";
    $username = "root";
    $password = "mysql";
    
    $db = new PDO("mysql:host=$servername;dbname=database", $username, $password); // setting up new object with PDO class, Use of Magic costant to get the directory of database
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); //this throw exception error and passing aruguments to setAttribute method of PDO class
} catch (Exception $e) {
    echo "Unable to connect:";
    echo $e->getMessage(); // this method displays the error message
    exit;
}
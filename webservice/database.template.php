<?php

// TODO: provide username and password for database account
$username = "";
$password = "";

$mysql = new mysqli("localhost", $username, $password, "secure_storage");

if($mysql->connect_error) {
	die(json_encode(array("error" => true, "message" => "could not connect to the database")));
}

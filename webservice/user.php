<?php

require("database.php");
require("validation.php");

$request_type = $_SERVER['REQUEST_METHOD'];

if($request_type == "GET") {
	$user_id = $_GET['user_id'];

	if(!isset($user_id)) {
		// fetch all users
		$result = $mysql->query("SELECT userid, pubkey FROM User", MYSQLI_USE_RESULT);
		if($result) {
			$res_arr = array();
			while($row = $result->fetch_assoc()) {
				array_push($res_arr, array("user_id" => $row["userid"]));
			}
			echo json_encode(array("error" => false, "users" => $res_arr));
			$result->close();
		} else {
			die_message("no users found");
		}
	} else {
		if(!validate_user($user_id)) {
			die_message("recipient must be in the form cls00-cls18");
		}
		// fetch a specific user's public key
		$stmt = $mysql->prepare("SELECT pubkey FROM User WHERE userid = ?");
		if(!$stmt) {
			die_message("database error: " . $mysql->error);
		}
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$stmt->bind_result($public_key);
		$stmt->fetch();
		if(strlen($public_key) === 0) {
			die_message("no public key stored for user_id " . $user_id);
		}
 		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=\"{$user_id}_public_key.pem\";");
		echo $public_key;
		$stmt->free_result();
	}
}
elseif($request_type == "POST") {
	// create a user/public key
	$user_id = $_POST['user_id'];
	$public_key = $_FILES['public_key'];

	if(!isset($user_id)) {
		die_message("user_id is a required POST parameter");
	} else if(!validate_user($user_id)) {
		die_message("user_id must be in the form cls00-cls18");
	}
	if(!isset($public_key)) {
		die_message("public_key is a required POST parameter");
	} else if(!validate_public_key(file_get_contents($public_key['tmp_name']))) {
		die_message("public_key must be a valid PEM encoded public key");
	}

	$stmt = $mysql->prepare("INSERT INTO User VALUES (?, ?)");
	$stmt->bind_param("ss", $user_id, file_get_contents($public_key['tmp_name']));
	if($stmt->execute()) {
		echo json_encode(array("error" => false, "message" => "user created successfully"));
	} else {
		die_message("database error: " . $mysql->error);
	}
}
else {
	// error
	die_message("unrecognised request type");
}

$mysql->close();


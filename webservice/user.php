<?php

require("database.php");

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
			echo json_encode(array("error" => true, "message" => "no users found"));
		}
	} else {
		// fetch a specific user's public key
		$stmt = $mysql->prepare("SELECT pubkey FROM User WHERE userid = ?");
		if(!$stmt) {
			die(json_encode(array("error" => true, "message" => "database error: " . $mysql->error)));
		}
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$stmt->bind_result($public_key);
		$stmt->fetch();
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
		die(json_encode(array("error" => true, "message" => "user_id is a required POST parameter")));
	}
	if(!isset($public_key)) {
		die(json_encode(array("error" => true, "message" => "public_key is a required POST parameter")));
	}

	$stmt = $mysql->prepare("INSERT INTO User VALUES (?, ?)");
	$stmt->bind_param("ss", $user_id, file_get_contents($public_key['tmp_name']));
	if($stmt->execute()) {
		echo json_encode(array("error" => false, "message" => "user created successfully"));
	} else {
		die(json_encode(array("error" => true, "message" => "database error: " . $stmt->error)));
	}
}
else {
	// error
	die(json_encode(array("error" => true, "message" => "unrecognised request type")));
}

$mysql->close();

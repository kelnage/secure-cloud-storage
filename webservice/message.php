<?php

require("database.php");

$request_type = $_SERVER['REQUEST_METHOD'];

if($request_type == "GET") {
	// fetch a specific message
	$msg_id = $_GET['msg_id'];

	if(!isset($msg_id)) {
		die(json_encode(array("error" => true, "message" => "msg_id is a required GET parameter")));
	}

	$stmt = $mysql->prepare("SELECT encbody, iv FROM Message WHERE id = ?");
	if(!$stmt) {
		die(json_encode(array("error" => true, "message" => "database error: " . $mysql->error)));
	}
	$stmt->bind_param("s", $msg_id);
	$stmt->execute();
	$stmt->bind_result($body, $iv);
	$stmt->fetch();
	echo json_encode(array("error" => false, "encrypted_body" => $body, "iv" => $iv));
	$stmt->free_result();
}
elseif($request_type == "POST") {
	// create a message
	$message = $_POST['message'];
	$iv = $_GET['iv'];

	// TODO: validate input

	if(!isset($message)) {
		die(json_encode(array("error" => true, "message" => "message is a required POST parameter")));
	}
	if(!isset($iv)) {
		die(json_encode(array("error" => true, "message" => "iv is a required POST parameter")));
	}

	$stmt = $mysql->prepare("INSERT INTO Message VALUES (NULL, ?, ?)");
	$stmt->bind_param("ss", base64_encode($message), $iv);

	if($stmt->execute()) {
		echo json_encode(array("error" => false, "message" => "message stored successfully", "id" => $stmt->insert_id));
	} else {
		die(json_encode(array("error" => true, "message" => "database error: " . $stmt->error)));
	}
}
else {
	// error
	die(json_encode(array("error" => true, "message" => "unrecognised request type")));
}

$mysql->close();


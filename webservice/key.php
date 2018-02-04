<?php

require("database.php");

$request_type = $_SERVER['REQUEST_METHOD'];

if($request_type == "GET") {
	// retrieve a key for a message
	$key_id = $_GET["key_id"];
	$recipient = $_GET["recipient"];

	if(isset($key_id)) {
		$stmt = $mysql->prepare("SELECT msgId, enckey, fromUser, toUser FROM MessageKey WHERE id = ?");
		if(!$stmt) {
			die(json_encode(array("error" => true, "message" => "database error: " . $mysql->error)));
		}
		$stmt->bind_param("s", $key_id);
		$stmt->execute();
		$stmt->bind_result($msgId, $enckey, $fromUser, $toUser);
		$stmt->fetch();
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=\"{$fromUser}_encrypted_message_key.enc\";");
		echo base64_decode($enckey);
		$stmt->free_result();
	} elseif(isset($recipient)) {
		$stmt = $mysql->prepare("SELECT id, msgId, enckey, fromUser FROM MessageKey WHERE toUser = ?");
		if(!$stmt) {
			die(json_encode(array("error" => true, "message" => "database error: " . $mysql->error)));
		}
		$stmt->bind_param("s", $recipient);
		$stmt->execute();
		$stmt->bind_result($id, $msgId, $enckey, $fromUser);
		$message_keys = array();
		while ($stmt->fetch()) {
			array_push($message_keys, array("key_id" => $id, "from" => $fromUser));
		}
		echo json_encode(array("error" => false, "keys" => $message_keys));
		$stmt->free_result();
	} else {
		die(json_encode(array("error" => true, "message" => "key_id or recipient are required GET parameters")));
	}
}
elseif($request_type == "POST") {
	// upload a key for a message
	$key = $_POST['encrypted_key'];
	$msg_id = $_GET['msg_id'];
	$from = $_GET['from'];
	$to = $_GET['to'];

	// TODO: validate input

	if(!isset($key)) {
		die(json_encode(array("error" => true, "message" => "encrypted_key is a required POST parameter")));
	}
	if(!isset($msg_id)) {
		die(json_encode(array("error" => true, "message" => "msg_id is a required POST parameter")));
	}
	if(!isset($from)) {
		die(json_encode(array("error" => true, "message" => "from is a required POST parameter")));
	}
	if(!isset($to)) {
		die(json_encode(array("error" => true, "message" => "to is a required POST parameter")));
	}

	$stmt = $mysql->prepare("INSERT INTO MessageKey VALUES (NULL, ?, ?, ?, ?)");
	$stmt->bind_param("isss", $msg_id, base64_encode($key), $from, $to);

	if($stmt->execute()) {
		echo json_encode(array("error" => false, "message" => "message key stored successfully", "id" => $stmt->insert_id));
	} else {
		die(json_encode(array("error" => true, "message" => "database error: " . $stmt->error)));
	}
}
else {
	// error
	die(json_encode(array("error" => true, "message" => "unrecognised request type")));
}

$mysql->close();

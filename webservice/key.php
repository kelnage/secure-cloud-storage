<?php

require("database.php");
require("validation.php");

$request_type = $_SERVER['REQUEST_METHOD'];

if($request_type == "GET") {
	// retrieve a key for a message
	$key_id = $_GET["key_id"];
	$recipient = $_GET["recipient"];

	if(isset($key_id)) {
		$stmt = $mysql->prepare("SELECT msgId, enckey, fromUser, toUser FROM MessageKey WHERE id = ?");
		if(!$stmt) {
			die_message("database error: " . $mysql->error);
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
		if(!validate_user($recipient)) {
			die_message("recipient must be in the form cls00-cls18");
		}
		$stmt = $mysql->prepare("SELECT id, msgId, enckey, fromUser FROM MessageKey WHERE toUser = ?");
		if(!$stmt) {
			die_message("database error: " . $mysql->error);
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
		die_message("key_id or recipient are required GET parameters");
	}
}
elseif($request_type == "POST") {
	// upload a key for a message
	$key = $_FILES['encrypted_key'];
	$msg_id = $_POST['msg_id'];
	$from = $_POST['from'];
	$to = $_POST['to'];

	// TODO: validate key

	if(!isset($key)) {
		die_message("encrypted_key is a required POST parameter");
	}
	if(!isset($msg_id)) {
		die_message("msg_id is a required POST parameter");
	}
	if(!isset($from)) {
		die_message("from is a required POST parameter");
	} else if(!validate_user($from)) {
		die_message("from must be in the form cls00-cls18");
	}
	if(!isset($to)) {
		die_message("to is a required POST parameter");
	} else if(!validate_user($to)) {
		die_message("to must be in the form cls00-cls18");
	}

	$stmt = $mysql->prepare("INSERT INTO MessageKey VALUES (NULL, ?, ?, ?, ?)");
	$stmt->bind_param("isss", $msg_id, base64_encode(file_get_contents($key['tmp_name'])), $from, $to);

	if($stmt->execute()) {
		echo json_encode(array("error" => false, "message" => "message key stored successfully", "id" => $stmt->insert_id));
	} else {
		die_message("database error: " . $mysql->error);
	}
}
else {
	// error
	die_message("unrecognised request type");
}

$mysql->close();


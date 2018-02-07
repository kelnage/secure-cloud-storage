<?php

function validate_user($user) {
	if(preg_match("/^cls[01][0-9]$/", $user) === 1) {
		return true;
	}
	return false;
}

function validate_public_key($public_key_text) {
	if(preg_match("/^-----BEGIN PUBLIC KEY-----\n.*-----END PUBLIC KEY-----\n$/ms", $public_key_text) === 1) {
		return true;
	}
	return false;
}

function die_message($message) {
	die(json_encode(array("error" => "true", "message" => $message)));
}


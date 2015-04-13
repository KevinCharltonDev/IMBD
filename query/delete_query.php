<?php
require_once 'query/error.php';

function deleteLocation($conn, $l_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$c_id = (int) $c_id;
	$sql = "DELETE FROM LOCATION_TO_CONTACT WHERE `L_Id` = {$l_id}";
	$conn->query($sql);
	
	$sql = "DELETE FROM LOCATION WHERE `L_Id` = {$l_id}";
	$conn->query($sql);
	
	return success(DELETE_SUCCESS, "A location has been deleted.");
}

function deleteContact($conn, $c_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$c_id = (int) $c_id;
	$sql = "DELETE FROM LOCATION_TO_CONTACT WHERE `C_Id` = {$c_id}";
	$conn->query($sql);
	
	$sql = "DELETE FROM CONTACT WHERE `C_Id` = {$c_id}";
	$conn->query($sql);
	
	return success(DELETE_SUCCESS, "A contact has been deleted.");
}
?>
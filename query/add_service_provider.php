<?php
function add($conn, $name, $type, $description, $websites, $accountEmail) {
	require_once 'query/error.php';
	
	$results = success(INSERT_SUCCESS, "A new business has been added.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO SERVICE_PROVIDER " .
		"(`Name`,`Type`,`Description`,`AccountEmail`,`IsFlagged`,`IsSuspended`) " .
		"VALUES (?, ?, ?, ?, 1, 0)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('siss', $name, $type, $description, $accountEmail);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "A business with that name is already in the directory.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	$sql = "INSERT INTO WEBSITE (`Sp_Id`,`Url`) VALUES(?,?)";
	
	if($stmt = $conn->prepare($sql)) {
		foreach($websites as $website) {
			$stmt->bind_param('is', $id, $website);
			$stmt->execute();
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	$permission = grantPermission($conn, $id, $accountEmail, 1);
	if(isset($permission['Error'])) {
		return $permission;
	}
	
	return $results;
}

function grantPermission($conn, $id, $email, $value) {
	require_once 'query/error.php';
	
	$results = success(INSERT_SUCCESS, "Permission has been granted to update a business.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO UPDATE_PERMISSIONS (`Sp_Id`, `AccountEmail`, `HasPermission`) VALUES (?, ?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('isi', $id, $email, $value);
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "Update permission has already been granted.");
		}
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}
?>
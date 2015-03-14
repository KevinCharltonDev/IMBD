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

function addContact($conn, $fname, $lname, $email, $jobTitle, $phone, $extension, $spId) {
	require_once 'query/error.php';
	
	$results = success(INSERT_SUCCESS, "A new contact has been added.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO CONTACT " .
		"(`Fname`,`Lname`,`Email`,`JobTitle`,`PhoneNumber`,`Extension`,`Sp_Id`) " .
		"VALUES (?, ?, ?, ?, ?, ?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ssssssi', $fname, $lname, $email, $jobTitle, $phone, $extension, $spId);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The contact you entered already exists for this business.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}

	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	return $results;
}

function addLocation($conn, $address1, $address2, $city, $state, $zip, $spId) {
	require_once 'query/error.php';

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = success(INSERT_SUCCESS, "A new location has been added.");
	
	$sql = "INSERT INTO LOCATION " .
		"(`Address1`,`Address2`,`City`,`State`,`Zip`,`Sp_Id`) " .
		"VALUES (?, ?, ?, ?, ?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssssi', $address1, $address2, $city, $state, $zip, $spId);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The location you entered already exists for this business.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}

	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	return $results;
}

function linkLocationContact($conn, $locationId, $contactId){
	require_once 'query/error.php';
	
	$results = success(INSERT_SUCCESS, "The contact has been linked to a location.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO LOCATION_TO_CONTACT " .
		"(`C_Id`,`L_Id`) " .
		"VALUES (?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ii', $contactId, $locationId);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The location and contact are already linked.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}
?>
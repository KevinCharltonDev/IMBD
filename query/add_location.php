<?php
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
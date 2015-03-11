<?php
function addLocation($conn, $address1, $address2, $city, $state, $zip, $spId) {
	require_once 'query/error.php';
	
	$results = getSuccessArray(2);

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO LOCATION " .
		"(`Address1`,`Address2`,`City`,`State`,`Zip`,`Sp_Id`) " .
		"VALUES (?, ?, ?, ?, ?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ssssii', $address1, $address2, $city, $state, $zip, $spId);
		
		if(!$stmt->execute()) {
			return getErrorArray(8);
		}

		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}

	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	return $results;
}

function linkLocationContact($conn, $locationId, $contactId){
	require_once 'query/error.php';
	
	$results = getSuccessArray(2);

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO LOCATION_TO_CONTACT " .
		"(`C_Id`,`L_Id`) " .
		"VALUES (?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ii', $contactId, $locationId);
		
		if(!$stmt->execute()) {
			return getErrorArray(8);
		}

		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	return $results;
}
?>
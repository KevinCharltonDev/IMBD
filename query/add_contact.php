<?php
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
?>
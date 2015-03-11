<?php
function addContact($conn, $fname, $lname, $email, $jobTitle, $phone, $extension, $spId) {
	require_once 'query/error.php';
	
	$results = getSuccessArray(2);

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO CONTACT " .
		"(`Fname`,`Lname`,`Email`,`JobTitle`,`PhoneNumber`,`Extension`,`Sp_Id`) " .
		"VALUES (?, ?, ?, ?, ?, ?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ssssiii', $fname, $lname, $email, $jobTitle, $phone, $extension, $spId);
		
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
?>
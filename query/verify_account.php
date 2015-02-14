<?php
//Post variables are email, password, and json to echo the results in JSON format
if(isset($_POST['json']) and isset($_POST['email']) and isset($_POST['password'])) {
	$results = verifyAccount($_POST['email'], $_POST['password'], true);
	echo json_encode($results);
}

function verifyAccount($email, $password, $fromApp = false) {
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($fromApp) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$results = array();

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "SELECT `Email`, `LoginAttemptsRemaining`, `Type`, `IsSuspended` FROM ACCOUNT " .
	"WHERE `Email` = ? AND `Password` = sha2(?, 256)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $email, $password);
		$stmt->execute();
		$stmt->bind_result($user, $loginAttempts, $type, $suspended);
		
		if($stmt->fetch()) {
			$results['Verified'] = true;
			$results['Email'] = $user;
			$results['LoginAttempts'] = (int) $loginAttempts;
			$results['Type'] = (int) $type;
			$results['Suspended'] = (boolean) $suspended;
		}
		else {
			$results['Verified'] = false;
			$results['Email'] = '';
			$results['LoginAttempts'] = 0;
			$results['Type'] = -1;
			$results['Suspended'] = false;
		}
		
		$stmt->close();
		return $results;
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	$conn->close();
}
?>
<?php
function verifyAccount($conn, $email, $password) {
	require_once 'query/error.php';

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$results = array();
	
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
}
?>
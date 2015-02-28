<?php
function createAccount($conn, $screenname, $email, $password) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO ACCOUNT " .
	"(ScreenName, Email, Password, LoginAttemptsRemaining, Type, IsSuspended, IsFlagged) " .
	"VALUES (?, ?, sha2(?, 256), 1000000, 0, 0, 0)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sss', $screenname, $email, $password);
		$stmt->execute();	
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	return getSuccessArray(2);
}

function updatePassword($conn, $email, $oldpassword, $newpassword){
	require_once 'query/error.php';
	require_once 'verify_account.php';
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$account = verifyAccount($conn, $email, $oldpassword);
	
	if(isset($account["Error"])) {
		return $account;
	}
	
	if($account["Verified"]) {
		$sql = "UPDATE ACCOUNT " .
				"SET `Password` = sha2(?, 256) " .
				"WHERE `Email` = ?";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ss', $newpassword, $email);
			$stmt->execute();
			$stmt->close();
		}
		else {
			//Statement could not be prepared
			$results = getErrorArray(3);
		}
	}
}
?>
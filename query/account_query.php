<?php
require_once 'query/error.php';

function verifyAccount($conn, $email, $password) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Email`, `ScreenName`, `Type`, `IsSuspended` FROM ACCOUNT " .
	"WHERE `Email` = ? AND `Password` = sha2(concat(?, `Salt`), 256)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $email, $password);
		$stmt->execute();
		$stmt->bind_result($user, $screenName, $type, $suspended);
		
		if($stmt->fetch()) {
			$results['Verified'] = true;
			$results['Email'] = $user;
			$results['ScreenName'] = $screenName;
			$results['Type'] = (int) $type;
			$results['Suspended'] = (boolean) $suspended;
		}
		else {
			$results['Verified'] = false;
			$results['Email'] = '';
			$results['ScreenName'] = '';
			$results['Type'] = -1;
			$results['Suspended'] = false;
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function createAccount($conn, $screenname, $email, $password, $flagged = false) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if(strlen($screenname) < 3 || strlen($screenname) > 30) {
		return error(INVALID_ARGUMENTS, "Your screen name must be between 3 and 30 characters.");
	}
	if(strlen($email) < 5 || strlen($email) > 60) {
		return error(INVALID_ARGUMENTS, "Your email must be between 5 and 60 characters.");
	}
	if(!preg_match('/^.+\@.+\..+$/', $email)) {
		return error(INVALID_ARGUMENTS, "The email you entered is invalid.");
	}
	if(strlen($password) < 6) {
		return error(INVALID_ARGUMENTS, "Passwords must be at least 6 characters.");
	}
	
	$flagAccount = $flagged ? 1 : 0;
	
	$sql = "INSERT INTO ACCOUNT " .
	"(ScreenName, Email, Password, Type, IsSuspended, IsFlagged, Salt) " .
	"VALUES (?, ?, sha2(concat(?, ?), 256), 0, 0, {$flagAccount}, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$salt = base64_encode(mcrypt_create_iv(18, MCRYPT_DEV_URANDOM));
		$stmt->bind_param('sssss', $screenname, $email, $password, $salt, $salt);
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The screen name or email you entered has already been taken.");
		}	
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(INSERT_SUCCESS, "Your account has been created.");
}

function updatePassword($conn, $email, $oldpassword, $newpassword){
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if(strlen($newpassword) < 6) {
		return error(INVALID_ARGUMENTS, "Passwords must be at least 6 characters.");
	}
	
	$account = verifyAccount($conn, $email, $oldpassword);
	
	if(isset($account["Error"])) {
		return $account;
	}
	
	if($account["Verified"]) {
		$sql = "UPDATE ACCOUNT " .
				"SET `Password` = sha2(concat(?, ?), 256), `Salt` = ? " .
				"WHERE `Email` = ?";
		
		if($stmt = $conn->prepare($sql)) {
			$salt = base64_encode(mcrypt_create_iv(18, MCRYPT_DEV_URANDOM));
			$stmt->bind_param('ssss', $newpassword, $salt, $salt, $account['Email']);
			$stmt->execute();
			$stmt->close();
		}
		else {
			return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
		}
	}
	else {
		return error(ACCOUNT_INVALID, "The old password you entered is incorrect.");
	}
	
	return success(UPDATE_SUCCESS, "Your password has been changed.");
}

function reportAccount($conn, $screenName) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE ACCOUNT " .
		"SET `IsFlagged` = 1 " .
		"WHERE `ScreenName` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $screenName);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The account has been flagged.  Thank you.");
}

function deleteAccount($conn, $screenName) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$formattedScreenName = mysqli_real_escape_string($conn, $screenName);
	
	$sql = "UPDATE SERVICE_PROVIDER SET `AccountEmail` = null WHERE `AccountEmail` IN 
		(SELECT `Email` FROM ACCOUNT WHERE `ScreenName` = '{$formattedScreenName}')";
	
	$conn->query($sql);
	
	$sql = "DELETE FROM REVIEW WHERE `AccountEmail` IN 
		(SELECT `Email` FROM ACCOUNT WHERE `ScreenName` = '{$formattedScreenName}')";
		
	$conn->query($sql);
	
	$sql = "DELETE FROM UPDATE_PERMISSIONS WHERE `AccountEmail` IN 
		(SELECT `Email` FROM ACCOUNT WHERE `ScreenName` = '{$formattedScreenName}')";
		
	$conn->query($sql);
	
	$sql = "DELETE FROM ACCOUNT WHERE `ScreenName` = '{$formattedScreenName}'";
	
	$conn->query($sql);
	
	return error(DELETE_SUCCESS, "Your account has been deleted.");
}

function suspendAccount($conn, $screenName) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE ACCOUNT " .
		"SET `IsFlagged` = 0, `IsSuspended` = 1 " .
		"WHERE `ScreenName` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $screenName);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The account has been suspended.  Thank you.");
}

function validateAccount($conn, $screenName) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE ACCOUNT " .
		"SET `IsFlagged` = 0, `IsSuspended` = 0 " .
		"WHERE `ScreenName` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $screenName);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The account has been validated.  Thank you.");
}

function flaggedAccounts($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Email`, `ScreenName`, `Type` FROM ACCOUNT " .
		"WHERE `IsFlagged` = 1 AND `IsSuspended` = 0";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($email, $name, $type);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Email" => $email, "Name" => $name, "Type" => $type);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function suspendedAccounts($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Email`, `ScreenName`, `Type` FROM ACCOUNT " .
		"WHERE `IsSuspended` = 1";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($email, $name, $type);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Email" => $email, "Name" => $name, "Type" => $type);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function changeAccountType($conn, $screenName, $type) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$type = (int) $type;
	$formattedScreenName = mysqli_real_escape_string($conn, $screenName);
	$sql = "UPDATE ACCOUNT SET `Type` = {$type} WHERE `ScreenName` = '{$formattedScreenName}'";
	$conn->query($sql);
	
	return success(UPDATE_SUCCESS, "An account type has been changed.");
}
?>
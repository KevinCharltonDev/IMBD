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

function resetCode($conn, $email, $iv) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$result = null;
	$sql = "UPDATE ACCOUNT SET `ResetCode` = ? WHERE `Email` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $iv, $email);
		$stmt->execute();
		
		if($stmt->affected_rows > 0) {
			$result = success(UPDATE_SUCCESS, "The reset code has been set.");
		}
		else {
			$result = error(NOT_FOUND, "The email you entered was not found.");
		}
		
		$stmt->close();
		return $result;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function validCode($conn, $code) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$code = mysqli_real_escape_string($conn, $code);
	$sql = "SELECT `ResetCode` FROM ACCOUNT WHERE `ResetCode` = '{$code}'";
	$result = $conn->query($sql);
	
	if($result->num_rows > 0) {
		return true;
	}
	
	return false;
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
	if(!preg_match('/^[a-zA-Z0-9\+\?=;:!@#$%^&*(),._-]{6,50}$/', $password)) {
		return error(INVALID_ARGUMENTS, "Passwords must be at least 6 characters and spaces are not allowed.");
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
	
	if(!preg_match('/^[a-zA-Z0-9\+\?=;:!@#$%^&*(),._-]{6,50}$/', $newpassword)) {
		return error(INVALID_ARGUMENTS, "Passwords must be at least 6 characters and spaces are not allowed.");
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

function resetPassword($conn, $email, $resetcode, $newpassword){
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if(!preg_match('/^[a-zA-Z0-9\+\?=;:!@#$%^&*(),._-]{6,50}$/', $newpassword)) {
		return error(INVALID_ARGUMENTS, "Passwords must be at least 6 characters and spaces are not allowed.");
	}
	
	$sql = "UPDATE ACCOUNT " .
			"SET `Password` = sha2(concat(?, ?), 256), `Salt` = ?, `ResetCode` = null " .
			"WHERE `Email` = ? AND `ResetCode` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$salt = base64_encode(mcrypt_create_iv(18, MCRYPT_DEV_URANDOM));
		$stmt->bind_param('sssss', $newpassword, $salt, $salt, $email, $resetcode);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "Your password has been reset.");
}

function updateScreenName($conn, $email, $screenName) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if(strlen($screenName) < 3 || strlen($screenName) > 30) {
		return error(INVALID_ARGUMENTS, "Your screen name must be between 3 and 30 characters.");
	}
	
	$formattedEmail = mysqli_real_escape_string($conn, $email);
	$formattedScreenName = mysqli_real_escape_string($conn, $screenName);
	$sql = "UPDATE ACCOUNT SET `ScreenName` = '{$formattedScreenName}' WHERE `Email` = '{$formattedEmail}'";
	if($conn->query($sql)) {
		return success(UPDATE_SUCCESS, "Your screen name has been changed.");
	}
	else {
		return error(DUPLICATE_KEY, "The screen name you entered already exists.");
	}
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
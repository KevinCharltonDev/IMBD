<?php
require_once 'query/error.php';

function verifyAccount($conn, $email, $password) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Email`, `ScreenName`, `LoginAttemptsRemaining`, `Type`, `IsSuspended` FROM ACCOUNT " .
	"WHERE `Email` = ? AND `Password` = sha2(?, 256)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $email, $password);
		$stmt->execute();
		$stmt->bind_result($user, $screenName, $loginAttempts, $type, $suspended);
		
		if($stmt->fetch()) {
			$results['Verified'] = true;
			$results['Email'] = $user;
			$results['ScreenName'] = $screenName;
			$results['LoginAttempts'] = (int) $loginAttempts;
			$results['Type'] = (int) $type;
			$results['Suspended'] = (boolean) $suspended;
		}
		else {
			$results['Verified'] = false;
			$results['Email'] = '';
			$results['ScreenName'] = '';
			$results['LoginAttempts'] = 0;
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

function createAccount($conn, $screenname, $email, $password) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if(strlen($screenname) < 3 || strlen($screenname) > 30) {
		return error(INVALID_ARGUMENTS, "Your screen name must be between 3 and 30 characters.");
	}
	if(strlen($email) < 5 || strlen($email) > 60) {
		return error(INVALID_ARGUMENTS, "Your email must be between 5 and 60 characters.");
	}
	if(preg_match('/^.+\@.+\..+$/', $email)) {
		return error(INVALID_ARGUMENTS, "The email you entered is invalid.");
	}
	if(strlen($newpassword) < 6) {
		return error(INVALID_ARGUMENTS, "Passwords must be at least 6 characters.");
	}
	
	$sql = "INSERT INTO ACCOUNT " .
	"(ScreenName, Email, Password, LoginAttemptsRemaining, Type, IsSuspended, IsFlagged) " .
	"VALUES (?, ?, sha2(?, 256), 1000000, 0, 0, 0)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sss', $screenname, $email, $password);
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
				"SET `Password` = sha2(?, 256) " .
				"WHERE `Email` = ?";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ss', $newpassword, $account['Email']);
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

function myBusinesses($conn, $email, $page, $resultsPerPage){
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	$offset = $resultsPerPage * ($page - 1);
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` " .
		"FROM SERVICE_PROVIDER " .
		"WHERE `Sp_Id` IN (SELECT `Sp_Id` FROM UPDATE_PERMISSIONS WHERE HasPermission = 1 AND AccountEmail = ?) " .
		"AND `IsSuspended` = 0 " .
		"ORDER BY `Name` " .
		"LIMIT ? OFFSET ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sii', $email, $resultsPerPage, $offset);
		
		$stmt->execute();
		$stmt->bind_result($id, $name, $type, $description);
		
		while ($stmt->fetch()) {
			$resultsArray = array("Id" => $id, "Name" => $name, "Type" => $type, "Description" => $description);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
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
	return error(100, "Not yet implemented");
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

function requestUpdatePermission($conn, $id, $email) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO update_permissions (Sp_Id, AccountEmail, HasPermission) " .
		"VALUES (?, ?, 0)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('is', $id, $email);
		$stmt->execute();
		$stmt->close();
	}
	
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "Your request has been sent and will be reviewed by an administrator, thank you.");
}
?>
<?php
function verifyAccount($conn, $email, $password) {
	require_once 'query/error.php';

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
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
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function createAccount($conn, $screenname, $email, $password) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
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
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
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
	require_once "query/error.php";
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	//Replace spaces with wildcard for SQL LIKE
	$offset = $resultsPerPage * ($page - 1);
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` " .
		"FROM SERVICE_PROVIDER " .
		"WHERE Sp_Id in (SELECT Sp_Id from UPDATE_PERMISSIONS WHERE HasPermission = 1 AND AccountEmail = ?) AND " .
		"`IsSuspended` = 0 " .
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
?>
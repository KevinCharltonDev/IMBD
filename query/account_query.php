<?php
function createAccount($screenname, $email, $password, $squestion, $sanswer, $fromApp = false) {
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($fromApp) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	if(!accountExists($email)){
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$results = array();
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO account " .
	"(ScreenName, Email, Password, LoginAttemptsRemaining, Type, IsSuspended, IsFlagged, SecurityQuestion, SecurityAnswer) " .
	"VALUES (?, ?, sha2(?, 256), 1000000, 0, 0, 0, ?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssss', $screenname, $email, $password, $squestion, $sanswer);
		$stmt->execute();
		
		$foundResults = false;
		
		$stmt->close();
		
		if(!$foundResults) {
			$results = getErrorArray(4);
		}
	}
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
	$conn->close();
	echo "Account Created";
	}
	else{
		echo "That email is already in use.";
	}
}

function updatePassword($email, $oldpassword, $newpassword, $fromApp = false){
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($fromApp) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	if(correctPassword($email, $oldpassword)){
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$results = array();
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "UPDATE ACCOUNT " .
			"SET `Password` = sha2(?, 256) " .
			"WHERE `Email` = ? AND `Password` = sha2(?, 256)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sss', $newpassword, $email, $oldpassword);
		$stmt->execute();
		
		$foundResults = false;
		
		$stmt->close();
		
		if(!$foundResults) {
			$results = getErrorArray(4);
		}
	}
	
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	echo "Password updated successfully.";
	$conn->close();
	}
	else{
		echo "Incorrect password.";
	}
}

function correctPassword($email, $oldpassword, $fromApp = false){
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	$foundResults = false;
	
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
	
	$sql = "SELECT `Email`, `Password` FROM ACCOUNT " .
			"WHERE `Email` = ? AND `Password` = sha2(?, 256)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $email, $oldpassword);
		$stmt->execute();
		$stmt->bind_result($email, $oldpassword);
		
		if ($stmt->fetch()) {
			$foundResults = true;
		}
		
		$stmt->close();
		
		if(!$foundResults) {
			$results = getErrorArray(4);
		}
	}
	
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
	$conn->close();
	return $foundResults;
}

function accountExists($email, $fromApp = false){
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	$foundResults = false;
	
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
	
	$sql = "SELECT `Email` FROM ACCOUNT " .
			"WHERE `Email` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$stmt->bind_result($email);
		
		if ($stmt->fetch()) {
			$foundResults = true;
		}
		
		$stmt->close();
		
		if(!$foundResults) {
			$results = getErrorArray(4);
		}
	}
	
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
	$conn->close();
	return $foundResults;
}
?>
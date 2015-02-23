<?php
function review($id, $review, $email, $fromApp = false) {
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
	if(reviewExists($id, $email)){
		$sql = "UPDATE Review " .
			"SET Comment = ? " .
			"WHERE AccountEmail = ? AND Sp_Id = ?";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ssi', $review, $email, $id);
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
	}
	else{
		$sql = "INSERT INTO review " .
		"(ReviewDate, Rating, Comment, IsFlagged, IsSuspended, AccountEmail, Sp_Id) " .
		"VALUES (NOW(), 0, ?, 0, 0, ?, ?)";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ssi', $review, $email, $id);
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
	}
	$conn->close();
	echo "Review Submitted";
	
}


function reviewExists($id, $email, $fromApp = false) {
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
	
	$sql = "SELECT AccountEmail FROM review " .
	"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
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
<?php
function review($conn, $id, $review, $email) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	if(reviewExists($conn, $id, $email) === true){
		$sql = "UPDATE REVIEW " .
			"SET Comment = ? " .
			"WHERE AccountEmail = ? AND Sp_Id = ?";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ssi', $review, $email, $id);
			$stmt->execute();
			$stmt->close();
		}
		else {
			//Statement could not be prepared
			return getErrorArray(3);
		}
		
		return getSuccessArray(1);
	}
	else{
		$sql = "INSERT INTO REVIEW " .
		"(ReviewDate, Rating, Comment, IsFlagged, IsSuspended, AccountEmail, Sp_Id) " .
		"VALUES (NOW(), 0, ?, 0, 0, ?, ?)";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ssi', $review, $email, $id);
			$stmt->execute();
			$stmt->close();
		}
		else {
			//Statement could not be prepared
			return getErrorArray(3);
		}
		
		return getSuccessArray(2);
	}
}


function reviewExists($conn, $id, $email) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$found = false;
	$sql = "SELECT AccountEmail FROM REVIEW " .
	"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->bind_result($email);
		
		if ($stmt->fetch()) {
			$found = true;
		}
		
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
	return $found;
}
?>
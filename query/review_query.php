<?php
function updateReview($conn, $id, $rating, $comment, $email) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE REVIEW " .
		"SET Rating = ?, Comment = ?, ReviewDate = NOW() " .
		"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('issi', $rating, $comment, $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "Your review has been successfully updated.");
}

function insertReview($conn, $id, $rating, $comment, $email) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO REVIEW " .
		"(ReviewDate, Rating, Comment, IsFlagged, IsSuspended, AccountEmail, Sp_Id) " .
		"VALUES (NOW(), ?, ?, 0, 0, ?, ?)";
		
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('issi', $rating, $comment, $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(INSERT_SUCCESS, "A new review has been added.");
}


function reviewExists($conn, $id, $email) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
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
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $found;
}


function reportReview($conn, $screenName, $id) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE REVIEW " .
		"SET `IsFlagged` = 1 " .
		"WHERE `Sp_Id` = ? AND " .
		"`AccountEmail` IN (SELECT `Email` FROM ACCOUNT WHERE `ScreenName` = ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('is', $id, $screenName);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The review has been flagged.  Thank you.");
}

function suspendReview($conn, $email, $id) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE REVIEW " .
		"SET IsSuspended = 1, IsFlagged = 0 " .
		"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The review has been suspended.");
}

function deleteReview($conn, $email, $id) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "DELETE FROM REVIEW " .
		"WHERE `AccountEmail` = ? AND `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		if($stmt->affected_rows == 0) {
			return error(NOT_FOUND, "No review was found to delete.");
		}
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "Your review was successfully deleted.");
}

function flaggedReviews($conn) {
	require_once 'query/error.php';
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `AccountEmail`, `Comment`, `Sp_Id` FROM REVIEW WHERE `IsFlagged` = 1";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($email, $comment, $spid);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Email" => $email,"Comment" => $comment, "Sp_Id" => $spid);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function usersFlaggedReviews($conn, $email){
	require_once 'query/error.php';
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Comment` FROM REVIEW WHERE `AccountEmail` = ? AND (`IsFlagged` = 1 OR `IsSuspended` = 1)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$stmt->bind_result($comment);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Comment" => $comment);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}
?>
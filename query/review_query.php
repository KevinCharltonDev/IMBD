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


function reportReview($conn, $email, $id) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE REVIEW " .
		"SET IsFlagged = 1 " .
		"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, "Sorry, there was an error reporting this review.");
	}
	
	return success(UPDATE_SUCCESS, "The review was reported successfully, thank you.");
}

function deleteReview($conn, $email, $id) {
	require_once 'query/error.php';
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "DELETE FROM REVIEW " .
		"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, "Sorry, there was an error deleting your review.");
	}
	
	return success(UPDATE_SUCCESS, "Your review was successfully deleted.");
}
?>
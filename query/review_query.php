<?php
require_once 'query/error.php';

function updateReview($conn, $id, $rating, $comment, $email) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if($rating > 5 || $rating < 1) {
		return error(INVALID_ARGUMENTS, "Rating must be between 1 and 5.");
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
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	if($rating > 5 || $rating < 1) {
		return error(INVALID_ARGUMENTS, "Rating must be between 1 and 5.");
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

function suspendReview($conn, $screenName, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE REVIEW " .
		"SET `IsFlagged` = 0, `IsSuspended` = 1 " .
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
	
	return success(UPDATE_SUCCESS, "The review has been suspended.");
}

function validateReview($conn, $screenName, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE REVIEW " .
		"SET `IsFlagged` = 0, `IsSuspended` = 0 " .
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
	
	return success(UPDATE_SUCCESS, "The review has been validated.");
}

function deleteReview($conn, $screenName, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "DELETE FROM REVIEW WHERE " .
	"`AccountEmail` IN (SELECT `Email` FROM ACCOUNT WHERE `ScreenName` = ?) AND `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $screenName, $id);
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
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Sp_Id`, `Comment`, `Rating`, date_format(`ReviewDate`, '%b %d, %Y') AS `Date`, " .
		"`ScreenName` FROM REVIEW, ACCOUNT " .
		"WHERE REVIEW.`IsSuspended` = 0 AND REVIEW.`IsFlagged` = 1 " .
		"AND REVIEW.`AccountEmail` = ACCOUNT.`Email` " .
		"ORDER BY `Rating` DESC, `ReviewDate` DESC";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($id, $comment, $rating, $date, $name);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $id, "Comment" => $comment, "Rating" => $rating,
				"Date" => $date, "Name" => $name);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function suspendedReviews($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Sp_Id`, `Comment`, `Rating`, date_format(`ReviewDate`, '%b %d, %Y') AS `Date`, " .
		"`ScreenName` FROM REVIEW, ACCOUNT " .
		"WHERE REVIEW.`IsSuspended` = 1 " .
		"AND REVIEW.`AccountEmail` = ACCOUNT.`Email` " .
		"ORDER BY `Rating` DESC, `ReviewDate` DESC";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($id, $comment, $rating, $date, $name);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $id, "Comment" => $comment, "Rating" => $rating,
				"Date" => $date, "Name" => $name);
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
<?php
require_once "query/error.php";

function lookUp($conn, $sp_id) {
	require_once "query/service_query.php";
	
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$data = businessData($conn, $sp_id);
	if(isset($data["Error"])) {
		return $data;
	}
	
	$contacts = contacts($conn, $sp_id);
	if(isset($contacts["Error"])) {
		return $contacts;
	}
	
	$locations = locations($conn, $sp_id);
	if(isset($locations["Error"])) {
		return $locations;
	}
	
	$reviews = reviews($conn, $sp_id);
	if(isset($reviews["Error"])) {
		return $reviews;
	}
	
	$services = getServiceData($conn, $sp_id);
	if(isset($services["Error"])) {
		return $services;
	}
	
	$results["Data"] = $data;
	$results["Contacts"] = $contacts;
	$results["Locations"] = $locations;
	$results["Reviews"] = $reviews;
	$results["Services"] = $services;
	
	return $results;
}

function businessData($conn, $sp_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` FROM SERVICE_PROVIDER " .
		"WHERE `Sp_Id` = ? AND `IsSuspended` = 0";
			
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($id, $name, $type, $description);
	
		if(!$stmt->fetch()) {
			return error(NOT_FOUND, "No business was found for this ID");
		}
	
		$data = array("Sp_Id" => $id, "Name" => $name, "Type" => $type, "Description" => $description);
		$stmt->close();
	
		$websites = websites($conn, $sp_id);
		if(isset($websites["Error"])) {
			return $websites;
		}
		
		$data["Websites"] = $websites;
		return $data;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function websites($conn, $sp_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Url` FROM WEBSITE WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($url);
		
		$websites = array();
		while($stmt->fetch()) {
			array_push($websites, $url);
		}
		
		$stmt->close();
		return $websites;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function contacts($conn, $sp_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `C_Id`, `Fname`, `Lname`, `Email`, `JobTitle`, " .
	"`PhoneNumber`, `Extension` FROM CONTACT " .
	"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($c_id, $first, $last, $email, $job, $phone, $extension);
		
		$contacts = array();
		while($stmt->fetch()) {
			$resultsArray = array("C_Id" => $c_id, "First" => $first, "Last" => $last, "Email" => $email,
				"Job" => $job, "Phone" => $phone, "Extension" => $extension);
			array_push($contacts, $resultsArray);
		}
		
		$stmt->close();
		return $contacts;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function locations($conn, $sp_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `L_Id`, `Address1`, `Address2`, `City`, `State`, `Zip` " .
	"FROM LOCATION WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($l_id, $address1, $address2, $city, $state, $zip);
		
		$locations = array();
		$ids = array();
		while($stmt->fetch()) {
			$resultsArray = array("L_Id" => $l_id, "Address1" => $address1, "Address2" => $address2,
				"City" => $city, "State" => $state, "Zip" => $zip);
			array_push($ids, $l_id);
			array_push($locations, $resultsArray);
		}
		
		$stmt->close();
		
		for($index = 0; $index < count($ids); $index++) {
			$contactsAtLocation = contactsForLocation($conn, $sp_id, $ids[$index]);
			if(isset($contactsAtLocation["Error"])) {
				return $contactsAtLocation;
			}
			
			$locations[$index]["Contacts"] = $contactsAtLocation;
		}
		
		return $locations;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function contactsForLocation($conn, $sp_id, $l_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `C_Id`, CONCAT(`Fname`, ' ', `Lname`) FROM CONTACT " .
	"WHERE `Sp_Id` = ? AND `C_Id` IN " .
	"(SELECT `C_Id` FROM LOCATION_TO_CONTACT WHERE `L_Id` = ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ii', $sp_id, $l_id);
		$stmt->execute();
		$stmt->bind_result($c_id, $name);
		
		$contacts = array();
		while($stmt->fetch()) {
			$resultsArray = array("C_Id" => $c_id, "Name" => $name);
			array_push($contacts, $resultsArray);
		}
		
		$stmt->close();
		return $contacts;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function locationsForContact($conn, $sp_id, $c_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `L_Id`, `Address1` FROM LOCATION " .
	"WHERE `Sp_Id` = ? AND `L_Id` IN " .
	"(SELECT `L_Id` FROM LOCATION_TO_CONTACT WHERE `C_Id` = ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ii', $sp_id, $c_id);
		$stmt->execute();
		$stmt->bind_result($l_id, $address);
		
		$locations = array();
		while($stmt->fetch()) {
			$resultsArray = array("L_Id" => $l_id, "Address" => $address);
			array_push($locations, $resultsArray);
		}
		
		$stmt->close();
		return $locations;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function reviews($conn, $sp_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Comment`, `Rating`, date_format(`ReviewDate`, '%b %d, %Y') AS `Date`, " .
	"`ScreenName` FROM REVIEW, ACCOUNT " .
	"WHERE ACCOUNT.`IsSuspended` = 0 AND REVIEW.`IsSuspended` = 0 " .
	"AND REVIEW.`AccountEmail` = ACCOUNT.`Email` AND `Sp_Id` = ? " .
	"ORDER BY `Rating` DESC, `ReviewDate` DESC";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($comment, $rating, $date, $name);
		
		$reviews = array();
		while($stmt->fetch()) {
			$resultsArray = array("Comment" => $comment, "Rating" => $rating, "Date" => $date, "Name" => $name);
			array_push($reviews, $resultsArray);
		}
		
		$stmt->close();
		return $reviews;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function location($conn, $l_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Sp_Id`, `Address1`, `Address2`, `City`, `State`, `Zip` " .
		"FROM LOCATION WHERE `L_Id` = ?";
		
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $l_id);
		$stmt->execute();
		$stmt->bind_result($sp_id, $address1, $address2, $city, $state, $zip);
		
		$location = null;
		if($stmt->fetch()) {
			$location = array("Sp_Id" => $sp_id, "Address1" => $address1, "Address2" => $address2,
				"City" => $city, "State" => $state, "Zip" => $zip);
		}
		else {
			return error(NOT_FOUND, "This location does not exist.");
		}
		
		$stmt->close();
		return $location;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function contact($conn, $c_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Sp_Id`, `Fname`, `Lname`, `Email`, `JobTitle`, `PhoneNumber`, `Extension` " .
		"FROM CONTACT WHERE `C_Id` = ?";
		
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $c_id);
		$stmt->execute();
		$stmt->bind_result($sp_id, $first, $last, $email, $job, $phone, $extension);
		
		$contact = null;
		if($stmt->fetch()) {
			$contact = array("Sp_Id" => $sp_id, "First" => $first, "Last" => $last,
				"Email" => $email, "Job" => $job, "Phone" => $phone, "Extension" => $extension);
		}
		else {
			return error(NOT_FOUND, "This contact does not exist.");
		}
		
		$stmt->close();
		return $contact;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}
?>
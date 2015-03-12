<?php
function lookUp($conn, $sp_id) {
	require_once "query/error.php";

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	//First get data from service provider table
	$data = getData($conn, $sp_id);
	if(isset($data["Error"])) {
		return $data;
	}
	
	//Get contacts not linked to a location
	$contacts = getContacts($conn, $sp_id);
	if(isset($contacts["Error"])) {
		return $contacts;
	}
	
	//Get locations including all contacts linked to each location
	$locations = getLocations($conn, $sp_id);
	if(isset($locations["Error"])) {
		return $locations;
	}
	
	$reviews = getReviews($conn, $sp_id);
	if(isset($reviews["Error"])) {
		return $reviews;
	}
	
	$results["Data"] = $data;
	$results["Contacts"] = $contacts;
	$results["Locations"] = $locations;
	$results["Reviews"] = $reviews;
	
	return $results;
}

function getData($conn, $sp_id) {
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` " .
	"FROM SERVICE_PROVIDER " .
	"WHERE `Sp_Id` = ? AND `IsSuspended` = 0";
			
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($id, $name, $type, $description);
	
		if(!$stmt->fetch()) {
			return error(NOT_FOUND, "No business was found for this ID");
		}
	
		$data = array("Id" => $id, "Name" => $name, "Type" => $type, "Description" => $description);
		$stmt->close();
	
		$websites = getWebsites($conn, $sp_id);
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

function getWebsites($conn, $sp_id) {
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

function getContacts($conn, $sp_id) {
	$sql = "SELECT `Fname`, `Lname`, `Email`, `JobTitle`, " .
	"`PhoneNumber`, `Extension` FROM CONTACT " .
	"WHERE `Sp_Id` = ? AND `C_Id` NOT IN " .
	"(SELECT `C_Id` FROM LOCATION_TO_CONTACT);";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($first, $last, $email, $job, $phone, $extension);
		
		$contacts = array();
		while($stmt->fetch()) {
			$resultsArray = array("First" => $first, "Last" => $last, "Email" => $email,
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

function getLocations($conn, $sp_id) {
	$sql = "SELECT `L_Id`, `Address1`, `Address2`, `City`, `State`, `Zip` " .
	"FROM LOCATION WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($id, $address1, $address2, $city, $state, $zip);
		
		$locations = array();
		$ids = array();
		while($stmt->fetch()) {
			$resultsArray = array("Address1" => $address1, "Address2" => $address2,
			"City" => $city, "State" => $state, "Zip" => $zip);
			array_push($ids, $id);
			array_push($locations, $resultsArray);
		}
		
		$stmt->close();
		
		for($index = 0; $index < count($ids); $index++) {
			$contactsAtLocation = getContactsAtLocation($conn, $sp_id, $ids[$index]);
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

function getContactsAtLocation($conn, $sp_id, $l_id) {
	$sql = "SELECT `Fname`, `Lname`, `Email`, `JobTitle`, " .
	"`PhoneNumber`, `Extension` FROM CONTACT " .
	"WHERE `Sp_Id` = ? AND `C_Id` IN " .
	"(SELECT `C_Id` FROM LOCATION_TO_CONTACT WHERE `L_Id` = ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ii', $sp_id, $l_id);
		$stmt->execute();
		$stmt->bind_result($first, $last, $email, $job, $phone, $extension);
		
		$contacts = array();
		while($stmt->fetch()) {
			$resultsArray = array("First" => $first, "Last" => $last, "Email" => $email,
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

function getReviews($conn, $sp_id) {
	$sql = "SELECT `Comment`, `Rating`, date_format(`ReviewDate`, '%b %d, %Y') AS `Date`, " .
	"`ScreenName` FROM REVIEW, ACCOUNT " .
	"WHERE ACCOUNT.`IsSuspended` = 0 AND REVIEW.`IsSuspended` = 0 " .
	"AND REVIEW.`AccountEmail` = ACCOUNT.`Email` AND `Sp_Id` = ? " .
	"ORDER BY `ReviewDate` DESC";
	
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
?>
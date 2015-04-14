<?php
require_once 'query/error.php';

function search($conn, $search, $searchloc, $page, $resultsPerPage) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	//Replace spaces with wildcard for SQL LIKE
	$match = '%' . preg_replace('/\s+/', '%', trim($search)) . '%';
	$matchloc = '%' . preg_replace('/\s+/', '%', trim($searchloc)) . '%';
	$offset = $resultsPerPage * ($page - 1);
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` " .
		"FROM SERVICE_PROVIDER " .
		"WHERE (`Name` LIKE ? OR `Description` LIKE ?) AND " .
		"`IsSuspended` = 0 " .
		"ORDER BY `Name` " .
		"LIMIT ? OFFSET ?";
		
	if($searchloc !== "") {
		$sql = "SELECT DISTINCT `Sp_Id`, `Name`, `Type`, `Description` FROM ( " .
			"SELECT SERVICE_PROVIDER.`Sp_Id`, `Name`, `Type`, `Description`, " .
			"concat(`Address1`, ' ', `Address2`, ' ', `City`, ' ', `State`, ' ', `Zip`) AS `Location` " .
			"FROM SERVICE_PROVIDER, LOCATION " .
			"WHERE SERVICE_PROVIDER.`Sp_Id` = LOCATION.`Sp_Id` " .
			"AND `IsSuspended` = 0 " .
			"HAVING `Location` LIKE ? " .
			") AS `AllResults` " .
			"WHERE (`Name` LIKE ? OR `Description` LIKE ?) " .
			"ORDER BY `Name`" .
			"LIMIT ? OFFSET ?";
	}
	
	if($stmt = $conn->prepare($sql)) {
		if($searchloc === "")
			$stmt->bind_param('ssii', $match, $match, $resultsPerPage, $offset);
		else
			$stmt->bind_param('sssii', $matchloc, $match, $match, $resultsPerPage, $offset);
		
		$stmt->execute();
		$stmt->bind_result($id, $name, $type, $description);
		
		while ($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $id, "Name" => $name, "Type" => $type, "Description" => $description);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}

function myBusinesses($conn, $email){
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` " .
		"FROM SERVICE_PROVIDER " .
		"WHERE `Sp_Id` IN (SELECT `Sp_Id` FROM UPDATE_PERMISSIONS WHERE HasPermission = 1 AND AccountEmail = ?) " .
		"AND `IsSuspended` = 0 " .
		"ORDER BY `Name`";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $email);
		
		$stmt->execute();
		$stmt->bind_result($id, $name, $type, $description);
		
		while ($stmt->fetch()) {
			$results[] = array("Sp_Id" => $id, "Name" => $name,
				"Type" => $type, "Description" => $description);
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}

function searchAll($conn, $search, $excludedServices, $page, $resultsPerPage) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	//Replace spaces with wildcard for SQL LIKE
	$match = preg_replace('/\s+/', '%', trim($search));
	$formattedSearch = mysqli_real_escape_string($conn, $match);
	$formattedExcludedServices = implode(',', $excludedServices);
	$offset = $resultsPerPage * ($page - 1);
	
	$sql = "CALL SearchAll('{$formattedSearch}', '{$formattedExcludedServices}', {$resultsPerPage}, {$offset})";
	$results = $conn->query($sql);
	
	$businesses = array();
	while($row = $results->fetch_assoc()) {
		$resultsArray = array(
			"Sp_Id" => (int) $row['Sp_Id'],
			"Name" => $row['Name'],
			"Type" => (int) $row['Type'],
			"Description" => $row['Description']);
		
		$businesses[] = $resultsArray;
	}
	
	$results->close();
	if($conn->more_results())
		$conn->next_result();
	
	return $businesses;
}
?>
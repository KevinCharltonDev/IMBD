<?php
function search($search, $page, $resultsPerPage, $fromApp = false) {
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
	
	//Replace spaces with wildcard for SQL LIKE
	$match = '%' . preg_replace('/\s+/', '%', trim($search)) . '%';
	$offset = $resultsPerPage * ($page - 1);
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description` " .
	"FROM SERVICE_PROVIDER " .
	"WHERE (`Name` LIKE ? OR `Description` LIKE ?) " .
	"AND `IsSuspended` = 0 " .
	"ORDER BY `Name` " .
	"LIMIT ? OFFSET ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ssii', $match, $match, $resultsPerPage, $offset);
		$stmt->execute();
		$stmt->bind_result($id, $name, $type, $description);
		
		$foundResults = false;
		
		while ($stmt->fetch()) {
			$foundResults = true;
			$resultsArray = array("Id" => $id, "Name" => $name, "Type" => $type, "Description" => $description);
			array_push($results, $resultsArray);
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
	return $results;
}
?>
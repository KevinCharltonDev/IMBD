<?php
function search($conn, $search, $searchloc, $page, $resultsPerPage) {
	require_once "query/error.php";
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$results = array();
	
	//Replace spaces with wildcard for SQL LIKE
	$match = '%' . preg_replace('/\s+/', '%', trim($search)) . '%';
	$matchloc = '%' . preg_replace('/\s+/', '%', trim($searchloc)) . '%';
	$offset = $resultsPerPage * ($page - 1);
	
	if($searchloc == ""){
		$locquery = " ";
		$locquery2 = " ";
		$locquery3 = "NOT(?) ";
	}
	else{
		$locquery = ", LOCATION ";
		$locquery2 = " SERVICE_PROVIDER.SP_ID = LOCATION.SP_ID AND ";
		$locquery3 = "(`address1` LIKE ?) ";
	}
	
	$sql = "SELECT SERVICE_PROVIDER.Sp_Id, `Name`, `Type`, `Description` " .
	"FROM SERVICE_PROVIDER" . $locquery . 
	"WHERE " . $locquery2 .
	"(`Name` LIKE ? OR `Description` LIKE ?) AND " . $locquery3 . 
	"AND SERVICE_PROVIDER.IsSuspended = 0 " .
	"ORDER BY `Name` " .
	"LIMIT ? OFFSET ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssii', $match, $match, $matchloc, $resultsPerPage, $offset);
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
			return getErrorArray(4);
		}
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	return $results;
}
?>
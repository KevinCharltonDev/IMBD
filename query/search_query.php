<?php
//Post values are s, page, and rpp for search, page number, and results per page
if(isset($_POST['s']) and isset($_POST['page'])) {
	$search = $_POST['s'];
	$page = (int) $_POST['page'];
	if($page < 1)
		$page = 1;
	
	$resultsPerPage = 5;
	if(isset($_POST['rpp']))
		$resultsPerPage = (int) $_POST['rpp'];
	if($resultsPerPage < 1)
		$resultsPerPage = 5;
	
	echo json_encode(search($search, $page, $resultsPerPage, true));
}

function search($search, $page, $resultsPerPage, $isPost = false) {
	$error1 = array("Error" => "The connection strings could not be found.", "Code" => 1);
	$error2 = array("Error" => "Could not connect to database", "Code" => 2);
	$error3 = array("Error" => "The SQL statement could not be prepared.", "Code" => 3);
	
	$fileName = 'connect/config.php';
	if($isPost)
		$fileName = '../connect/config.php';
	if(is_file($fileName)) {
		include_once($fileName);
		$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
		$results = array();
	
		if ($conn->connect_error) {
			return $error2;
		}
		else {
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
				
				$count = 0;
				while ($stmt->fetch()) {
					$resultsArray = array("Id" => $id, "Name" => $name, "Type" => $type, "Description" => $description);
					array_push($results, $resultsArray);
					$count++;
				}
				
				array_push($results, array("Count" => $count));
				
				$stmt->close();
			}
			else {
				//Statement could not be prepared
				return $error3;
			}
		}
		
		$conn->close();
		return $results;
	}
	else {
		//Could not find the connection string file
		return $error1;
	}
}
?>
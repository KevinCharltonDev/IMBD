<?php
require_once 'query/error.php';

function addColumn($conn, $serviceName, $columnName, $columnDescription, $type, $possibleValuesKey) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL AddColumn(?, ?, ?, ?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssis', $serviceName, $columnName, $columnDescription, $type, $possibleValuesKey);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "An attempt was made to add a column.  Check to see if it was successful.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function addPossibleValue($conn, $key, $value) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL AddPossibleValue(?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $key, $value);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "An attempt was made to add a value.  Check to see if it was successful.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function chooseService($conn, $serviceName, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL ChooseService(?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $serviceName, $sp_id);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "A new service has been selected.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function createService($conn, $serviceName, $serviceDescription) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL CreateService(?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $serviceName, $serviceDescription);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "An attempt was made to create a new service.  Check to see if it was successful.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function getServiceData($conn, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sp_id = (int) $sp_id;
	$sql = "CALL GetServices(" . $sp_id . ")";
	$result = $conn->query($sql);
	
	$services = array();
	while($row = $result->fetch_assoc()) {
		$services[] = $row;
	}
	
	$result->close();
	$conn->next_result(); //Procedures can return more than one table so this is necessary
	
	$serviceData = array();
	foreach($services as $service) {
		$sql = "CALL GetServiceData('" . $service["Name"] . "', " . $sp_id . ")";
		$result = $conn->query($sql);
		
		while($row = $result->fetch_assoc()) {
			$serviceData[$service["Name"]] = $row;
		}
		
		$result->close();
		$conn->next_result();
	}
	
	foreach($serviceData as $serviceName => $service) {
		$metadata = getServiceMetadata($conn, $serviceName);
		
		foreach($service as $columnName => $columnValue) {
			$type = 1;
			if($columnName === 'Sp_Id') {
				$type = 3;
			}
			else {
				$type = (int) $metadata[$serviceName]['Columns'][$columnName]['Type'];
			}
			
			$column = &$serviceData[$serviceName][$columnName];
			switch($type) {
				case 0: $column = (boolean) $column; break;
				case 3: $column = (int) $column; break;
				case 4: $column = (double) $column; break;
			}
		}
	}
	
	return $serviceData;
}

function getServiceMetadata($conn, $serviceName) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$metadata = array();
	$sql = "CALL GetServiceMetadata('" . $serviceName . "')";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()) {
		$serviceName = $row['Table_Name'];
		$metadata[$serviceName]["Description"] = $row["Table_Description"];
		$metadata[$serviceName]["Columns"][$row["Column_Name"]]["Description"] = $row["Column_Description"];
		$metadata[$serviceName]["Columns"][$row["Column_Name"]]["Type"] = $row["Type"];
		$metadata[$serviceName]["Columns"][$row["Column_Name"]]["PossibleValuesKey"] = $row["PossibleValuesKey"];
	}
	$conn->next_result();
	
	return $metadata;
}

function getAllServiceMetadata($conn) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$metadata = array();
	$sql = "CALL GetServiceMetadata(null)";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()) {
		$serviceName = $row['Table_Name'];
		$metadata[$serviceName]["Description"] = $row["Table_Description"];
		$metadata[$serviceName]["Columns"][$row["Column_Name"]]["Description"] = $row["Column_Description"];
		$metadata[$serviceName]["Columns"][$row["Column_Name"]]["Type"] = $row["Type"];
		$metadata[$serviceName]["Columns"][$row["Column_Name"]]["PossibleValuesKey"] = $row["PossibleValuesKey"];
	}
	$conn->next_result();
	
	return $metadata;
}

function getServices($conn, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL GetServices(?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $sp_id);
		$stmt->execute();
		$stmt->bind_result($name, $description);
		
		$results = array();
		while($stmt->fetch()) {
			array_push($results, array('Name' => $name, 'Description' => $description));
		}
		
		$stmt->close();
		
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function getAllServices($conn) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL GetServices(null)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($name, $description);
		
		$results = array();
		while($stmt->fetch()) {
			array_push($results, array('Name' => $name, 'Description' => $description));
		}
		
		$stmt->close();
		
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function rejectService($conn, $serviceName, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL RejectService(?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $serviceName, $sp_id);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "A service has been rejected.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function setServiceValue($conn, $serviceName, $columnName, $columnValue, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL SetServiceValue(?, ?, ?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssi', $serviceName, $columnName, $columnValue, $sp_id);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "A value has been set.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

?>
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

function deleteColumn($conn, $serviceName, $columnName) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL DeleteColumn(?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ss', $serviceName, $columnName);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "An attempt was made to delete a column.  Check to see if it was successful.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function deleteService($conn, $serviceName) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL DeleteService(?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $serviceName);
		$stmt->execute();
		$stmt->close();
		
		return success(INSERT_SUCCESS, "An attempt was made to delete a service.  Check to see if it was successful.");
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function getPossibleValues($conn, $key) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Value` FROM POSSIBLE_VALUES WHERE `Key` = '" .
		mysqli_real_escape_string($conn, $key) . "'";
	$results = $conn->query($sql);
	
	$values = array();
	while($row = $results->fetch_assoc()) {
		$values[] = $row['Value'];
	}
	
	$results->close();
	$conn->next_result();
	
	return $values;
}

function getServiceData($conn, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sp_id = (int) $sp_id;
	$services = getServices($conn, $sp_id);
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
			if(($type === 0 || $type === 3 || $type === 4) && $column === '-1') {
				$column = '';
			}
			else {
				switch($type) {
					case 0: $column = (boolean) $column; break;
					case 3: $column = (int) $column; break;
					case 4: $column = (double) $column; break;
				}
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
	
	$result->close();
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
	
	$result->close();
	$conn->next_result();
	
	return $metadata;
}

function getServices($conn, $sp_id) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sp_id = (int) $sp_id;
	$sql = "CALL GetServices({$sp_id})";
	$result = $conn->query($sql);
	
	$results = array();
	while($row = $result->fetch_assoc()) {
		$results[] = $row;
	}
	
	$result->close();
	$conn->next_result();
	
	return $results;
}

function getAllServices($conn) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "CALL GetServices(null)";
	$result = $conn->query($sql);
	
	$results = array();
	while($row = $result->fetch_assoc()) {
		$results[] = $row;
	}
	
	$result->close();
	$conn->next_result();
	
	return $results;
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
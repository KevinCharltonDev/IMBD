<?php
require 'query/error.php';
require 'connect/config.php';
require 'php/functions.php';

require 'query/account_query.php';
require 'query/add_query.php';
require 'query/look_up_query.php';
require 'query/review_query.php';
require 'query/search_query.php';
require 'query/update_listing.php';

function checkAccount($conn, $email, $password) {
	$account = verifyAccount($conn, $email, $password);
	
	if(isset($account['Error'])) {
		echo json_encode($account);
		return false;
	}
	
	if(!$account['Verified']) {
		echo json_encode(error(ACCOUNT_INVALID, "Your account information is invalid."));
		return false;
	}
	
	return $account;
}

function checkPermission($conn, $id, $account) {
	$permission = hasUpdatePermission($conn, $id, $account['Email'], $account['Type']);
	
	if(isset($permission['Error'])) {
		echo json_encode($permission);
		return false;
	}
	
	if($permission === false) {
		echo json_encode(error(NO_PERMISSION, "You do not have permission to update this."));
		return false;
	}
	
	return true;
}

function checkContactPermission($conn, $id, $account) {
	$permission = hasContactUpdatePermission($conn, $id, $account['Email'], $account['Type']);
	
	if(isset($permission['Error'])) {
		echo json_encode($permission);
		return false;
	}
	
	if($permission === false) {
		echo json_encode(error(NO_PERMISSION, "You do not have permission to update this."));
		return false;
	}
	
	return true;
}

function checkLocationPermission($conn, $id, $account) {
	$permission = hasLocationUpdatePermission($conn, $id, $account['Email'], $account['Type']);
	
	if(isset($permission['Error'])) {
		echo json_encode($permission);
		return false;
	}
	
	if($permission === false) {
		echo json_encode(error(NO_PERMISSION, "You do not have permission to update this."));
		return false;
	}
	
	return true;
}

function accountQuery($conn, $query) {
	if($query === "verify_account") {
		if(!isPostSet("email", "password")) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		echo json_encode(verifyAccount($conn, $_POST['email'], $_POST['password']));
	}
	else if($query === "create_account") {
		if(!isPostSet("email", "password", "screenname")) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$email = $_POST['email'];
		$password = $_POST['password'];
		$screenname = $_POST['screenname'];
		
		echo json_encode(createAccount($conn, $screenname, $email, $password));
	}
	else {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
	}
}

function searchQuery($conn, $query) {
	if($query === "search") {
		if(!isPostSet('search', 'location', 'page', 'rpp')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$search = $_POST['search'];
		$location = $_POST['location'];
		$page = (int) $_POST['page'];
		if($page < 1)
			$page = 1;
		
		$resultsPerPage = (int) $_POST['rpp'];
		if($resultsPerPage < 1)
			$resultsPerPage = 5;
		
		echo json_encode(search($conn, $search, $location, $page, $resultsPerPage));
	}
	else if($query === "look_up") {
		if(!isPostSet('id')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$id = (int) $_POST['id'];
		echo json_encode(lookUp($conn, $id));
	}
	else {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
		return;
	}
}

function updateQuery($conn, $query) {
	if(!isPostSet('email', 'password')) {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
		return;
	}
	
	$account = checkAccount($conn, $_POST['email'], $_POST['password']);
	
	if($account === false) {
		return;
	}
	
	if($query === "update") {
		if(!isPostSet('id', 'name', 'type', 'description', 'websites')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$id = (int) $_POST['id'];
		$name = $_POST['name'];
		$type = (int) $_POST['type'];
		$description = $_POST['description'];
		$websites = separate($_POST['websites'], ";");
		
		$permission = checkPermission($conn, $id, $account);
		
		if($permission === true) {
			echo json_encode(update($conn, $id, $name, $type, $description, $websites));
		}
	}
	else if($query === "update_contact") {
		if(!isPostSet('cid', 'first', 'last', 'contactemail', 'job', 'phone', 'extension')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$id = (int) $_POST['cid'];
		$first = $_POST['first'];
		$last = $_POST['last'];
		$contactemail = $_POST['contactemail'];
		$job = $_POST['job'];
		$phone = $_POST['phone'];
		$extension = $_POST['extension'];
		
		$permission = checkContactPermission($conn, $id, $account);
		
		if($permission === true) {
			echo json_encode(updateContact($conn, $first, $last, $contactemail, $job, $phone, $extension, $id));
		}
	}
	else if($query === "update_location") {
		if(!isPostSet('lid', 'address1', 'address2', 'city', 'state', 'zip')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$id = (int) $_POST['lid'];
		$address1 = $_POST['address1'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		
		$permission = checkLocationPermission($conn, $id, $account);
		
		if($permission === true) {
			echo json_encode(updateLocation($conn, $address1, $address2, $city, $state, $zip, $id));
		}
	}
	else {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
	}
}

function addQuery($conn, $query) {
	if(!isPostSet('email', 'password')) {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
		return;
	}
	
	$account = checkAccount($conn, $_POST['email'], $_POST['password']);
	
	if($account === false) {
		return;
	}
	
	if($query === "add") {
		if(!isPostSet('name', 'type', 'description', 'websites')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$name = $_POST['name'];
		$type = (int) $_POST['type'];
		$description = $_POST['description'];
		$websites = separate($_POST['websites'], ";");
		echo json_encode(add($conn, $name, $type, $description, $websites, $account['Email']));
	}
	else if($query === "add_location") {
		if(!isPostSet('id', 'address1', 'address2', 'city', 'state', 'zip')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$id = (int) $_POST['id'];
		$address1 = $_POST['address1'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		
		$permission = checkPermission($conn, $id, $account);
		
		if($permission === true) {
			echo json_encode(addLocation($conn, $address1, $address2, $city, $state, $zip, $id));
		}
	}
	else if($query === "add_contact") {
		if(!isPostSet('id', 'first', 'last', 'contactemail', 'job', 'phone', 'extension')) {
			echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
			return;
		}
		
		$id = (int) $_POST['id'];
		$first = $_POST['first'];
		$last = $_POST['last'];
		$contactemail = $_POST['contactemail'];
		$job = $_POST['job'];
		$phone = $_POST['phone'];
		$extension = $_POST['extension'];
		
		$permission = checkPermission($conn, $id, $account);
		
		if($permission === true) {
			echo json_encode(addContact($conn, $first, $last, $contactemail, $job, $phone, $extension, $id));
		}
	}
	else {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
	}
}

if(isPostSet('query')) {
	error_reporting(E_ALL & ~E_WARNING);
	$query = $_POST['query'];
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	
	if ($conn->connect_error) {
		echo json_encode(error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE));
		return;
	}
	
	if($query === "verify_account" or $query === "create_account") {
		accountQuery($conn, $query);
	}
	else if($query === "search" or $query === "look_up") {
		searchQuery($conn, $query);
	}
	else if(startsWith($query, "update")) {
		updateQuery($conn, $query);
	}
	else if(startsWith($query, "add")) {
		addQuery($conn, $query);
	}
	else {
		echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
	}
	
	$conn->close();
}
else {
	echo json_encode(error(INVALID_PARAMETERS, INVALID_PARAMETERS_MESSAGE));
}
?>
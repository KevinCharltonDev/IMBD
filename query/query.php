<?php
require_once 'error.php';

function isPostSet() {
	$set = true;
	foreach(func_get_args() as $arg) {
		$set = $set && isset($_POST[$arg]);
	}
	
	return $set;
}

if(isPostSet('query')) {
	$query = $_POST['query'];
	$account = null;
	
	// If email and password are sent, the account must be verified
	if(isPostSet('email', 'password')) {
		require_once 'verify_account.php';
		$account = verifyAccount($_POST['email'], $_POST['password'], true);
		if(isset($account["Error"]))
			echo json_encode($account);
	}
	
	// Handle verify_account first
	if($query === "verify_account") {
		if(!is_null($account))
			echo json_encode($account);
		
		return;
	}
	
	// If an email and password are sent and the account is not valid
	if(!is_null($account) and !$account['Verified']) {
		echo json_encode(getErrorArray(7));
		return;
	}
	
	if($query === "search") {
		if(isPostSet('s', 'page', 'rpp')) {
			require_once 'search_query.php';
			
			$search = $_POST['s'];
			$page = (int) $_POST['page'];
			if($page < 1)
				$page = 1;
			
			$resultsPerPage = (int) $_POST['rpp'];
			if($resultsPerPage < 1)
				$resultsPerPage = 5;
			
			echo json_encode(search($search, $page, $resultsPerPage, true));
		}
	}
	else if($query === "look_up") {
		if(isPostSet('id')) {
			require_once 'look_up_query.php';
			
			$id = (int) $_POST['id'];
			echo json_encode(lookUp($id, true));
		}
	}
	else if($query === "update") {
		if(isPostSet('id', 'name', 'type', 'description', 'email', 'password')) {
			
			require_once 'update_listing.php';
			
			$id = (int) $_POST['id'];
			$name = $_POST['name'];
			$type = (int) $_POST['type'];
			$description = $_POST['description'];
			
			$permission = hasUpdatePermission($id, $account['Email'], $account['Type'], true);
			if(is_array($permission)) {
				echo json_encode($permission);
			}
			else if($permission) {
				echo json_encode(updateListing($id, $name, $type, $description, null, true));
			}
			else {
				echo json_encode(getErrorArray(6));
			}
		}
	}
}
?>
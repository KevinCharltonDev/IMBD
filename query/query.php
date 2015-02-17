<?php
if(isset($_POST['query'])) {
	$query = $_POST['query'];
	$account = null;
	
	if(isset($_POST['email']) and isset($_POST['password'])) {
		require_once 'verify_account.php';
		$account = verifyAccount($_POST['email'], $_POST['password'], true);
		
		if(!$account['Verified']) {
			echo json_encode(getErrorArray(7));
			return;
		}
	}
	
	if($query === "search") {
		if(isset($_POST['s']) and isset($_POST['page']) and isset($_POST['rpp'])) {
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
		if(isset($_POST['id'])) {
			require_once 'look_up_query.php';
			
			$id = (int) $_POST['id'];
			echo json_encode(lookUp($id, true));
		}
	}
	else if($query === "update") {
		if(isset($_POST['id']) and isset($_POST['name']) and isset($_POST['type'])
			and isset($_POST['description']) and !is_null($account)) {
			
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
	else if($query === "verify_account") {
		echo json_encode($account);
	}
}
?>
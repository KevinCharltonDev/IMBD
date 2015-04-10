<?php
session_start();

require 'query/update_listing.php';
require 'query/service_query.php';
require 'php/functions.php';
require 'php/data.php';
require 'connect/config.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

// An ID is needed to view this page so redirect to home page if not set
if(!isset($_GET['id'])) {
	redirect();
	exit;
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$sp_id = (int) $_GET['id'];

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $sp_id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$sp_id}");
	exit;
}

$serviceData = getServiceData($conn, $sp_id);
$serviceMetadata = getAllServiceMetadata($conn);

if(isPostSet('service')) {
	$serviceName = $_POST['service'];
	if(isset($serviceMetadata[$serviceName])) {
		foreach($serviceMetadata[$serviceName]['Columns'] as $columnName => $columnData) {
			$formattedColumnName = str_replace(" ", "_", $columnName);
			$type = (int) $columnData['Type'];
			$value = filter(isPostSet($formattedColumnName) ? $_POST[$formattedColumnName] : '');
			
			if($type === 0) {
				$value = isPostSet($formattedColumnName) ? '1' : '0';
			}
			
			if((is_string($value) && $value === '') && ($type === 3 || $type === 4)) {
				$value = '-1';
			}
			
			if(is_array($value)) {
				$value = implode(',', $value);
			}
			
			setServiceValue($conn, $serviceName, $columnName, $value, $sp_id);
		}
		
		setMessage("Service information has been updated.", false);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Update Services</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php';?>
<section>
<?php
if(isset($_SESSION['Error'])) {
	printError($_SESSION['Error']['Message']);
	unsetResult();
}
if(isset($_SESSION['Success'])) {
	printMessage($_SESSION['Success']['Message']);
	unsetResult();
}
?>
<h2>Update Services</h2>
<div class="content">
<p><a href='listing.php?id=<?php echo $sp_id; ?>'>Back</a></p>
<?php
$n = 0;
$serviceCount = count($serviceData);
foreach($serviceData as $serviceName => $service) {
	echo "<form action=\"updateservice.php?id={$sp_id}\" method=\"POST\" id=\"service{$n}\">\n";
	echo "<h3>" . htmlspecialchars($serviceName) . "</h3>\n";
	$mainDiv = HTMLTag::create("div");
	
	foreach($service as $columnName => $columnValue) {
		if($columnName === 'Sp_Id') {
			continue;
		}
		
		$type = $serviceMetadata[$serviceName]['Columns'][$columnName]['Type'];
		$possibleValuesKey = $serviceMetadata[$serviceName]['Columns'][$columnName]['PossibleValuesKey'];
		$possibleValues = is_null($possibleValuesKey) ? array() : getPossibleValues($conn, $possibleValuesKey);
		
		$nameDiv = HTMLTag::create("div");
		$nameDiv->attribute("style", "margin-top: 10px;");
		$nameDiv->innerHTML(htmlspecialchars($columnName));
		
		$inputDiv = HTMLTag::create("div");
		$inputDiv->attribute("style", "margin-bottom: 20px;margin-left: 10px;");
		$input = null;
		
		switch($type) {
			case 0:
				$input = HTMLTag::create("input", true, true)->
					attribute("type", "checkbox")->
					attribute("name", htmlspecialchars($columnName));
				
				($columnValue == 1) ? $input->attribute("checked", "checked") : null;
				break;
			case 1:
			case 3:
			case 4:
				$input = HTMLTag::create("input", true, true)->
					attribute("type", "text")->
					attribute("name", htmlspecialchars($columnName))->
					attribute("value", htmlspecialchars($columnValue));
				break;
			case 2:
				$input = HTMLTag::create("textarea")->
					attribute("name", $columnName)->
					innerHTML($columnValue);
				break;
			case 5:
			case 6:
				$input = new HTMLDropDown(htmlspecialchars($columnName));
				$input->selectedValue($columnValue);
				foreach($possibleValues as $value) {
					$input->option(htmlspecialchars($value), htmlspecialchars($value));
				}
				break;
			case 7:
				$separateValues = separate($columnValue, ",");
				$input = HTMLTag::create("div");
				
				$leftDiv = HTMLTag::create("div")->attribute("style", "width: 200px;float: left;");
				$middleDiv = HTMLTag::create("div")->attribute("style", "width: 200px;float: left;");
				$rightDiv = HTMLTag::create("div")->attribute("style", "float: left;");
				$extraDiv = HTMLTag::create("div")->attribute("style", "clear: both;");
				$count = 0;
				
				foreach($possibleValues as $value) {
					$checkbox = HTMLTag::create("input", true, true)->
						attribute("type", "checkbox")->
						attribute("name", htmlspecialchars($columnName) . "[]")->
						attribute("value", htmlspecialchars($value));
						
					if(in_array($value, $separateValues)) {
						$checkbox->attribute("checked", "checked");
					}
					
					if($count % 3 === 0) {
						$leftDiv->innerHTML('<div>', $checkbox->html(), htmlspecialchars($value), '</div>');
					}
					else if($count % 3 === 1) {
						$middleDiv->innerHTML('<div>', $checkbox->html(), htmlspecialchars($value), '</div>');
					}
					else {
						$rightDiv->innerHTML('<div>', $checkbox->html(), htmlspecialchars($value), '</div>');
					}
					
					$count++;
				}
				
				$input->innerHTML($leftDiv->html(), $middleDiv->html(), $rightDiv->html(), $extraDiv->html());
				break;
			case 8:
				$separateValues = separate($columnValue, ",");
				$otherValue = "";
				
				foreach($separateValues as $value) {
					if(!in_array($value, $possibleValues)) {
						$otherValue = $value;
					}
				}
				
				$input = HTMLTag::create("div");
				
				$leftDiv = HTMLTag::create("div")->attribute("style", "width: 200px;float: left;");
				$middleDiv = HTMLTag::create("div")->attribute("style", "width: 200px;float: left;");
				$rightDiv = HTMLTag::create("div")->attribute("style", "float: left;");
				$extraDiv = HTMLTag::create("div")->attribute("style", "clear: both;");
				$count = 0;
				
				foreach($possibleValues as $value) {
					$checkbox = HTMLTag::create("input", true, true)->
						attribute("type", "checkbox")->
						attribute("name", htmlspecialchars($columnName) . "[]")->
						attribute("value", htmlspecialchars($value));
						
					if(in_array($value, $separateValues)) {
						$checkbox->attribute("checked", "checked");
					}
					
					if($count % 3 === 0) {
						$leftDiv->innerHTML('<div>', $checkbox->html(), htmlspecialchars($value), '</div>');
					}
					else if($count % 3 === 1) {
						$middleDiv->innerHTML('<div>', $checkbox->html(), htmlspecialchars($value), '</div>');
					}
					else {
						$rightDiv->innerHTML('<div>', $checkbox->html(), htmlspecialchars($value), '</div>');
					}
					
					$count++;
				}
				
				$otherInput = HTMLTag::create("input", true, true)->
					attribute("type", "text")->
					attribute("name", htmlspecialchars($columnName) . "[]")->
					attribute("value", htmlspecialchars($otherValue));
				
				$extraDiv->innerHTML("Other<br>" . $otherInput->html());
				$input->innerHTML($leftDiv->html(), $middleDiv->html(), $rightDiv->html(), $extraDiv->html());
				break;
		}
		$inputDiv->innerHTML($input->html());
		$mainDiv->innerHTML($nameDiv->html(), $inputDiv->html());
	}
	
	echo $mainDiv->html();
	
	$prev = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Previous")->
		attribute("onclick", "showPrev('service', {$n});");
	if($n == 0)
		$prev->attribute("disabled", "disabled");
	
	echo $prev->html();
		
	$next = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Next")->
		attribute("onclick", "showNext('service', {$n});");
	if($n == $serviceCount - 1)
		$next->attribute("disabled", "disabled");
	
	echo $next->html();
	
	echo "<input type='hidden' name='service' value='{$serviceName}'>\n";
	echo "<hr>\n";
	echo "<input type='submit' value='Submit'>\n";
	echo "</form>\n";
	$n++;
}
	
$conn->close();
?>
<script type="text/javascript">
toggleMultipleDisplay("service", 1, <?php echo ($serviceCount - 1); ?>);
</script>
</div>
</section>
</body>
</html>
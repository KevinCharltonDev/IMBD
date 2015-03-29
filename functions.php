<?php
function isPostSet() {
	$set = true;
	foreach(func_get_args() as $arg) {
		$set = $set && isset($_POST[$arg]);
	}
	
	return $set;
}

function startsWith($string, $value) {
	return $value === "" or strrpos($string, $value, -strlen($string)) !== FALSE;
}

function redirect($page = '') {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/$page");
}

function setResult($result) {
	if(isset($result['Error'])) {
		$_SESSION['Error'] = $result;
	}
	else if(isset($result['Success'])) {
		$_SESSION['Success'] = $result;
	}
}

function setMessage($message, $isError) {
	if($isError === true) {
		$_SESSION['Error'] = array('Error' => true, 'Message' => $message);
	}
	else {
		$_SESSION['Success'] = array('Success' => true, 'Message' => $message);
	}
}

function unsetResult() {
	unset($_SESSION['Error']);
	unset($_SESSION['Success']);
}

function spTypeToString($type) {
	if($type === 0) {
		return "Individual";
	}
	else if($type === 1) {
		return "Group";
	}
	else if($type === 2) {
		return "Business";
	}
	else if($type === 3) {
		return "Organization";
	}
	else {
		return "";
	}
}

function separate($string, $char) {
	$array = explode($char, $string);
		
	// Trim all strings
	for($i = 0; $i < count($array); $i++) {
		$array[$i] = trim($array[$i]);
	}
	
	// Empty strings convert to boolean false so are filtered out
	$array = array_filter($array);
	
	return $array;
}

function websitesFromString($websitesString) {
	return separate($websitesString, "\n");
}

function statesDropDown($selectedValue) {
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$sql = "SELECT * FROM STATE";
	$results = $conn->query($sql);
	
	$stateDropDown = new HTMLDropDown("state");
	$stateDropDown->selectedValue($selectedValue);
	
	while($row = $results->fetch_assoc()) {
		$stateDropDown->option($row['Name'], $row['Abbreviation']);
	}
	
	$results->close();
	$conn->close();
	return $stateDropDown->html();
}

function printError($message, $link = null) {
	echo "<div class='error'>\n";
	echo htmlspecialchars($message);
	if(!is_null($link)) {
		echo "<br/><a href='$link'>Click here to continue.</a>";
	}
	echo "</div>\n";
}

function printMessage($message, $link = null) {
	echo "<div class='message'>\n";
	echo htmlspecialchars($message);
	if(!is_null($link)) {
		echo "<br/><a href='$link'>Click here to continue.</a>";
	}
	echo "</div>\n";
}

function printContact($contact) {
	$c_id = (int) $contact["C_Id"];
	$first = htmlspecialchars($contact["First"]);
	$last = htmlspecialchars($contact["Last"]);
	$email = htmlspecialchars($contact["Email"]);
	$job = htmlspecialchars($contact["Job"]);
	$phone = htmlspecialchars(formatPhoneNumber($contact["Phone"], $contact["Extension"]));
	
	echo "<div>\n";
	printNotEmpty($first . ' ' . $last);
	printNotEmpty($job);
	printNotEmpty($phone);
	printNotEmpty($email);
	echo "</div>\n";
}

function printLocation($location) {
	$address1 = htmlspecialchars($location["Address1"]);
	$address2 = htmlspecialchars($location["Address2"]);
	$city = htmlspecialchars($location["City"]);
	$state = htmlspecialchars($location["State"]);
	$zip = htmlspecialchars($location["Zip"]);
	
	echo "<div>\n";
	printNotEmpty($address1);
	printNotEmpty($address2);
	printNotEmpty($city . ', ' . $state . ' ' . $zip);
	
	$contacts = $location["Contacts"];
	if(count($contacts) > 0) {
		echo "<h4>Contacts for this location</h4>\n";
	}
	
	foreach($contacts as $contact) {
		echo $contact["Name"];
		echo "<br/>\n";
	}
	
	echo "</div>\n";
}

function printReview($review) {
	$comment = htmlspecialchars($review["Comment"]);
	$rating = htmlspecialchars($review["Rating"]);
	$date = htmlspecialchars($review["Date"]);
	$name = htmlspecialchars($review["Name"]);
	
	static $count = 0;
	$count++;
	
	echo "<div class='review'>";
	echo "<h4 onmousedown='toggleDisplay(\"review{$count}\")'>{$name} - {$date}</h4>\n";
	echo "<div id='review{$count}'>\n<hr>";
	echo "<noscript>{$rating} / 5</noscript>\n";
	echo "<script type='text/javascript'>\n";
	echo "var stars = new Stars(\"star{$count}\", 5, {$rating}, false);\n";
	echo "stars.printStars();\n";
	echo "</script>\n";
	echo "<br>\n";
	echo "<p>{$comment}</p>\n";
	echo "<input type='hidden' name='report' value='{$name}'>\n";
	echo "<input type='submit' value='Report review.'>\n";
	echo "</div>\n";
	echo "</div>\n";
}

function printMyReview($review) {
	$comment = htmlspecialchars($review["Comment"]);
	$rating = htmlspecialchars($review["Rating"]);
	$date = htmlspecialchars($review["Date"]);
	$name = htmlspecialchars($review["Name"]);
	
	echo "<div class='review'>";
	echo "<h4 onmousedown='toggleDisplay(\"myreview\")'>{$name} - {$date}</h4>\n";
	echo "<div id='myreview'>\n<hr>";
	echo "<noscript>{$rating} / 5</noscript>\n";
	echo "<script type='text/javascript'>\n";
	echo "var stars = new Stars(\"mystars\", 5, {$rating}, false);\n";
	echo "stars.printStars();\n";
	echo "</script>\n";
	echo "<br>\n";
	echo "<p>{$comment}</p>\n";
	echo "<input type='hidden' name='delete' value='review'>\n";
	echo "<input type='submit' value='Delete my review.'>\n";
	echo "</div>\n";
	echo "</div>\n";
}

function printNotEmpty($s, $lineBreak = true) {
	if(trim($s) != '') {
		echo $s;
		if($lineBreak)
			echo "<br>\n";
	}
}

function formatPhoneNumber($phone, $extension = '') {
	$formatted = '';
	
	if(strlen($phone) == 7) {
		$formatted = substr($phone, 0, 3) . '-' . substr($phone, 3);
	}
	else if(strlen($phone) == 10) {
		$formatted =  '(' . substr($phone, 0, 3) . ') ' . formatPhoneNumber(substr($phone, 3));
	}
	else if(strlen($phone) == 11) {
		$formatted = substr($phone, 0, 1) . '-' . substr($phone, 1, 3) . '-' . formatPhoneNumber(substr($phone, 4));
	}
	else {
		$formatted = $phone;
	}
	
	if(!empty($extension)) {
		$formatted .= ', ext. ' . $extension;
	}
	
	return $formatted;
}

function businessForm($name='', $type=2, $description='', $websites=array()) {
	$nameInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "name")->
		attribute("maxlength", "60")->
		attribute("value", htmlspecialchars($name));
		
	$typeDropDown = new HTMLDropDown("type");
	$typeDropDown->
		selectedValue($type)->
		option("Individual", 0)->
		option("Group", 1)->
		option("Business", 2)->
		option("Organization", 3);
		
	$descriptionTextArea = HTMLTag::create("textarea")->
		attribute("name", "description")->
		attribute("cols", "75")->
		attribute("rows", "6")->
		attribute("maxlength", "255")->
		innerHTML(htmlspecialchars($description));
		
	$websiteString = "";
	foreach($websites as $website) {
		// &#13;&#10; is a line feed followed by a carriage return in html for the textarea
		$websiteString .= htmlspecialchars($website) . '&#13;&#10;';
	}
	
	$websiteTextArea = HTMLTag::create("textarea")->
		attribute("name", "websites")->
		attribute("cols", "75")->
		attribute("rows", "6")->
		attribute("maxlength", "2000")->
		innerHTML($websiteString);
		
	$table = new HTMLTable();
		
	$table->
		cell("Name: ")->
		cell($nameInput->html())->
		nextRow()->
		cell("Type: ")->
		cell($typeDropDown->html())->
		nextRow()->
		cell("Description: ")->
		cell($descriptionTextArea->html())->
		nextRow()->
		cell("Websites: ")->
		cell($websiteTextArea->html());

	echo $table->html();
}

function locationForm($address1='', $address2='', $city='', $state='IN', $zip='') {
	$address1TextArea = HTMLTag::create("textarea")->
		attribute("name", "address1")->
		attribute("maxlength", "60")->
		attribute("placeholder", "This box is required to add a location.")->
		innerHTML(htmlspecialchars($address1));
		
	$address2TextArea = HTMLTag::create("textarea")->
		attribute("name", "address2")->
		attribute("maxlength", "60")->
		innerHTML(htmlspecialchars($address2));
		
	$cityInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "city")->
		attribute("maxlength", "30")->
		attribute("value", htmlspecialchars($city));
		
	$zipInput = HTMLTag::create("input", true, true)->
		attribute("name", "zip")->
		attribute("maxlength", "5")->
		attribute("value", htmlspecialchars($zip));
		
	$table = new HTMLTable();
		
	$table->
		cell("Address 1: ")->
		cell($address1TextArea->html())->
		nextRow()->
		cell("Address 2: ")->
		cell($address2TextArea->html())->
		nextRow()->
		cell("City: ")->
		cell($cityInput->html())->
		nextRow()->
		cell("State: ")->
		cell(statesDropDown($state))->
		nextRow()->
		cell("Zip code: ")->
		cell($zipInput->html())->
		nextRow();

	echo $table->html();
}

function contactForm($first='', $last='', $email='', $job='', $phone='', $extension='') {
	$fnameTextArea = HTMLTag::create("textarea")->
		attribute("name", "first")->
		attribute("maxlength", "25")->
		attribute("placeholder", "This box is required to add a contact.")->
		innerHTML(htmlspecialchars($first));
		
	$lnameTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "last")->
		attribute("maxlength", "40")->
		attribute("value", htmlspecialchars($last));
		
	$emailTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "email")->
		attribute("maxlength", "60")->
		attribute("value", htmlspecialchars($email));
		
	$jobTitleTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "job")->
		attribute("maxlength", "30")->
		attribute("value", htmlspecialchars($job));
		
	$phoneInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "phone")->
		attribute("maxlength", "11")->
		attribute("value", htmlspecialchars($phone));
		
	$extensionInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "extension")->
		attribute("maxlength", "4")->
		attribute("value", htmlspecialchars($extension));
		
	$table = new HTMLTable();
		
	$table->
		cell("First Name: ")->
		cell($fnameTextArea->html())->
		nextRow()->
		cell("Last Name: ")->
		cell($lnameTextArea->html())->
		nextRow()->
		cell("Email: ")->
		cell($emailTextArea->html())->
		nextRow()->
		cell("Job title: ")->
		cell($jobTitleTextArea->html())->
		nextRow()->
		cell("Phone Number: ")->
		cell($phoneInput->html())->
		nextRow()->
		cell("Extension: ")->
		cell($extensionInput->html())->
		nextRow();

	echo $table->html();
}

class HTMLTag {
	private $tag = '';
	private $attributes = array();
	private $innerHTML = '';
	private $emptyElement = false;
	private $inline = false;
	
	function __construct($tag, $emptyElement = false, $inline = false) {
		$this->tag = $tag;
		$this->emptyElement = $emptyElement;
		$this->inline = $inline;
	}
	
	static function create($tag, $emptyElement = false, $inline = false) {
		$htmlTag = new HTMLTag($tag, $emptyElement, $inline);
		return $htmlTag;
	}
	
	function innerHTML() {
		$this->innerHTML .= implode("", func_get_args());
		return $this;
	}
	
	function attribute($name, $value) {
		$this->attributes[$name] = $value;
		return $this;
	}
	
	function html() {
		$quote = '"';
		$newline = $this->inline ? "" : "\n";
		$equals = '=';
		
		$html = "<{$this->tag}";
		
		foreach($this->attributes as $name => $value) {
			$html .= ' ' .$name . $equals . $quote . $value . $quote;
		}
		
		// An empty element does not have a closing tag.
		if($this->emptyElement) {
			$html .= "/>" . $newline;
			return $html;
		}
		
		$html .= ">" . $newline;
		$html .= $this->innerHTML;
		$html .= "</{$this->tag}>\n";
		
		return $html;
	}
}

class HTMLTable {
	private $contents = array();
	private $classAttribute = '';
	
	private $currentRow = 0;
	
	function __construct() {
		array_push($this->contents, array());
	}
	
	function cell($html) {
		array_push($this->contents[$this->currentRow], $html);
		return $this;
	}
	
	function nextRow() {
		array_push($this->contents, array());
		$this->currentRow++;
		return $this;
	}
	
	function setClass($class) {
		$this->classAttribute = $class;
		return $this;
	}
	
	function html() {
		$table = new HTMLTag("table");
		if(!empty($this->classAttribute))
			$table->attribute("class", $this->classAttribute);
		
		foreach($this->contents as $row) {
			$rowTag = new HTMLTag("tr");
			foreach($row as $column) {
				$rowTag->innerHTML(HTMLTag::create("td", false, true)->innerHTML($column)->html());
			}
			$table->innerHTML($rowTag->html());
		}
		
		return $table->html();
	}
}

class HTMLDropDown {
	private $name = '';
	private $options = array();
	private $selectedValue = '';
	
	function __construct($name) {
		$this->name = $name;
	}
	
	function option($text, $value) {
		$this->options[$text] = $value;
		return $this;
	}
	
	function selectedValue($value) {
		$this->selectedValue = $value;
		return $this;
	}
	
	function html() {
		$selectTag = new HTMLTag("select");
		$selectTag->attribute("name", $this->name);
		foreach($this->options as $text => $value) {
			$optionTag = new HTMLTag("option");
			$optionTag->attribute("value", $value)->innerHTML($text);
			
			if($value === $this->selectedValue)
				$optionTag->attribute("selected", "selected");
			
			$selectTag->innerHTML($optionTag->html());
		}
		
		return $selectTag->html();
	}
}
?>
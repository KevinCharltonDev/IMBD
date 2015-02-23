<?php
function redirect($page = '') {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/$page");
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

function websitesFromString($websitesString) {
	// Separate into array by delimiter \n
	$websites = explode("\n", $websitesString);
	
	// Trim all websites
	for($i = 0; $i < count($websites); $i++) {
		$websites[$i] = trim($websites[$i]);
	}
	
	// Empty strings convert to boolean false so are filtered out
	$websites = array_filter($websites);
	
	return $websites;
}

function printContact($contact) {
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
		echo "<h4>Contacts</h4>\n";
	}
	
	foreach($contacts as $contact) {
		printContact($contact);
		echo "<br>\n";
	}
	
	echo "</div>\n";
}

function printReview($review) {
	$comment = htmlspecialchars($review["Comment"]);
	$rating = htmlspecialchars($review["Rating"]);
	$date = htmlspecialchars($review["Date"]);
	$name = htmlspecialchars($review["Name"]);
	
	echo "<div>";
	echo "Comment: {$comment}<br>\n";
	echo "Rating: {$rating}<br>\n";
	echo "Date: {$date}<br>\n";
	echo "Review By: {$name}<br><br>\n";
	echo "</div>";
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
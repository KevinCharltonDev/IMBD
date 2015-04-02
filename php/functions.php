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

function printError($message, $link = null) {
	$messageFormatted = htmlspecialchars($message);
echo <<<HTML

<div class="error">
{$messageFormatted}
<img src="images/error_close1.png" onload="closeEvents(this, 'error');">
</div>
HTML;
}

function printMessage($message, $link = null) {
	$messageFormatted = htmlspecialchars($message);
echo <<<HTML

<div class="message">
{$messageFormatted}
<img src="images/message_close1.png" onload="closeEvents(this, 'message');">
</div>
HTML;
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
/**
 * This function changes the background of an input field if its value is invalid.
 * @param {string} id : The id of the input field
 * @param {function:string->boolean} f : A validation function
 * @return {boolean} : true if valid input
 */
function validateInput(id, f) {
	var input = document.getElementById(id);
	if (f(input.value)) {
		return true;
	}
	
	input.style.backgroundColor = '#FF5555';
	return false;
}

/**
 * Returns a function that checks if a string is an integer and if it is between min and max.
 * @param {int} min : Minimum valid number
 * @param {int} max : Maximum valid number
 * @return {function:string->boolean} : A validation function
 */
function validateNumber(min, max) {
	return function(value) {
		var intRegex = new RegExp("^[0]$|^[1-9][0-9]*$");
		if (!intRegex.test(value)) {
			return false;
		}

		var number = parseInt(value, 10);
		return number >= min && number <= max;
	};
}

/**
 * Returns a function that checks if a string has a valid length and valid characters.
 * @param {int} : The minimum acceptable length
 * @param {int} : The maximum acceptable length
 * @param {string} : Special characters allowed in the regular expression
 * The space character should be at the beginning like this: ' !@#$%^&*-_'
 * @return {function:string->boolean} : A validation function
 */
function validateText(minLength, maxLength, specialCharacters) {
	return function(value) {
		var length = "{" + minLength + "," + maxLength + "}";
		var textRegex = new RegExp("^[a-zA-Z0-9" + specialCharacters + "]" + length + "$");
		return textRegex.test(value);
	};
}

/**
 * Returns a function that validates a string against a regular expression.
 * @param {string} : The regular expression
 * @return {function:string->boolean} : A validation function
 */
function validateByRegEx(regex) {
	return function(value) {
		return new RegExp(regex).test(value);
	};
}

function onlySendNonEmptyInput(formId) {
	var form = document.getElementById(formId);
	var inputs = form.getElementsByTagName('input');
	
	for(var i = 0; i < inputs.length; i++) {
		var input = inputs[i];
		if(input.getAttribute("name") && !input.value) {
			input.setAttribute("name", "");
		}
	}
}

function combineCheckboxValues(formId, hiddenId) {
	var form = document.getElementById(formId);
	var inputs = form.getElementsByTagName('input');
	var hidden = document.getElementById(hiddenId);
	var idArray = [];
	
	for(var i = 0; i < inputs.length; i++) {
		if(inputs[i].type === "checkbox" && inputs[i].checked === true) {
			idArray[idArray.length] = parseInt(inputs[i].value, 10);
		}
	}
	
	hidden.value = idArray.join("-");
}
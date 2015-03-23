function toggleDisplay(id){
	var input = document.getElementById(id);
	 if(input.style.display == 'none')
		input.style.display = 'block';
	else
		input.style.display = 'none';
}

function toggleMultipleDisplay(id, startIndex, lastIndex) {
	for(var i = startIndex; i <= lastIndex; i++) {
		toggleDisplay(id.concat(i));
	}
}

function showNext(id, i) {
	var fullId = id.concat(i);
	var nextId = id.concat(i + 1);
	toggleDisplay(fullId);
	toggleDisplay(nextId);
}

function showPrev(id, i) {
	showNext(id, i - 1);
}


// Constructor for stars objects
function Stars(starId, totalNumber, numberFilled, half) {
	this.starId = starId;
	this.ratingId = "";
	this.totalNumber = totalNumber;
	this.numberFilled = numberFilled;
	this.half = half;
	
	this.setStars = function(number, half) {
		for(var i = 0; i < this.totalNumber; i++) {
			var src = "";
			if(i < number)
				src = "images/full_star.png";
			else if(i == (number) && half)
				src = "images/half_star.png";
			else
				src = "images/star.png";
			
			document.getElementById(this.starId + i.toString(10)).src = src;
		}
	};
	
	this.printStars = function() {
		for(var i = 0; i < this.totalNumber; i++) {
			document.write("<img src='star.png' id='", this.starId, i, "' />");
		}
		document.writeln();
		this.setStars(this.numberFilled, this.half);
	};
	
	this.printRatingInput = function(ratingId) {
		this.ratingId = ratingId;
		var value = this.numberFilled.toString(10);
		value += (this.half) ? "+" : "";
		document.writeln("<input type='hidden' value='", value, "' name='", ratingId, "' id='", ratingId, "'/>");
	};
	
	this.attachListeners = function() {
		for(i = 0; i < this.totalNumber; i++) {
			var star = document.getElementById(this.starId + i.toString(10));
			star.onmouseover = mouseOver(this, i);
			star.onmouseout = mouseOut(this);
			star.onmousedown = click(this, i);
		}
	};
}

// User moves mouse over a star
function mouseOver(stars, number) {
	return function() {
		stars.setStars(number + 1, false);
	};
}

// User moves mouse out of a star
function mouseOut(stars) {
	return function() {
		var rating = document.getElementById(stars.ratingId);
		var number = parseInt(rating.value, 10);
		var half = (rating.value.indexOf("+") == -1) ? false : true;
		stars.setStars(stars.numberFilled, stars.half);
	}
}

// User presses mouse down on star (full click is not required)
function click(stars, number) {
	return function() {
		document.getElementById(stars.ratingId).value = number + 1;
		stars.numberFilled = number + 1;
		stars.half = false;
	};
}
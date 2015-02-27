<?php
echo "<nav class='nav'>\n";
echo "<ul>\n";
echo "<li><a href='index.php'>Home</a></li>\n";
echo "<li><a href='add.php'>Add Business</a></li>\n";
if(isset($_SESSION['Email'])) {
	echo "<li><a href='account.php'>My Account</a></li>\n";
	echo "<li><a href='logout.php'>Sign Out</a></li>\n";
}
else {
	echo "<li><a href='account.php'>Create Account</a></li>\n";
	echo "<li><a href='login.php'>Sign In</a></li>\n";
}
echo"</ul>\n";
echo "</nav>\n";
?>
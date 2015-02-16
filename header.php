<?php
echo "<nav>\n";
echo "<ul>\n";
echo "<li><a href='index.php'>Home</a></li>\n";
echo "<li><a href='index.php'>Add Business</a></li>\n";
echo "<li><a href='index.php'>Account</a></li>\n";
if(isset($_SESSION['Email'])) {
	echo "<li><a href='logout.php'>Sign Out</a></li>\n";
}
else {
	echo "<li><a href='login.php'>Sign In</a></li>\n";
}
echo"</ul>\n";
echo "</nav>\n";
?>
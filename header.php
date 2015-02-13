<?php
echo "<header>\n";
echo "<div class='left'>\n";
echo "<a href='index.php'>Home</a>\n";
echo "</div>\n";
echo "<div class='right'>\n";
if(isset($_SESSION['Email'])) {
	echo "<a href='logout.php'>Sign Out</a>\n";
}
else {
	echo "<a href='login.php'>Sign In</a>\n";
}
echo"</div>\n";
echo "</header>\n";
?>
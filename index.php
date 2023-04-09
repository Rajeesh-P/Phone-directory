<?php

require __DIR__.'/vendor/autoload.php';

use Phonebook\Directory;

$directory = new Directory;

$records = ($_SERVER['REQUEST_METHOD'] == 'GET') 
				? $directory->getRecords($_GET)
				: $directory->addRecord($_POST);

?>

<h2>Phone directory</h2>

<?php 
	if (isset($_COOKIE["formError"]) && $_COOKIE["formError"] != "") {
		echo "<div>". $_COOKIE["formError"] ."</div>";
	}
?>

<form method="post">
	<label for="first_name">First name:</label><br>
	<input type="text" id="first_name" name="first_name"><br>
	<label for="surname">Surname:</label><br>
	<input type="text" id="surname" name="surname"><br>
	<label for="phone">Phone:</label><br>
	<input type="text" id="phone" name="phone"><br><br>
	<input type="submit" value="Create">
</form>

<div>
	<h3>Search by number</h3>
	<form>
		<label for="search">Enter search number:</label><br>
		<input type="text" name="search"><br><br>
		<input type="submit" value="Search">
	</form>
</div>

<div>
	<table>
		<tr>
			<th>Sl no</th>
			<th>First name</th>
			<th>Surname</th>
			<th>Phone</th>
		</tr>
		<?php 
		foreach($records["result"] as $record) {
			echo "<tr>";
			echo "<td>" . $record['id'] . "</td>";
			echo "<td>" . $record['first_name'] . "</td>";
			echo "<td>" . $record['surname'] . "</td>";
			echo "<td>" . $record['phone'] . "</td>";
			echo "</tr>";
		}
		?>
	</table>
</div>

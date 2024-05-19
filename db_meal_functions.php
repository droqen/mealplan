<?php

require_once 'db_connect.php';
if (!$conn) { die("991; No connection"); }

function echo_prettymeals($ids) {
	global $conn;
	try {
		if ($ids == "*") {
			$res = $conn->query('SELECT id,name,ingredientlist,notes FROM meals');
		} else if (is_array($ids)) {
			$res = $conn->query('SELECT id,name,ingredientlist,notes FROM meals WHERE id in (' . implode(',', array_map('intval', $ids)) . ')');
		} else {
			die ("99A; prettymeals terminated on illegal ids $ids");
		}
		if (!$res) { die ("99B; select failed on ids $ids"); }
		$found = 0;
		$meal_to_edit_id = $_POST['showeditmeal']??null;
		foreach($res as $row) {
			if ($row[0] == $meal_to_edit_id) {
				echo_editmeal_1($row);
			} else {
				echo_prettymeal_1($row);
			}
			$found ++;
		}
	} catch (PDOException $e) {
		die("999; PDO execute (select) failed: " . $e->getMessage() . "");
	}
}

function echo_prettymeal_1($row) {
	$id = $row[0];
	$name = $row[1];
	$ingrd = $row[2];
	$notes = $row[3];
	
	echo "<div id='mealpretty' class='meal$id'>\n";
	echo "<h3>$name</h3>\n";
	echo "<form class='mealedit_enter' method='POST'><input type='hidden' name='showeditmeal' value='$id'>\n";
	echo "<input type='submit' value='Edit'></form>\n";
	echo "<br/>\n";
	$i = 0;
	foreach(preg_split("/\r\n|\n|\r/", $ingrd) as $line) {
		echo "\t<input type='checkbox' id='ingred$i'><label for='ingred$i'>$line</label><br/>\n"; // TODO save in session
		$i++;
	}
	echo "<p>$notes</p>\n";
	echo "</div>\n";
}

function echo_new_meal_button() {
	echo "<form method='POST' id='newmeal'>
<input type='hidden' name='new_meal'>
<input type='submit' value='Add New Meal'>
</form>\n";
}
function try_newmeal_from_post() {
	global $conn;
	if (isset($_POST['new_meal'])) {
		$conn->query("INSERT INTO meals(name) VALUES('New Meal')");
	}
}
function try_savemeal_from_post() {
	$saveid = $_POST['save_meal']??null;
	if ($saveid) {
		$name = $_POST['name'];
		$ingrd = $_POST['ingrd'];
		$notes = $_POST['notes'];
		savemeal($saveid, $name, $ingrd, $notes);
		return true;
	} else {
		return false;
	}
}

function savemeal($id, $name, $ingrd, $notes) {
	global $conn;
	try {
		$sth = $conn->prepare("UPDATE meals SET name=?, ingredientlist=?, notes=? WHERE id=$id");
		$res = $sth->execute([$name,$ingrd,$notes,]);
		$count = $sth->rowCount();
		if ($count == 0) {
			echo ("995; update failed (either bad id $id or no values were changed)");
		}
	}
	catch (PDOException $e) {
		die("998; PDO execute (update) failed: " . $e->getMessage() . "");
	}
}

function echo_editmeal_1($row) {
	$id = $row[0];
	$name = $row[1];
	$ingrd = $row[2];
	$notes = $row[3];
	echo "<form method='POST' id='mealeditor'>\n";
	echo "<p>Meal name</p><input type='text' id='editname' name='name' value='$name'><br/>\n";
	echo "<p>Ingredient list</p><textarea name='ingrd'>$ingrd</textarea><br/>\n";
	echo "<p>Additional notes</p><textarea name='notes'>$notes</textarea><br/>\n";
	echo "<input type='hidden' name='save_meal' value='$id'>\n";
	if (isset($_POST['browse'])) { echo "<input type='hidden' name='browse' value=1>"; }
	echo "<input type='submit' value='Save Changes'>\n";
	echo "</form>\n";
}

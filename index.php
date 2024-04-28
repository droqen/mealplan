<!DOCTYPE html>
<html>
<head>
<title>mealplan</title>
<style>
	textarea, input#editname { margin: 10px; }
	input#editname { font-weight: bold; }
	h3, form.mealedit_enter { display: inline-block; margin: 0; }
	form#mealeditor { padding: 10px; margin: 5px; background-color: antiquewhite; }
	form#mealeditor p { margin: 1em 0 -0.25em 0; font-style: italic;  }
	form#mealeditor textarea { width: 50vw; height: 4em;}
	form#newmeal { padding: 10px; margin: 5px; background-color: gold; }
	form#browse { padding: 10px; margin: 5px; background-color: lightgoldenrodyellow; }
	form#unbrowse { padding: 10px; margin: 5px; background-color: gainsboro; }
	form#nextweek { padding: 10px; margin: 5px; background-color: greenyellow; }
</style>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<body>
<?php require_once 'db_connect.php' ?>
<?php require_once 'echo_userident_form.php' ?>
<?php require_once 'db_meal_functions.php' ?>
<?php
	try_newmeal_from_post();
	try_savemeal_from_post();

	if (isset($_GET['browse'])) {
		echo "<h2>Browsing all meals in the database</h2>\n";
		echo_prettymeals("*");
		echo_new_meal_button();
		echo "<form id='unbrowse' method='POST' action='/'>
<input type='submit' value='Back to Current Week'>
</form>\n";
	} else {
		if (isset($_GET['shuffle'])) {
			$meal_ids = array();
			$meals_result = $conn->query('SELECT id FROM meals WHERE enabled = 1');
			foreach($meals_result as $meal_row) {
				$meal_ids[] = $meal_row[0];
			}
			$too_recent_meal_ids = array();
			$last4weeks_result = $conn->query('SELECT meal1,meal2 FROM weeks ORDER BY startofweek DESC LIMIT 4');
			foreach($last4weeks_result as $last4weeks_row) {
				$meal1 = $last4weeks_row[0];
				$meal2 = $last4weeks_row[1];
				if ($meal1 > 0) { $too_recent_meal_ids[] = $meal1; }
				if ($meal2 > 0) { $too_recent_meal_ids[] = $meal2; }
			}
			$good_meal_ids = array_diff($meal_ids, $too_recent_meal_ids);
			$meal1 = 0; $meal2 = 0;
			shuffle($good_meal_ids);
			$meal1 = $good_meal_ids[0];
			$meal2 = $good_meal_ids[1];
			$date = $_POST['shuffle_start_date'];
			$q = "INSERT INTO weeks(meal1,meal2,startofweek) VALUES($meal1,$meal2,'$date')";
			$res = $conn->query($q);
			if ($res) {
				echo "<br/>GENERATED A NEW WEEK<br/>using query $q<br/>\n";
			} else {
				echo "<br/>WEEK GENERATION FAILED<br/>using query $q<br/>\n";
			}
		}
		$res = $conn->query('SELECT meal1,meal2,startofweek FROM weeks ORDER BY startofweek DESC LIMIT 1');
		$meals = [];
		foreach($res as $row) {
			$meal1 = $row[0];
			$meal2 = $row[1];
			$dateString = $row[2];
			echo "\n\n<span id='phpDate' style='display:none;'>$dateString</span>\n";
			// if ($meal1) { echo "<span id='phpMeal1' style='display:none;'>$meal1</span>\n"; }
			// if ($meal2) { echo "<span id='phpMeal2' style='display:none;'>$meal2</span>\n"; }
			if ($meal1) { $meals[] = $meal1; }
			if ($meal2) { $meals[] = $meal2; }
			echo "\n";
		}
		echo "<h2>Meals for <span id='current_period'></span></h2>\n";
		echo_prettymeals($meals);
		echo "<form id='browse' method='POST' action='?browse'>
<input type='submit' value='Browse All Meals'>
</form>\n";
		echo "<form id='nextweek' method='POST' action='?shuffle'>
<input id='nextweek_submit' type='submit' value='Generate Meals for Next Week!'>
<input id='nextweek_date' type='hidden' name='shuffle_start_date'>
</form>\n";
	}
?>

</body>
<script>
	const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	function to_yyyymmdd(d){return `${d.getFullYear()}-${("0"+(d.getMonth()+1)).slice(-2)}-${("0"+d.getDate()).slice(-2)}`;}
	function to_ymd_nice(d){return `${MONTHS[d.getMonth()]} ${("0"+d.getDate()).slice(-2)}`;}
	let now = new Date();
	console.log(document.getElementById("phpDate").innerText);
	let weekstart = new Date(document.getElementById("phpDate").innerText);
	weekstart.setDate(weekstart.getDate()+1);
	let weekstartplus14 = new Date(weekstart.getTime());
	weekstartplus14.setDate(weekstart.getDate()+14);
	document.getElementById("current_period").innerText = `${to_ymd_nice(weekstart)} to ${to_ymd_nice(weekstartplus14)}`;
	document.getElementById("nextweek_submit").value = `Generate Meals for ${to_ymd_nice(now)} ->`;
	document.getElementById("nextweek_date").value = `${to_yyyymmdd(now)}`;
	if (to_yyyymmdd(now)==to_yyyymmdd(weekstart)) {
		document.getElementById("nextweek_submit").setAttribute("disabled", true);	
	}
</script>

</html>
<?php
$TBL_PREFIX = getenv('FRIENDSWIPE_TBL_PREFIX');

// Connect to the MySQL database.
$MYSQLI = new mysqli(
	getenv('FRIENDSWIPE_HOST'), getenv('FRIENDSWIPE_USER'),
	getenv('FRIENDSWIPE_PASS'), getenv('FRIENDSWIPE_DB')
);

if (mysqli_connect_errno())
{
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

if (array_key_exists('swipes', $_GET))
{
	if (!array_key_exists('sender', $_GET))
	{
		exit('Error: Sender ID not provided');
	}
	else if (!is_int($_GET['sender']))
	{
		exit('Error: Sender ID must be an integer.');
	}
	else
	{
		$sender = $_GET['sender'];

		$swipes = array();
		$query = 'SELECT `choice`, `sender`, `recipient`, `time`
			FROM `' . $TBL_PREFIX . 'swipes`
			WHERE `sender` = ?;';
		$stmt = $MYSQLI->prepare($query);
		$stmt->bind_param('i', $sender);
		$stmt->execute() or die('MySQL Error: ' . $MYSQLI->error.__LINE__);
		$result = $stmt->get_result();
		$stmt->close();

		while ($swipe = $result->fetch_assoc())
		{
			$swipes[] = $swipe;
		}
		$result->close();

		exit(json_encode($swipes));
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>FriendSwipe API</title>
</head>
<body>
	<fieldset>
		<legend>Get Swipes</legend>
		<form action="index.php" method="get">
			<p>
				<label for="sender">Sender: </label>
				<input type="text" id="sender" name="sender" />
			</p>

			<p>
				<input type="submit" name="swipes" />
			</p>
		</form>
	</fieldset>
</body>
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

	$sender = intval($_GET['sender']);

	if ($sender <= 0)
	{
		exit('Error: Sender ID must be a positive integer.');
	}
	else
	{
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

if (array_key_exists('matches', $_GET))
{
	if (!array_key_exists('sender', $_GET))
	{
		exit('Error: Sender ID not provided');
	}

	$sender = intval($_GET['sender']);

	if ($sender <= 0)
	{
		exit('Error: Sender ID must be a positive integer.');
	}
	else
	{
		$matches = array();
		$query = 'SELECT s1.sender, s1.recipient,
			s1.time AS `time_sender`, s2.time AS `time_recipient`

			FROM `' . $TBL_PREFIX . 'swipes` AS s1

			INNER JOIN `' . $TBL_PREFIX . 'swipes` AS s2
			ON s1.recipient = s2.sender

			WHERE s1.sender = ? AND s1.choice = \'reconnect\' AND
			s2.choice = \'reconnect\';';
		$stmt = $MYSQLI->prepare($query);
		$stmt->bind_param('i', $sender);
		$stmt->execute() or die('MySQL Error: ' . $MYSQLI->error.__LINE__);
		$result = $stmt->get_result();
		$stmt->close();

		while ($match = $result->fetch_assoc())
		{
			$matches[] = $match;
		}
		$result->close();

		exit(json_encode($matches));
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

	<fieldset>
		<legend>Get Matches</legend>
		<form action="index.php" method="get">
			<p>
				<label for="sender">Sender: </label>
				<input type="text" id="sender" name="sender" />
			</p>

			<p>
				<input type="submit" name="matches" />
			</p>
		</form>
	</fieldset>
</body>
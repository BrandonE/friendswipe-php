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

if (array_key_exists('swipe', $_POST))
{
	if (!array_key_exists('sender', $_POST))
	{
		exit('Error: Sender ID not provided');
	}

	$sender = intval($_POST['sender']);

	if ($sender <= 0)
	{
		exit('Error: Sender ID must be a positive integer.');
	}

	if (!array_key_exists('recipient', $_POST))
	{
		exit('Error: Recipient ID not provided');
	}

	$recipient = intval($_POST['recipient']);

	if ($recipient <= 0)
	{
		exit('Error: Recipient ID must be a positive integer.');
	}

	$query = 'SELECT `id`
		FROM `' . $TBL_PREFIX . 'swipes`
		WHERE `sender` = ? AND `recipient` = ?;';
	$result = $MYSQLI->prepare($query);
	$result->bind_param('ii', $sender, $recipient);
	$result->execute() or die('MySQL Error: ' . $MYSQLI->error.__LINE__);
	$result->store_result();

	if ($result->num_rows)
	{
		die('Error: This Sender has already swiped for this Receiver.');
	}
	$result->fetch();
	$result->close();

	if (!array_key_exists('choice', $_POST))
	{
		exit('Error: Choice not provided');
	}

	$choice = $_POST['choice'];

	if (!in_array($choice, array('ignore', 'unfollow', 'reconnect')))
	{
		exit(
			'Error: Choice must be one of the following: \'ignore\', ' .
			'\'unfollow\', or \'reconnect\''
		);
	}

	$time = time();

	$query = 'INSERT INTO `' . $TBL_PREFIX . 'swipes`
		(`choice`, `sender`, `recipient`, `time`)
		VALUES (?, ?, ?, ?);';
	$result = $MYSQLI->prepare($query);
	$result->bind_param('siii', $choice, $sender, $recipient, $time);
	$result->execute() or die('MySQL Error: ' . $MYSQLI->error.__LINE__);
	$result->close();

	$query = 'SELECT `id`
		FROM `' . $TBL_PREFIX . 'swipes`
		WHERE `choice` = ? AND `sender` = ? AND `recipient` = ? AND
		`time` = ?;';
	$result = $MYSQLI->prepare($query);
	$result->bind_param('siii', $choice, $sender, $recipient, $time);
	$result->execute() or die('MySQL Error: ' . $MYSQLI->error.__LINE__);
	$result->store_result();

	if (!$result->num_rows)
	{
		die(json_encode(false));
	}
	$result->fetch();
	$result->close();

	exit(json_encode(true));
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

	<fieldset>
		<legend>Submit Swipe</legend>
		<form action="index.php" method="post">
			<p>
				<label for="choice">Choice: </label>

				<select id="choice" name="choice">
					<option>ignore</option>
					<option>unfollow</option>
					<option>reconnect</option>
				</select>
			</p>

			<p>
				<label for="sender">Sender: </label>
				<input type="text" id="sender" name="sender" />
			</p>

			<p>
				<label for="sender">Recipient: </label>
				<input type="text" id="recipient" name="recipient" />
			</p>

			<p>
				<input type="submit" name="swipe" />
			</p>
		</form>
	</fieldset>
</body>
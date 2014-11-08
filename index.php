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
		// ERROR
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

		echo json_encode($swipes);
	}
}
?>
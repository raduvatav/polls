<?php
use \OCP\DB;
use \OCP\User;

$poll_id = $_POST['poll_id'];

// get title and description from DB, needed for next page
$query = DB::prepare('SELECT title, description, type, expire FROM *PREFIX*polls_events WHERE id=?');
$result = $query->execute(array($poll_id));
$row = $result->fetchRow();

$title = $row['title'];
$desc = $row['description'];

// is expired?
$expire = $row['expire'];
$expired = false;
if (isset($expire) && (strlen($expire) > 0)) {
	$expired = date('U') > $expire ? true : false;
}

if (!isset($desc) || !strlen($desc)) $desc = '_none_';
$poll_type = $row['type'];

if ($poll_type === 'datetime') {
	// page2 needs json->chosen
	$query = DB::prepare('SELECT dt FROM *PREFIX*polls_dts WHERE id=?');
	$result = $query->execute(array($poll_id));
	$arr = array();
	while ($row = $result->fetchRow()) {

		$dt = explode('_', $row['dt']);

		$obj = new stdClass();
		$obj->date = $dt[0];
		$obj->time = $dt[1];
		array_push($arr, $obj);

	}

	usort($arr, 'sort_dates');

	$chosen = $arr;
}
else {
	// next page (last_text.php) needs json->items
	$chosen = array();

	$query = DB::prepare('SELECT dt, description FROM *PREFIX*polls_dts WHERE id=?');
	$result = $query->execute(array($poll_id));
	while ($row = $result->fetchRow()) {
		$obj = new stdClass();
		$obj->dt = $row['dt'];
		$obj->desc = $row['description'];
		array_push($chosen, $obj);
	}

}
// other users
$others = array();
$query = DB::prepare('SELECT dt, user, ok FROM *PREFIX*polls_particip WHERE id=? ORDER BY USER');
$result = $query->execute(array($poll_id));
while ($row = $result->fetchRow()) {
	$obj = new stdClass();

	$obj->dt = $row['dt'];
	$obj->ok = $row['ok'];

	if (!isset($others[$row['user']])) {
		$others[$row['user']] = array();
	}
	array_push($others[$row['user']], $obj);
}
// comments
$query = DB::prepare('SELECT user, dt, comment FROM *PREFIX*polls_comments WHERE id=?');
$result = $query->execute(array($poll_id));
$comments = array();
while ($row = $result->fetchRow()) {
	$obj = new stdClass();
	$obj->user = $row['user'];
	$obj->dt = $row['dt'];
	$obj->comment = $row['comment'];
	array_push($comments, $obj);
}
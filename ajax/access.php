<?php
use \OCP\DB;

if (isset ($_POST) && isset ($_POST['access'])) {

	$query = DB::prepare('update *PREFIX*polls_events set access=? where id=?');
	$result = $query->execute(array($_POST['access'], $_POST['poll_id']));

}

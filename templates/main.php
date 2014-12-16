<?php
use \OCP\DB;
use \OCP\User;
use \OCP\Util;

// coming directly to vote (link)
if (isset ($_GET) && isset ($_GET['poll_id'])){

    // check if poll is public / user registered
    $query = DB::prepare('select access from *PREFIX*polls_events where id=?');
    $result = $query->execute(array($_GET['poll_id']));
    $row = $result->fetchRow();
    $access = $row['access'];

    // if !public and !loggedIn go to login page
    if (strcmp($access, 'public') && !OCP\User::isLoggedIn()){
        OCP\User::checkLoggedIn();
    }
	// check if user has access to this poll
	if (!userHasAccess($_GET['poll_id'])) {
		include 'error_no_poll.php';
		return;
	}

    unset ($_POST);

	$_POST['j'] = "vote";
	$_POST['poll_id'] = $_GET['poll_id'];
    unset ($_GET);
}

if (isset ($_POST) && isset ($_POST['j'])) {
    //echo '<pre>POST: '; print_r($_POST); echo '</pre>';

    $post_j = $_POST['j'];

	// vote: build vote page; finish: save "vote" - both available w/o login
	if(($post_j != 'vote') && ($post_j != 'finish')) OCP\User::checkLoggedIn();



	switch($post_j) {
		case 'start':
			include 'start.php';
			return;
		// coming form p0 to p1
		case 'page1':

			$title = htmlspecialchars($_POST['text_title']);
			$desc = htmlspecialchars($_POST['text_desc']);

			if (!isset($desc) || !strlen($desc)) $desc = '_none_';
			$access = $_POST['radio_pub'];

			if ($access === 'select') {

				$groups = json_decode($_POST['access_ids'])->groups;
				$users = json_decode($_POST['access_ids'])->users;

				$access = '';
				foreach ($groups as $gid) {
					$access .= 'group_' . $gid . ';';
				}
				foreach ($users as $uid) {
					$access .= 'user_' . $uid . ';';
				}
			}

			$poll_id = UTIL::generateRandomBytes(16);

			$expire = '';
			if(isset($_POST['check_expire'])) {
				$expire = $_POST['expire_date'];

				if (isset($expire) && (strlen($expire) > 0)) {
					$expire = '' . (strtotime($expire) + 60*60*24); //add one day, so it expires at the end of a day
				}
			}

			if ($_POST['radio_type'] === 'text') {
				// --- text based poll ---
				// add entry to db; don't set 'created' yet!
				$query = DB::prepare('INSERT INTO *PREFIX*polls_events(id, type, title, description, OWNER, access, expire) VALUES (?,?,?,?,?,?,?)');
				$query->execute(array($poll_id, 'text', $title, $desc, User::getUser(), $access, $expire));

				include 'select_text_items.php';
			} else {
				// --- event schedule poll (dates/times) ---

				// add entry to db; don't set 'created' yet!
				$query = DB::prepare('INSERT INTO *PREFIX*polls_events(id, type, title, description, OWNER, access, expire) VALUES (?,?,?,?,?,?,?)');
				$query->execute(array($poll_id, 'datetime', $title, $desc, User::getUser(), $access, $expire));

				// load next page
				include 'select_dates.php';
			}

			return;

		// staying in p0 with delete poll
		case 'delete':
			$id = $_POST['delete_id'];
			$query = DB::prepare('DELETE FROM *PREFIX*polls_events WHERE id=?');
			$query->execute(array($id));
			$query = DB::prepare('DELETE FROM *PREFIX*polls_dts WHERE id=?');
			$query->execute(array($id));
			$query = DB::prepare('DELETE FROM *PREFIX*polls_particip WHERE id=?');
			$query->execute(array($id));
			$query = DB::prepare('DELETE FROM *PREFIX*polls_comments WHERE id=?');
			$query->execute(array($id));
			$query = DB::prepare('DELETE FROM *PREFIX*polls_notif WHERE id=?');
			$query->execute(array($id));

			$partic = hasParticipated();
			$partic_polls = $partic['partic_polls'];
			$partic_comm = $partic['partic_comments'];

			include 'poll_summary.php';

			return;

		// from p0 -> select poll (or link)
		case 'vote':

			include 'vote.php';

			include 'last.php';

			return;

		// coming from p1 to p2 (date/time poll)
		case 'page2':
			$poll_type = 'datetime';
			$chosen = json_decode($_POST['chosen_dates'])->chosen;
			$poll_id = $_POST['poll_id'];

			usort($chosen, 'sort_dates');

			// get title and description from DB, needed for next page
			$query = DB::prepare('SELECT title, description, expire FROM *PREFIX*polls_events WHERE id=?');
			$result = $query->execute(array($poll_id));
			$row = $result->fetchRow();

			$title = $row['title'];
			$desc = $row['description'];
			$expire = $row['expire'];

			if (!isset($desc) || !strlen($desc)) $desc = '<_none_>';

			$query = DB::prepare('INSERT INTO *PREFIX*polls_dts(id, dt) VALUES(?,?)');
			foreach ($chosen as $el) {
				$query->execute(array($poll_id, $el->date . '_' . $el->time));
			}

			include 'last.php';

			return;
		// coming from p1 to p2 (text poll)
		case 'page2_text':
			$poll_type = 'text';
			$chosen = json_decode($_POST['items'])->items;
			$poll_id = $_POST['poll_id'];

			// get title and description from DB, needed for next page
			$query = DB::prepare('SELECT title, description FROM *PREFIX*polls_events WHERE id=?');
			$result = $query->execute(array($poll_id));
			$row = $result->fetchRow();

			$title = $row['title'];
			$desc = $row['description'];

			if (!isset($desc) || !strlen($desc)) $desc = '<_none_>';

			$query = DB::prepare('INSERT INTO *PREFIX*polls_dts(id, dt, description) VALUES(?,?,?)');

			foreach ($chosen as $el) {
				$query->execute(array($poll_id, $el->dt, $el->desc));
			}

			include 'last.php';
			return;

		// from p2 -> finish
		case 'finish':
			//$poll_id = $json->poll_id;

			include 'finish.php';

			include 'last.php';

            return;
    }

}

// delete unfinished polls
$query = DB::prepare('select id from *PREFIX*polls_events where created is null and owner=?');
$ids = $query->execute(array(User::getUser()));
while($row = $ids->fetchRow()){
    $query = DB::prepare('delete from *PREFIX*polls_events where created is null and id=?');
    $query->execute(array($row['id']));
    $query = DB::prepare('delete from *PREFIX*polls_dts where id=?');
    $query->execute(array($row['id']));
}

$partic = hasParticipated();
$partic_polls = $partic['partic_polls'];
$partic_comm = $partic['partic_comments'];
include 'poll_summary.php';


// ---- helper functions ----

function userHasAccess($poll_id) {

	$query = DB::prepare('select * from *PREFIX*polls_events where id=?');
	$result = $query->execute(array($poll_id));
	$row = $result->fetchRow();
	if ($row) {
		$access = $row['access'];
		$owner = $row['owner'];
	}
	else {
		return false;
	}

	if ($access === 'public') return true;
	
	if ($access === 'hidden') return true;

	if (!User::isLoggedIn()) return false;

	if ($access === 'registered') return true;

	if ($owner === User::getUser()) return true;

	$user_groups = OC_Group::getUserGroups(User::getUser());

	$arr = explode(';', $access);

	foreach ($arr as $item) {
		if (strpos($item, 'group_') === 0) {
			$grp = substr($item, 6);
			foreach ($user_groups as $user_group) {
				if ($user_group === $grp) return true;
			}
		}
		else if (strpos($item, 'user_') === 0) {
			$usr = substr($item, 5);
			if ($usr === User::getUser()) return true;
		}
	}

	return false;
}

function oclog($str) {
	Util::writeLog("_____________polls", $str, \OCP\Util::ERROR);
}

function hasParticipated(){
    $query = DB::prepare('select id from *PREFIX*polls_particip where user=? order by id');
    $polls = $query->execute(array(User::getUser()))->fetchAll();

	$query = DB::prepare('select distinct id from *PREFIX*polls_comments where user=? order by id');
	$comm = $query->execute(array(User::getUser()))->fetchAll();
	return array('partic_polls' => $polls, 'partic_comments' => $comm);
}

function sort_dates($a, $b) {
	$arra = explode('.', $a->date);
	$dta = $arra[2] . $arra[1] . $arra[0] . '_' . $a->time;
	$arrb = explode('.', $b->date);
	$dtb = $arrb[2] . $arrb[1] . $arrb[0] . '_' . $b->time;

	return strcmp($dta, $dtb);
}
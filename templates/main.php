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
	// if !registered check access (groups/users)
//	else if (($access !== 'registered') || strcmp($access, 'public')) {
		// check if user has access to this poll
		if (!userHasAccess($_GET['poll_id'])) {
			include 'error_no_poll.php';
			return;
		}
//	}

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

			$partic = hasParticipated();
			$partic_polls = $partic['partic_polls'];
			$partic_comm = $partic['partic_comments'];
			include 'poll_summary.php';
			return;

		// from p0 -> select poll (or link)
		case 'vote':
			//$poll_id = $json->poll_id;
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
			$poll_id = $_POST['poll_id'];
			$poll_type = $_POST['poll_type'];
			$options = json_decode($_POST['options']);

			$sel_yes = $options->sel_yes;
			$sel_no = $options->sel_no;
			if (User::isLoggedIn()) {
				$user = User::getUser();
			} else {
				$user = htmlspecialchars($options->ac_user);
			}
			//get current set dates
			$query = DB::prepare('SELECT ok, dt FROM *PREFIX*polls_particip WHERE id=? AND user=?');
			$result = $query->execute(array($poll_id, $user));
			$set_dts = $result->fetchAll();
			// remove row (if exist, else doesn't matter)
			$query = DB::prepare('DELETE FROM *PREFIX*polls_particip WHERE id=? AND USER=?');
			$result = $query->execute(array($poll_id, $user));

			$sql = 'INSERT INTO *PREFIX*polls_particip(ok, id, USER, dt) VALUES(?,?,?,?)';
			$query = DB::prepare('INSERT INTO *PREFIX*polls_particip(ok, id, USER, dt) VALUES(?,?,?,?)');
			
            $hits = 0;
            
			// insert
			foreach ($sel_yes as $dt) {
			    foreach($set_dts as $set_dt){
			        if($set_dt['dt'] == $dt && $set_dt['ok'] == 'yes'){
			            $hits++;
			            break;
			        }
			    }
				$query->execute(array('yes', $poll_id, $user, $dt));
			}
			foreach ($sel_no as $dt) {
			    foreach($set_dts as $set_dt){
			        if($set_dt['dt'] == $dt && $set_dt['ok'] == 'no'){
			            $hits++;
			            break;
			        }
			    }
				$query->execute(array('no', $poll_id, $user, $dt));
			}
			//if one line has changed
			if( $hits != (count($sel_no) + count($sel_yes)) ){
			    $receivers = array();
				//get owner
				$query = DB::prepare('SELECT owner, title FROM *PREFIX*polls_events WHERE id=?');
				$result = $query->execute(array($poll_id));
				$row = $result->fetchRow();
				$title = $row['title'];
				array_push($receivers, $row['owner']);
				//get participants
				$query = DB::prepare('SELECT user FROM *PREFIX*polls_particip WHERE id=?');
				$result = $query->execute(array($poll_id));
				while ($row = $result->fetchRow()){
					array_push($receivers, $row['user']);
				}
				//get comments
				$query = DB::prepare('SELECT user FROM *PREFIX*polls_comments WHERE id=?');
				$result = $query->execute(array($poll_id));
				while ($row = $result->fetchRow()){
					array_push($receivers, $row['user']);
				}
				$users = array_unique($receivers);
				foreach($users as $uid){
					if($user === $uid) continue;
					$email = \OCP\Config::getUserValue($uid, 'settings', 'email');
					if(strlen($email) === 0 || !isset($email)) continue;
					$url = \OC_Helper::makeURLAbsolute(OCP\Util::linkToRoute('polls_goto', array('poll_id' => $poll_id)));
					$msg = $l->t('Hello %s,<br/><br/><strong>%s</strong> participated in the poll \'%s\'.<br/><br/>To go directly to the poll, you can use this link: <a href="%s">%s</a>', array(
						OCP\User::getDisplayName($uid), OCP\User::getDisplayName($user), $title, $url, $url));
					$msg .= "<br/><br/>";
					$toname = OCP\User::getDisplayName($uid);
					$subject = $l->t('ownCloud Polls -- New Comment');
					$fromaddress = "polls-noreply@getenv.net";
					$fromname = $l->t("ownCloud Polls");
					OC_Mail::send($email, $toname, $subject, $msg, $fromaddress, $fromname, 1);
				}
			}

			// set 'created' timestamp if this user is the owner
			$query = DB::prepare('SELECT owner, created, access, title FROM *PREFIX*polls_events WHERE id=?');
			$result = $query->execute(array($poll_id));
			$row = $result->fetchRow();


			if (($user === $row['owner']) && (!isset($row['created']))) {
				// only on new

				// set creation date
				$query = DB::prepare('UPDATE *PREFIX*polls_events SET created=? WHERE id=?');
				//$query->execute(array(date('d.m.Y_H:i'), $poll_id));
				$query->execute(array(date('U'), $poll_id));
				$access = $row['access'];
				$title = $row['title'];
				if($access !== 'hidden'){
					$users = array();
					if($access === 'public' || $access === 'registered'){
						$users = OC_User::getUsers();
					} else if(strpos($access, ';') !== false){
						$receivers = array();
						$arr = explode(';', $access);
						foreach ($arr as $item) {
							if (strpos($item, 'group_') === 0) {
								$grp = substr($item, 6);
								$users = OC_Group::usersInGroup($grp);
								foreach ($users as $uid) {
									if($user === $uid) continue;
									array_push($receivers, $uid);
								}
							}
							else if (strpos($item, 'user_') === 0) {
								$uid = substr($item, 5);
								array_push($receivers, $uid);
							}
						}
						$users = array_unique($receivers);
					}
					foreach($users as $uid){
						if($user === $uid) continue;
						$email = \OCP\Config::getUserValue($uid, 'settings', 'email');
						if(strlen($email) === 0 || !isset($email)) continue;
						$url = \OC_Helper::makeURLAbsolute(OCP\Util::linkToRoute('polls_goto', array('poll_id' => $poll_id)));
						$msg = $l->t('Hello %s,<br/><br/><strong>%s</strong> shared the poll \'%s\' with you. To go directly to the poll, you can use this link: <a href="%s">%s</a>', array(
							OCP\User::getDisplayName($uid), OCP\User::getDisplayName($user), $title, $url, $url));
						$msg .= "<br/><br/>";
						$toname = OCP\User::getDisplayName($uid);
						$subject = $l->t('ownCloud Polls -- New Poll');
						$fromaddress = "polls-noreply@getenv.net";
						$fromname = $l->t("ownCloud Polls");
						OC_Mail::send($email, $toname, $subject, $msg, $fromaddress, $fromname, 1);
						//if(!$sent) oclog("Could not send email with the subject " . $subject . " to " . $to);
					}
				}
			}

			// save comment
			if (isset($options->comment) && (strlen($options->comment) > 0)) {
				$receivers = array();
				//get owner
				$query = DB::prepare('SELECT owner, title FROM *PREFIX*polls_events WHERE id=?');
				$result = $query->execute(array($poll_id));
				$row = $result->fetchRow();
				$title = $row['title'];
				array_push($receivers, $row['owner']);
				//get participants
				$query = DB::prepare('SELECT user FROM *PREFIX*polls_particip WHERE id=?');
				$result = $query->execute(array($poll_id));
				while ($row = $result->fetchRow()){
					array_push($receivers, $row['user']);
				}
				//get comments
				$query = DB::prepare('SELECT user FROM *PREFIX*polls_comments WHERE id=?');
				$result = $query->execute(array($poll_id));
				while ($row = $result->fetchRow()){
					array_push($receivers, $row['user']);
				}
				$users = array_unique($receivers);
				foreach($users as $uid){
					if($user === $uid) continue;
					$email = \OCP\Config::getUserValue($uid, 'settings', 'email');
					if(strlen($email) === 0 || !isset($email)) continue;
					$url = \OC_Helper::makeURLAbsolute(OCP\Util::linkToRoute('polls_goto', array('poll_id' => $poll_id)));
					$msg = $l->t('Hello %s,<br/><br/><strong>%s</strong> commented on the poll \'%s\'.<br/><br/><i>%s</i><br/><br/>To go directly to the poll, you can use this link: <a href="%s">%s</a>', array(
						OCP\User::getDisplayName($uid), OCP\User::getDisplayName($user), $title, htmlspecialchars($options->comment), $url, $url));
					$msg .= "<br/><br/>";
					$toname = OCP\User::getDisplayName($uid);
					$subject = $l->t('ownCloud Polls -- New Comment');
					$fromaddress = "polls-noreply@getenv.net";
					$fromname = $l->t("ownCloud Polls");
					OC_Mail::send($email, $toname, $subject, $msg, $fromaddress, $fromname, 1);
				}
				
				$query = DB::prepare('INSERT INTO *PREFIX*polls_comments(id,USER,dt,COMMENT) VALUES(?,?,?,?)');
				//$query->execute(array($poll_id, $user, date('d.m.Y_H:i'), $json->comment));
				$query->execute(array($poll_id, $user, date('U'), htmlspecialchars($options->comment)));
			}

			// delete not finished polls
			$query = DB::prepare('DELETE FROM *PREFIX*polls_events WHERE created IS NULL');
			$query->execute();
			/*if (User::isLoggedIn()) {
				include 'poll_summary.php';
			}
			else {
				\OCP\Util::addScript('polls', 'page_anon');
			}*/

			/* Load vote page (copy of case 'vote')*/
			unset($_POST);

			$query = DB::prepare('SELECT title, description FROM *PREFIX*polls_events WHERE id=?');
			$result = $query->execute(array($poll_id));
			$row = $result->fetchRow();

			$title = $row['title'];
			$desc = $row['description'];

			if (!isset($desc) || !strlen($desc)) $desc = '<_none_>';

			if ($poll_type === 'datetime') {
				// next page (last.php) needs json->chosen
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
			else { //text
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
			$query = DB::prepare('select dt, user, ok from *PREFIX*polls_particip where id=? order by user');
			$result = $query->execute(array($poll_id));
			while($row = $result->fetchRow()) {
				$obj = new stdClass();

				$obj->dt = $row['dt'];
				$obj->ok = $row['ok'];

				if (!isset($others[$row['user']])) {
					$others[$row['user']] = array();
				}
				array_push($others[$row['user']], $obj);
			}
			// comments
			$query = DB::prepare('select user, dt, comment from *PREFIX*polls_comments where id=?');
			$result = $query->execute(array($poll_id));
			$comments = array();
			while($row = $result->fetchRow()) {
				$obj = new stdClass();
				$obj->user = $row['user'];
				$obj->dt = $row['dt'];
				$obj->comment = $row['comment'];
				array_push($comments, $obj);
			}
			if ($poll_type === 'datetime') {

				include 'last.php';
			}
			else {
				include 'last.php';
			}
			
            return;
		/*case 'home':
			include 'poll_summary.php';
			return;*/
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
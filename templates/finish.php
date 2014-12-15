<?php
use \OCP\DB;
use \OCP\User;
use \OC_L10N;

$poll_id = $_POST['poll_id'];
$poll_type = $_POST['poll_type'];
$options = json_decode($_POST['options']);


$sel_yes = $options->sel_yes;
$sel_no = $options->sel_no;
if (User::isLoggedIn()) {
	$user = User::getUser();

	// save if user wants to get email notifications or not
	$check_notif = $options->check_notif === 'true';
	$query = DB::prepare('DELETE FROM *PREFIX*polls_notif WHERE id=? AND user=?');
	$query->execute(array($poll_id, $user));

	if ($check_notif) {

		$query = DB::prepare('INSERT INTO *PREFIX*polls_notif(id, user) values(?, ?)');
		$query->execute(array($poll_id, $user));
	}

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



// if current user made some input, notify all subscribed users
if(($options->values_changed === 'true') || (isset($options->comment) && (strlen($options->comment) > 0))){

	$users = array();
	$query = DB::prepare('SELECT user FROM *PREFIX*polls_notif WHERE id=?');
	$res = $query->execute(array($poll_id));
	while ($row = $res->fetchRow()) {
		array_push($users, $row['user']);
	}

	$query = DB::prepare('INSERT INTO *PREFIX*polls_particip(ok, id, USER, dt) VALUES(?,?,?,?)');
	// insert
	foreach ($sel_yes as $dt) {
		$query->execute(array('yes', $poll_id, $user, $dt));
	}
	foreach ($sel_no as $dt) {
		$query->execute(array('no', $poll_id, $user, $dt));
	}

	foreach($users as $uid){
		if($user === $uid) continue;
		$email = \OCP\Config::getUserValue($uid, 'settings', 'email');
		if(strlen($email) === 0 || !isset($email)) continue;
		$url = \OC_Helper::makeURLAbsolute(OCP\Util::linkToRoute('polls_goto', array('poll_id' => $poll_id)));

		// set translation language according to the user who receives the email
		OC_L10N::forceLanguage($uid, 'core', 'lang', 'en');

		$msg = $l->t('Hello %s,<br/><br/><strong>%s</strong> participated in the poll \'%s\'.<br/><br/>To go directly to the poll, you can use this link: <a href="%s">%s</a>', array(
			OCP\User::getDisplayName($uid), OCP\User::getDisplayName($user), $title, $url, $url));

		$msg .= "<br/><br/>";

		$toname = OCP\User::getDisplayName($uid);
		$subject = $l->t('ownCloud Polls -- New Comment');
		$fromaddress = "polls-noreply@localhost";
		$fromname = $l->t("ownCloud Polls");

		OC_Mail::send($email, $toname, $subject, $msg, $fromaddress, $fromname, 1);
	}

	// set the language back (todo: really need this?)
	OC_L10N::forceLanguage($user, 'core', 'lang', 'en');

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
			$fromaddress = "polls-noreply@localhost";
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
		$fromaddress = "polls-noreply@localhost";
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
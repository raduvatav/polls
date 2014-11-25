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
	else if (strcmp($access, 'registered')) {
		// check if user has access to this poll
		if (!userHasAccess($_GET['poll_id'])) {
			echo '<h1>You are not allowed to view this poll</h1>';
			return;
		}
	}


    unset ($_POST);

	$_POST['j'] = "vote";
	$_POST['poll_id'] = $_GET['poll_id'];
    unset ($_GET);
}

if (isset ($_POST) && isset ($_POST['j'])) {
    //echo '<pre>POST: '; print_r($_POST); echo '</pre>';

    $post_j = $_POST['j'];

    //$json = json_decode($post_j);
    //echo '<pre>json: '; print_r($json); echo '</pre>';

	//oclog("postj: " . $post_j);

	// vote: build vote page; finish: save "vote" - both available w/o login
	if(($post_j != 'vote') && ($post_j != 'finish')) OCP\User::checkLoggedIn();



	switch($post_j){
        case 'start':
            include 'start.php';
            return;
        // coming form p0 to p1
        case 'page1':
			$title = htmlspecialchars($_POST['text_title']);
			$desc = htmlspecialchars($_POST['text_desc']);
			$access = $_POST['radio_pub'];

			if ($access === 'select') {

				$groups = json_decode($_POST['access_ids'])->groups;
				$users = json_decode($_POST['access_ids'])->users;

				$access = '';
				foreach($groups as $gid) {
					$access .= 'group_' . $gid . ';';
				}
				foreach($users as $uid) {
					$access .= 'user_' . $uid . ';';
				}
			}

            // add entry to db; don't set 'created' yet!
			$poll_id = substr(md5(uniqid('', true)), 0, 16);

            $query = DB::prepare('insert into *PREFIX*polls_events(id, title, description, owner, access) values (?,?,?,?,?)');
            $result = $query->execute(array($poll_id, $title, $desc, User::getUser(), $access));

            // load next page
            include 'select_dates.php';

            return;

        // staying in p0 with delete poll
        case 'delete':
			$id = $_POST['delete_id'];
            $query = DB::prepare('delete from *PREFIX*polls_events where id=?');
            $query->execute(array($id));
            $query = DB::prepare('delete from *PREFIX*polls_dts where id=?');
            $query->execute(array($id));
            $query = DB::prepare('delete from *PREFIX*polls_particip where id=?');
            $query->execute(array($id));
            $query = DB::prepare('delete from *PREFIX*polls_comments where id=?');
            $query->execute(array($id));

            $result = hasParticipated();
			$partic_polls = $partic['partic_polls'];
			$partic_comm = $partic['partic_comments'];
            include 'poll_summary.php';
            return;

        // from p0 -> select poll (or link)
        case 'vote':
            //$poll_id = $json->poll_id;
			$poll_id = $_POST['poll_id'];

            // get title and description from DB, needed for next page
            $query = DB::prepare('select title, description from *PREFIX*polls_events where id=?');
            $result = $query->execute(array($poll_id));
            $row = $result->fetchRow();

            $title = $row['title'];
            $desc = $row['description'];

            // page2 needs json->chosen
            $query = DB::prepare('select dt from *PREFIX*polls_dts where id=?');
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


            include 'last.php';
            return;

        // coming from p1 to p2
        case 'page2':
			$chosen = json_decode($_POST['chosen_dates'])->chosen;
			$poll_id = $_POST['poll_id'];
            /*$poll_id = $json->poll_id;*/
			
			usort($chosen, 'sort_dates');

            // get title and description from DB, needed for next page
            $query = DB::prepare('select title, description from *PREFIX*polls_events where id=?');
            $result = $query->execute(array($poll_id));
            $row = $result->fetchRow();

            $title = $row['title'];
            $desc = $row['description'];


            $query = DB::prepare('insert into *PREFIX*polls_dts(id, dt) values(?,?)');
            foreach($chosen as $el) {
                $query->execute(array($poll_id, $el->date . '_' . $el->time));
            }

            include 'last.php';

            return;

        // from p2 -> finish
        case 'finish':

            //$poll_id = $json->poll_id;
			$poll_id = $_POST['poll_id'];
			$options = json_decode($_POST['options']);

            $sel_yes = $options->sel_yes;
            $sel_no = $options->sel_no;
            if (User::isLoggedIn()) {
                $user = User::getUser();
            }
            else {
                $user = htmlspecialchars($options->ac_user);
            }
            // remove row (if exist, else doesn't matter)
            $query = DB::prepare('delete from *PREFIX*polls_particip where id=? and user=?');
            $result = $query->execute(array($poll_id, $user));

            $sql = 'insert into *PREFIX*polls_particip(ok, id, user, dt) values(?,?,?,?)';
            $query = DB::prepare('insert into *PREFIX*polls_particip(ok, id, user, dt) values(?,?,?,?)');

            // insert
            foreach ($sel_yes as $dt){
                $query->execute(array('yes', $poll_id, $user, $dt));
            }
            foreach ($sel_no as $dt){
                $query->execute(array('no', $poll_id, $user, $dt));
            }


            // set 'created' timestamp if this user is the owner
            $query = DB::prepare('select owner, created from *PREFIX*polls_events where id=?');
            $result = $query->execute(array($poll_id));
            $row = $result->fetchRow();


            if (($user === $row['owner'] ) && (!isset($row['created']))) {
                // only on new

                // set creation date
                $query = DB::prepare('update *PREFIX*polls_events set created=? where id=?');
                //$query->execute(array(date('d.m.Y_H:i'), $poll_id));
				$query->execute(array(date('U'), $poll_id)); //TODO change time format to date('U')
            }

            // save comment
            if (isset($options->comment) && (strlen($options->comment) > 0)) {

                $query = DB::prepare('insert into *PREFIX*polls_comments(id,user,dt,comment) values(?,?,?,?)');
                //$query->execute(array($poll_id, $user, date('d.m.Y_H:i'), $json->comment));
				$query->execute(array($poll_id, $user, date('U'), $options->comment));

            }


            // delete not finished polls
            $query = DB::prepare('delete from *PREFIX*polls_events where created is null');
            $query->execute();
            /*if (User::isLoggedIn()) {
                include 'poll_summary.php';
            }
            else {
                \OCP\Util::addScript('polls', 'page_anon');
            }*/
			
			/* Load vote page (copy of case 'vote')*/
			unset($_POST);
			
			$query = DB::prepare('select title, description from *PREFIX*polls_events where id=?');
			$result = $query->execute(array($poll_id));
			$row = $result->fetchRow();

			$title = $row['title'];
			$desc = $row['description'];

			// page2 needs json->chosen
			$query = DB::prepare('select dt from *PREFIX*polls_dts where id=?');
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
			include 'last.php';
			
            return;
		/*case 'home':
			include 'poll_summary.php';
			return;*/
    }

}

// delete unfinished polls
$query = DB::prepare('delete from *PREFIX*polls_events where created is null and owner=?');
$query->execute(array(User::getUser()));

$partic = hasParticipated();
$partic_polls = $partic['partic_polls'];
$partic_comm = $partic['partic_comments'];
include 'poll_summary.php';


// ---- helper functions ----

function userHasAccess($poll_id) {

	if (!User::isLoggedIn()) return false;

	$query = DB::prepare('select * from *PREFIX*polls_events where id=?');
	$result = $query->execute(array($poll_id));
	$row = $result->fetchRow();
	if ($row) {
		$access = $row['access'];
	}
	else {
		return false;
	}

	if (($access === 'registered') || ($access === 'public')) return true;

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

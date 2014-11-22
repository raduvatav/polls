<?php
use \OCP\DB;
use \OCP\User;

if (isset ($_GET) && isset ($_GET['poll_id'])){
	unset ($_POST);
	$_POST['j'] = '{"q":"vote","poll_id":"' . $_GET['poll_id'] . '"}';
	unset ($_GET);
	
}

if (isset ($_POST) && isset ($_POST['j'])) {
	//echo '<pre>POST: '; print_r($_POST); echo '</pre>';

    $post_j = $_POST['j'];

    $json = json_decode($post_j);
	//echo '<pre>json: '; print_r($json); echo '</pre>';

    switch($json->q){
        // coming form p0 to p1
        case 'page1':
	        $title = htmlspecialchars($json->title);
            $descr = htmlspecialchars($json->descr);
 
            // add entry to db; don't set 'created' yet!
            $query = DB::prepare('insert into *PREFIX*polls_events(title, description, owner) values (?,?,?)');
            $result = $query->execute(array($title, $descr, User::getUser()));

            $poll_id = DB::insertid();

            // load next page
            include 'page1.php';

            return;

		// staying in p0 with delete poll
		case 'delete':
			$query = DB::prepare('delete from *PREFIX*polls_events where id=?');
			$query->execute(array($json->id));
			$query = DB::prepare('delete from *PREFIX*polls_dts where id=?');
			$query->execute(array($json->id));
			$query = DB::prepare('delete from *PREFIX*polls_particip where id=?');
			$query->execute(array($json->id));
			$query = DB::prepare('delete from *PREFIX*polls_comments where id=?');
			$query->execute(array($json->id));

			include 'page0.php';
			return;

        // from p0 -> select poll
        case 'vote':
            $poll_id = $json->poll_id;

            // get title and description from DB, needed for next page
            $query = DB::prepare('select title, description from *PREFIX*polls_events where id=?');
            $result = $query->execute(array($poll_id));
            $row = $result->fetchRow();

            $title = $row['title'];
            $descr = $row['description'];

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

            $json->chosen = $arr;

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


            include 'page2.php';
            return;

        // coming from p1 to p2
        case 'page2':

            $poll_id = $json->poll_id;

			usort($json->chosen, 'sort_dates');

            // get title and description from DB, needed for next page
            $query = DB::prepare('select title, description from *PREFIX*polls_events where id=?');
            $result = $query->execute(array($poll_id));
            $row = $result->fetchRow();

            $title = $row['title'];
            $descr = $row['description'];


            $query = DB::prepare('insert into *PREFIX*polls_dts(id, dt) values(?,?)');
            foreach($json->chosen as $el) {
                $query->execute(array($poll_id, $el->date . '_' . $el->time));
            }

            include 'page2.php';

            return;

        // from p2 -> finish
        case 'finish':

            $poll_id = $json->poll_id;

            $sel_yes = $json->sel_yes;
            $sel_no = $json->sel_no;
            $user = User::getUser();

            // remove row (if exist, else doesn't matter)
            $query = DB::prepare('delete from *PREFIX*polls_particip where id=? and user=?');
            $result = $query->execute(array($poll_id, $user));

            $sql = 'insert into *PREFIX*polls_particip(ok, id, user, dt) values(?,?,?,?)';
            $query = DB::prepare('insert into *PREFIX*polls_particip(ok, id, user, dt) values(?,?,?,?)');

            // insert
            foreach ($sel_yes as $dt){

                $query->execute(array('yes', $poll_id, User::getUser(), $dt));
            }
            foreach ($sel_no as $dt){

                $query->execute(array('no', $poll_id, User::getUser(), $dt));
            }


            // set 'created' timestamp if this user is the owner
            $query = DB::prepare('select owner, created from *PREFIX*polls_events where id=?');
            $result = $query->execute(array($poll_id));
            $row = $result->fetchRow();


            if (($user === $row['owner'] ) && (!isset($row['created']))) {
                // only on new

                // set creation date
                $query = DB::prepare('update *PREFIX*polls_events set created=? where id=?');
                $query->execute(array(date('d.m.Y_H:i'), $poll_id));
            }

            // save comment
            if (isset($json->comment) && (strlen($json->comment) > 0)) {

                $query = DB::prepare('insert into *PREFIX*polls_comments(id,user,dt,comment) values(?,?,?,?)');
                $query->execute(array($poll_id, $user, date('d.m.Y_H:i'), $json->comment));

            }


            // delete not finished polls
            $query = DB::prepare('delete from *PREFIX*polls_events where created is null');
            $query->execute();

            include 'page0.php';
            return;
    }

}

// delete unfinished polls
$query = DB::prepare('delete from *PREFIX*polls_events where created is null and owner=?');
$query->execute(array(User::getUser()));

include 'page0.php';



function sort_dates($a, $b) {
	$arra = explode('.', $a->date);
	$dta = $arra[2] . $arra[1] . $arra[0] . '_' . $a->time;
	$arrb = explode('.', $b->date);
	$dtb = $arrb[2] . $arrb[1] . $arrb[0] . '_' . $b->time;

	return strcmp($dta, $dtb);
}


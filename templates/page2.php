<?php
\OCP\Util::addStyle('polls', 'page2');
\OCP\Util::addScript('polls', 'page2');
use \OCP\DB;
use \OCP\User;

// count how many times in each date
$arr_dates = null;  // will be like: [21.02] => 3
$arr_years = null;  // [1992] => 6

for ($i = 0; $i < count($json->chosen); $i++){
    $ch_obj = $json->chosen[$i];
    $arr = explode('.', $json->chosen[$i]->date);
    $day_month = $arr[0] . '.' . $arr[1] . '.'; // 21.02
    $year = $arr[2];                      // 1992

    if (isset($arr_dates[$day_month])) {
        $arr_dates[$day_month] += 1;
    }
    else {
        $arr_dates[$day_month] = 1;
    }

    // -----
    if (isset($arr_years[$year])) {
        $arr_years[$year] += 1;
    }
    else {
        $arr_years[$year] = 1;
    }

}

$for_string_dates = '';
foreach (array_keys($arr_dates) as $dt){                           // date (13.09)
    $for_string_dates .= '<th colspan="' . $arr_dates[$dt] . '">' . $dt . '</th>';
}

$for_string_years = '';
foreach (array_keys($arr_years) as $year) {                         // year (1992)
    $for_string_years .= '<th colspan="' . $arr_years[$year] . '">' . $year . '</th>';
}


echo '<table class="cl_table_1" id="id_table_1">';

$line = str_replace("\n", '<br>', $descr);

// ----------- title / descr --------
echo '<tr>';
echo '<th><div id="id_title">' . $title . '</div></th>';

echo '<td colspan="' . (count($json->chosen)+1) . '" class="cl_desc_header"><div id="id_descr">' . $line . '</div></th>';
echo '</tr>';

// -------------- url ---------------
echo '<tr><th>poll url:</th><td colspan="' . (count($json->chosen)+1) . '">';
$url = \OCP\Util::linkToRoute('polls_index'); 
$url = \OC_Helper::makeURLAbsolute($url);
$url .= 'goto/' . $poll_id;
echo $url;
echo '</td></tr>';

// empty row
echo '<tr><td>&nbsp</td></tr>';

// ---------- main table --------------
echo '    <tr>';
echo '        <th rowspan="3">&nbsp</th>';                          // upper left header rectangle

echo $for_string_years;

echo '    </tr>';
echo '    <tr>';

echo $for_string_dates;

echo '    </tr>';

echo '    <tr>';

for ($i = 0; $i < count($json->chosen); $i++) {
    $ch_obj = $json->chosen[$i];
    echo '    <th>' . $ch_obj->time . '</th>';
}

echo '    </tr>';

// init array for counting 'yes'-votes for each dt
$total_y = array();
for ($i = 0; $i < count($json->chosen); $i++){
	$total_y[$i] = 0;
}
$user_voted = null;
// -------------- other users ---------------
// loop over users
foreach (array_keys($others) as $usr) {

    if ($usr === User::getUser()) {
        $user_voted = $others[$usr];
        continue;
    }


    echo '<th>' . $usr . '</th>';
	$i_tot = -1;
	// loop over dts
    foreach($json->chosen as $dt){
		$i_tot++;

        $cl = 'cl_maybe';

        $arr = $others[$usr];

        $str = $dt->date . '_' . $dt->time;

		// look what user voted for this dts
        foreach ($others[$usr] as $obj){
            if ($str === $obj->dt) {
                if ($obj->ok === 'yes'){
                    $cl = 'cl_yes';
					$total_y[$i_tot]++;
                }
                else if ($obj->ok === 'no'){
					$total_n[$i_tot]++;
                    $cl = 'cl_no';
                }
                break;
            }
        }

        //echo '<td class="' . $cl . '">&nbsp';
        echo '<td class="' . $cl . '">' . $obj->date;

        echo '<input type="hidden" value="' . $str .   '" />';
        echo '</td>';
    }

    echo '</tr>';

}


// -------------- current user --------------

echo '<tr>';
echo '<th>' . User::getUser() . '</th>';
$i_tot = -1;
foreach ($json->chosen as $dt) {
	$i_tot++;

    $str = $dt->date . '_' . $dt->time;

    // see if user already has data for this event
    $cl = 'cl_maybe';
    if (isset($user_voted)){
        foreach ($user_voted as $obj) {
            if ($obj->dt === $str) {
                if ($obj->ok === 'yes'){
                    $cl = 'cl_yes';
					$total_y[$i_tot]++;
                }
                else if ($obj->ok === 'no'){
                    $cl = 'cl_no';
					$total_n[$i_tot]++;
                }
            }
        }
    }

    echo '<td class="cl_click ' . $cl . '">&nbsp';


    echo '<input type="hidden" value="' . $str .   '" />';
    echo '</td>';
}
echo '</tr>';
// --------------- total --------------------
echo '<tr>';
echo '<th>Total:</th>';
for ($i = 0; $i < count($json->chosen); $i++) {
	echo '<td><table id="id_tab_total"><tr>';
	echo '<td id="id_y_' . $i . '" class="cl_total_y">' . (isset($total_y[$i]) ? $total_y[$i] : '0') . '</td>';
	echo '<td id="id_n_' . $i . '" class="cl_total_n">' . (isset($total_n[$i]) ? $total_n[$i] : '0') . '</td>';
	echo '</tr></table></td>';
}
echo '</tr>';


echo '</table>';

echo '<table class="cl_comment">';
// -------- leave comment ----------

echo '<tr><td>&nbsp</td></tr>';
echo '<tr>';
echo '    <th>Comment</th>';
echo '    <td><textarea cols="50" rows="5" id="id_comment"></textarea></td>';
echo '</tr>';


// -------- submit -----------
echo '<tr><td>&nbsp</td></tr>';


echo '<tr>';
echo '    <form name="form1" action="' . \OCP\Util::linkToRoute('polls_index') . '" method="POST">';
echo '        <input type="hidden" name="j" />';
echo '        <input type="hidden" name="poll_id" value="' . $poll_id . '" />';
echo '        <td colspan="2" style "background-color: white">';
echo '           <input type="button" id="id_submit" value="Finish" />';
echo '        </td>';
echo '    </form>';
echo '</tr>';


echo '</table>';


// -------- comments ----------
echo '<table class="cl_user_comments">';
foreach ($comments as $obj) {
    echo '<tr>';

    echo '<th>';
    echo '    <div id="id_user_name">' . $obj->user . ' :</div>';

    echo '  <br>';
    echo '    <div id="id_user_dt">' . str_replace('_', '<br>', $obj->dt) . '</div>';

    echo '</th>';

    echo '<td>' . str_replace("\n", "<br>", $obj->comment) . '</td>';
    echo '</tr>';
}
echo '</table>';

<?php
\OCP\Util::addStyle('polls', 'page1');
\OCP\Util::addScript('polls', 'page1');

$arr = explode("\n", $descr);
echo '<table id="id_table_1">';
echo '    <tr>';
echo '        <th class="cl_title_header" rowspan="' . count($arr) . '"><em><div id="id_title">' . $title . '</div></em></th>';

for ($i = 0; $i < count($arr); $i++) {
    $line = $arr[$i];
    if ($i > 0)
    echo '    <tr>';
    echo '    <th class="cl_desc_header"><em><div id="id_descr">' . $line . '</div></em></th>';
    echo '    </tr>';
}
echo '<tr><td>&nbsp</td></tr>';
echo '    <tr>';
echo '        <td><h2>Click on days to add or remove</h2></td>';
echo '        <td><h2>Select hour & minute, then click on time</h2></td>';
echo '    </tr>';
echo '    <tr>';
echo '        <td class="cl_pad_left">';
echo '            <table id="id_cal_table" class="cl_with_border">';
echo '                <tr>';
echo '                    <th style="padding:0px" colspan="1">';
echo '                        <a id="id_header_prev_month"><<</a>';
echo '                    </th>';
echo '                    <th id="id_header_curr_month" colspan="2"></th>';
echo '                    <th id="id_header_curr_year" colspan="3"></th>';
echo '                    <th style="padding:0px" colspan="1">';
echo '                        <a id="id_header_next_month">>></a>';
echo '                    </th>';
echo '                </tr>';
echo '                <tr>';
echo '                    <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>';
echo '                </tr>';

for ($i = 0; $i < 6; $i++) {
    echo '            <tr>';
    for ($j = 0; $j < 7; $j++) {
        echo '            <td id="id_cell_' . $i . '_' . $j . '" ></td>';

    }
    echo '            </tr>';
}

echo '            </table>';
echo '        </td>';
echo '        <td class="cl_pad_left">';
echo '            <table id="id_time_table">';
echo '                <tr>';
echo '                    <td>';
echo '                        <table id="id_hours_table" >';

// -------- hours ------
echo '                            <tr>';
echo '                                <td class="cl_hour_selected" id="id_hour_0">00</td>';
for ($i = 1; $i < 24; $i++) {
    $str = sprintf("%02d", $i);
    echo '                            <td class="cl_hour" id="id_hour_' . $i . '">' . $str . '</td>';
}
echo '                            </tr>';

// -------- minutes ----
echo '                            <tr>';
echo '                                <td colspan="4" class="cl_min_selected" id="id_min_00">00</td>';
for ($i = 10; $i < 60; $i += 10) {
    $str = sprintf("%02d", $i);
    echo '                            <td colspan="4" class="cl_min" id="id_min_' . $str . '">' . $str . '</td>';
}
echo '                            </tr>';

// -------- selected hour ---
echo '                            <tr>';
echo '                                <td colspan="8" > click to add ---></td>';
echo '                                <td colspan="8" class="cl_time_display" id="id_time_display">00:00</td>';
echo '                                <td colspan="8"><--- click to add </td>';
echo '                            </tr>';

echo '                        </table>';
echo '                    </td>';
echo '                </tr>';

echo '                <tr>';
echo '                    <td>';
echo '                        <table  class="cl_with_border" id="id_poss_table">';
// table
// -------- entries ('possibilities', to be filled by js)
echo '                            <tr id="id_poss_table_header_row">';    // header row
echo '                                <th>date\time</th>';         // corner (date\time)
echo '                            </tr>';

echo '                        </table>';
echo '                    </td>';
echo '                </tr>';
echo '            </table>';
echo '        </td>';
echo '    </tr>';
echo '    <tr>';
echo '        <form name="form1" method="POST">';
echo '            <input type="hidden" name="j" />';
echo '            <input type="hidden" name="poll_id" value="' . $poll_id . '" />';
echo '            <td colspan="2">';
echo '                <input type="button" id="id_submit" value="...next" />';
echo '            </td>';
echo '        </form>';
echo '    </tr>';
echo '</table>';


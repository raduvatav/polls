<?php
\OCP\Util::addStyle('polls', 'page0');
\OCP\Util::addScript('polls', 'page0');

?>

<form name="form1" method="POST">
<input type="hidden" name="j" />

<table>
    <tr>
        <td>
            <table id="id_table_1" class="cl_create_form">
                <tr>
                    <td colspan="2"><div class="cl_title">Create new poll</div></td>
                </tr>
                <tr>
                    <th>Title</th>
                    <td><input type="text" id="id_in_title" /></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><textarea cols="50" rows="5" id="id_in_descr"></textarea></td>
                </tr>
                <tr>
                        <td colspan="2"><input type="button" id="id_submit" value="...next" /></td>
                </tr>
            </table>
        </td>
        <td>

            <table class="cl_create_form">
                <tr>
                    <td colspan="4"><div class="cl_title">Go to...</div></td>
                </tr>
                <tr>
                    <th>Title</th>
                    <th id="id_th_descr">Description</th>
                    <th class="cl_cell_width">Created</th>
					<th>By</th>
                </tr>
                <?php
                $query = OCP\DB::prepare('select * from *PREFIX*polls_events');
                $result = $query->execute();
                $user = OCP\User::getUser();

                while ($row = $result->fetchRow()) {
                    echo '<tr>';
                    echo '<th class="cl_link">' . $row['title'] . '<input type="hidden" value="' . $row['id']  . '" /></th>';
                    $str = $row['description'];
                    if (strlen($str) > 60){
                        $str = substr($str, 0, 57) . '...';
                    }

                    echo '<td class="cl_cell_padd">'  . $str . '</td>';
                    echo '<td class="cl_cell_padd">' . $row['created'] . '</td>';
	                echo '<td class="cl_cell_padd">' . $row['owner'] . '</td>';

					if (strcmp($row['owner'], OCP\User::getUser()) == 0) {
						echo '<td id="id_del_' . $row['id'] . '" class="cl_delete">...delete</td>';
					}
				
                    echo '</tr>';
                }
                ?>
            </table>
        </td>
    </tr>
</table>
</form>


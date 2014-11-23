<?php
\OCP\Util::addStyle('polls', 'page0');
\OCP\Util::addScript('polls', 'page0');
?>

<form name="new_poll" method="POST">
	<input type="hidden" name="j" value="start"/>
	<input type="hidden" name="delete_id" value=""/>
	<input type="hidden" name="poll_id" value="" />
	<div class="goto_poll">
		<table class="cl_create_form">
			<!--<tr>
				<td colspan="4">-->
				<h1>Summary</h1>
				<!--</td>
			</tr>-->
			<tr>
				<th>Title</th>
				<th id="id_th_descr">Description</th>
				<th class="cl_cell_width">Created</th>
				<th>By</th>
				<th id="id_th_descr">Access (click for link)</th>
			</tr>

			<?php
			$query = OCP\DB::prepare('select * from *PREFIX*polls_events');
			$result = $query->execute();
			
			//print_r($result);
			//$rowfoo = $result->fetchRow();
			//echo count($rowfoo);
			//echo $rowfoo['title'];
			?>

			<?php while ($row = $result->fetchRow()) : ?>
				<tr>
					<td class="cl_link">
						<?php echo $row['title']; ?>
						<input type="hidden" value="<?php echo $row['id']; ?>" />
					</td>
					<?php
						$str = $row['description'];
						/*if (strlen($str) > 60){
							$str = substr($str, 0, 57) . '...';
						}*/
						$str = wordwrap($str, 50, "<br/>", true);
					?>
					<td><?php echo $str; ?></td>
					<?php //echo '<td>' . str_replace("_", " ", $row['created']) . '</td>'; ?>
					<td><?php echo date('d.m.Y H:i', $row['created']); ?></td>
					<td><?php echo OCP\User::getDisplayName($row['owner']); ?></td>

					<?php 
						// direct url to poll
						$url = \OCP\Util::linkToRoute('polls_index');
						$url = \OC_Helper::makeURLAbsolute($url);
						$url .= 'goto/' . $row['id'];
					?>
					<td class="cl_poll_url">
						<?php echo $row['access']; ?>
						<input type="hidden" value="<?php echo $url; ?>" />
					</td>
					<?php if (strcmp($row['owner'], OCP\User::getUser()) == 0) : ?>
						<td id="id_del_<?php echo $row['id']; ?>" class="cl_delete">...delete</td>
					<?php endif; ?>
				</tr>
			<?php endwhile; ?>
		</table>
	</div>
	<input type="submit" id="submit_new_poll" value="Create new poll" />
</form>
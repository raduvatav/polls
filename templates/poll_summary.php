<?php
\OCP\Util::addStyle('polls', 'page0');
\OCP\Util::addScript('polls', 'page0');
OCP\User::checkLoggedIn();
?>

<h1><?php p($l->t('Summary')); ?></h1>
<form name="new_poll" method="POST">
	<input type="hidden" name="j" value="start"/>
	<input type="hidden" name="delete_id" value=""/>
	<input type="hidden" name="poll_id" value="" />
	<div class="goto_poll">
		<table class="cl_create_form">
			<tr>
				<th><?php p($l->t('Title')); ?></th>
				<th id="id_th_descr"><?php p($l->t('Description')); ?></th>
				<th class="cl_cell_width"><?php p($l->t('Created')); ?></th>
				<th><?php p($l->t('By')); ?></th>
				<th><?php p($l->t('participated')); ?></th>
				<th id="id_th_descr"><?php p($l->t('Access')); ?></th>
			</tr>

			<?php
				$query = OCP\DB::prepare('select * from *PREFIX*polls_events order by created');
				$result = $query->execute();
			?>

			<?php while ($row = $result->fetchRow()) : ?>
				<?php  if (!userHasAccess($row['id'])) continue; ?>
				<tr>
					<td class="cl_link" title="Go to">
						<?php echo $row['title']; ?><input type="hidden" value="<?php echo $row['id']; ?>" />
					</td>
					<?php
						//$str = $row['description'];
						/*if (strlen($str) > 60){
							$str = substr($str, 0, 57) . '...';
						}*/
						//$str = wordwrap($str, 50, "<br/>", true);
					?>
					<td><div class="wordwrap"><?php echo $row['description']; ?></div></td>
					<?php //echo '<td>' . str_replace("_", " ", $row['created']) . '</td>'; ?>

					<?php
					// direct url to poll
					$url = \OCP\Util::linkToRoute('polls_index');
					$url = \OC_Helper::makeURLAbsolute($url);
					$url .= 'goto/' . $row['id'];
					?>

					<td class="cl_poll_url" title="Click to get link"><input type="hidden" value="<?php echo $url; ?>" /><?php echo date('d.m.Y H:i', $row['created']); ?></td>
					<td>
						<?php
							if($row['owner'] == OCP\User::getUser()) p($l->t('Yourself'));
							else echo OCP\User::getDisplayName($row['owner']); //echo OCP\User::getDisplayName($row['owner']);

						?>
					</td>
                    <td>
						<?php
							$partic_class = 'partic_no';
							for($i = 0; $i < count($partic_polls); $i++){
								if($row['id'] == $partic_polls[$i]['id']){
									$partic_class = 'partic_yes';
									array_splice($partic_polls, $i, 1);
									break;
								}
							}
						?>
						<div class="partic_all <?php echo $partic_class; ?>">
						</div>
						/
						<?php
							$partic_class = 'partic_no';
							for($i = 0; $i < count($partic_comm); $i++){
								if($row['id'] == $partic_comm[$i]['id']){
									$partic_class = 'partic_yes';
									array_splice($partic_comm, $i, 1);
									break;
								}
							}
						?>
						<div class="partic_all <?php echo $partic_class; ?>">
						</div>
                    </td>
					<td <?php if (strcmp($row['owner'], OCP\User::getUser()) == 0) echo 'class="cl_poll_access" title="Edit access"' ?> >
						<?php p($l->t($row['access'])); ?>
					</td>
					<?php if (strcmp($row['owner'], OCP\User::getUser()) == 0) : ?>
						<td id="id_del_<?php echo $row['id']; ?>" class="cl_delete"><?php p($l->t('delete')); ?></td>
					<?php endif; ?>
				</tr>
			<?php endwhile; ?>
		</table>
	</div>
	<input type="submit" id="submit_new_poll" value="<?php p($l->t('Create new poll')); ?>" />
</form>

<div id="dialog-box">
	<div id="dialog-message"></div>
	<table id="table_access">
		<tr>
			<td>
				<table id="table_groups">
					<tr>
						<th><?php p($l->t('Groups')); ?></th>
					</tr>
					<?php $groups = OC_Group::getUserGroups(OC_User::getUser()); ?>
					<?php foreach($groups as $gid) : ?>
						<tr>
							<td class="cl_group_item"><?php echo $gid; ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</td>
			<td>
				<table id="table_users">
					<tr>
						<th><?php p($l->t('Users')); ?></th>
					</tr>
					<?php $users = OC_User::getUsers(); ?>
					<?php foreach ($users as $uid) : ?>
						<tr>
							<td class="cl_user_item"><?php echo $uid; ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</td>
		</tr>
	</table>
	<a id="button_close_access"><?php p($l->t('Close')); ?></a>
</div>

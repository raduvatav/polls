<?php
	\OCP\Util::addStyle('polls', 'main');
	\OCP\Util::addScript('polls', 'select_dates');

	$arr = explode("\n", $desc);
?>
<table id="id_table_1">
	<tr>
	<th class="cl_title_header" rowspan="<?php count($arr); ?>"><em><div id="id_title"><?php echo $title; ?></div></em></th>

	<tr>
		<th class="cl_desc_header"><em><div class="wordwrap" id="id_descr"><?php echo $desc; //wordwrap($line, 50, "<br/>", true); ?></div></em></th>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
       <td><h2><?php p($l->t('Click on days to add or remove')); ?></h2></td>
       <td><h2><?php p($l->t('Select hour & minute, then click on time')); ?></h2></td>
	</tr>
	<tr>
		<td class="cl_pad_left">
			<table id="id_cal_table" class="cl_with_border">
			<tr>
				<th style="padding:0px" colspan="1">
					<a id="id_header_prev_month"><<</a>
				</th>
				<th id="id_header_curr_month" colspan="2"></th>
				<th id="id_header_curr_year" colspan="3"></th>
				<th style="padding:0px" colspan="1">
					<a id="id_header_next_month">>></a>
				</th>
			</tr>
			<tr>
				<th><?php p($l->t('Mon')); ?></th>
				<th><?php p($l->t('Tue')); ?></th>
				<th><?php p($l->t('Wed')); ?></th>
				<th><?php p($l->t('Thu')); ?></th>
				<th><?php p($l->t('Fri')); ?></th>
				<th><?php p($l->t('Sat')); ?></th>
				<th><?php p($l->t('Sun')); ?></th>
			</tr>
			<?php for ($i = 0; $i < 6; $i++) : ?>
				<tr>
					<?php for ($j = 0; $j < 7; $j++) : ?>
						<td id="id_cell_<?php echo $i.'_'.$j; ?>" ></td>
					<?php endfor; ?>
				</tr>
			<?php endfor; ?>
			</table>
		</td>
		<td class="cl_pad_left">
			<table id="id_time_table">
				<tr>
					<td>
						<table id="id_hours_table" >

	<?php // -------- hours ------ ?>
							<tr>
								<td class="cl_hour_selected" id="id_hour_0">00</td>
								<?php for ($i = 1; $i < 24; $i++) : ?>
									<?php $str = sprintf("%02d", $i); ?>
									<td class="cl_hour" id="id_hour_<?php echo $i; ?>"><?php echo $str; ?></td>
								<?php endfor; ?>
							</tr>

	<?php // -------- minutes ---- ?>
							<tr>
								<?php for ($i = 0; $i < 60; $i += 5) : ?>
									<?php $str = sprintf("%02d", $i); ?>
									<?php if($i === 0): ?>
										<td colspan="2" class="cl_min_selected" id="id_min_00">00</td>
									<?php else : ?>
										<td colspan="2" class="cl_min" id="id_min_<?php echo $str; ?>"><?php echo $str; ?></td>
									<?php endif; ?>
								<?php endfor; ?>
							</tr>

	<?php // -------- selected hour --- ?>
							<tr>
								<td colspan="8" > <?php p($l->t('click to add')); ?> ---></td>
								<td colspan="8" class="cl_time_display" id="id_time_display">00:00</td>
								<td colspan="8"><--- <?php p($l->t('click to add')); ?> </td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table  class="cl_with_border" id="id_poss_table">
	<?php // table
	// -------- entries ('possibilities', to be filled by js) ?>
							<tr id="id_poss_table_header_row"> <?php    // header row ?>
								<th><?php p($l->t('date\\time')); ?></th> <?php         // corner (date\time) ?>
							</tr>

						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<form name="finish_poll" method="POST">
			<input type="hidden" name="j" value="page2"/>
			<input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>" />
			<input type="hidden" name="chosen_dates" />
			<td colspan="2">
				<input type="button" id="submit_cancel_poll" value="<?php p($l->t('Cancel')); ?>" />
				<input type="button" id="submit_finish_poll" value="<?php p($l->t('Next')); ?>" />
			</td>
		</form>
	</tr>
</table>

<?php include 'footer.php'; ?>

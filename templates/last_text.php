<?php
\OCP\Util::addStyle('polls', 'page2');
\OCP\Util::addScript('polls', 'page2');

use \OCP\User;


$line = str_replace("\n", '<br>', $desc);

?>

<p>
	<?php if(!User::isLoggedIn()) : ?>
	<?php p($l->t('Already have an account?')); ?>
	<?php $url = OC_Helper::linkToAbsolute( '', 'index.php' ).'?redirect_url='.OCP\Util::linkToRoute('polls_goto', array('poll_id' => $poll_id)); ?>
	<a href="<?php echo $url; ?>"><?php p($l->t('Login')); ?></a>
<?php endif; ?>
</p>

<h1><?php echo $title; ?></h1>

<h2><?php p($l->t('Description')); ?></h2>
<div class="wordwrap desc"><?php echo $line; ?></div>

<?
// -------------- url ---------------

?>
<h2><?php p($l->t('poll URL')); ?></h2>
<p class="url">
	<?php
	$url = \OCP\Util::linkToRoute('polls_index');
	$url = \OC_Helper::makeURLAbsolute($url);
	$url .= 'goto/' . $poll_id;
	?>
	<a href="<?php echo $url;?>"><?php echo $url; ?></a>
	<?php //echo '</td></tr>'; ?>
</p>


<div class="scroll_div">
	<table class="cl_table_1" id="id_table_1"> <?php //from above title ?>

		<tr>
			<th>&nbsp;</th> <?php // upper left header rectangle ?>

			<?php foreach ($items as $el) : ?>
				<th title="<?php echo $el->desc ?>"><?php echo $el->dt; ?></th>
			<?php endforeach; ?>

		</tr>
		<?php
		// init array for counting 'yes'-votes for each dt
		$total_y = array();
		$total_n = array();
		for ($i = 0; $i < count($items); $i++){
			$total_y[$i] = 0;
			$total_n[$i] = 0;
		}
		$user_voted = null;
		// -------------- other users ---------------
		// loop over users
		?>
		<?php

		if (isset($others)) {
			echo '<tr>';

			foreach (array_keys($others) as $usr) {
				if ($usr === User::getUser()) {
					$user_voted = $others[$usr];
					continue;
				}
				echo '<th>' . User::getDisplayName($usr) . '</th>';
				$i_tot = -1;
				// loop over dts
				foreach ($items as $el) {
					$i_tot++;

					$cl = 'cl_maybe';

					$arr = $others[$usr];

					$str = $el->dt;

					// look what user voted for this dts
					foreach ($others[$usr] as $obj) {
						if ($str === $obj->dt) {
							if ($obj->ok === 'yes') {
								$cl = 'cl_yes';
								$total_y[$i_tot]++;
							} else if ($obj->ok === 'no') {
								$total_n[$i_tot]++;
								$cl = 'cl_no';
							}
							break;
						}
					}

					//echo '<td class="' . $cl . '">&nbsp';
					echo '<td class="' . $cl . '">' . $obj->dt;
					echo '<input type="hidden" value="' . $str . '" />';
					echo '</td>';
				}
			}
			echo '</tr>';
		}

	// -------------- current user --------------
	?>

		<tr>

			<?php
			if (User::isLoggedIn()) {
				echo '<th>' . User::getDisplayName() . '</th>';
			}
			else {
				echo '<th id="id_ac_detected" ><input type="text" name="user_name" id="user_name" /></th>';
			}
			$i_tot = -1;
			foreach ($items as $el) {
				$i_tot++;
				$str = $el->dt;

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
			?>
		</tr>
		<?php // --------------- total -------------------- ?>
		<tr>
			<th><?php p($l->t('Total')); ?>:</th>
			<?php for ($i = 0; $i < count($items); $i++) :
				echo '<td><table id="id_tab_total"><tr>';
				echo '<td id="id_y_' . $i . '" class="cl_total_y">' . (isset($total_y[$i]) ? $total_y[$i] : '0') . '</td>';
				echo '<td id="id_n_' . $i . '" class="cl_total_n">' . (isset($total_n[$i]) ? $total_n[$i] : '0') . '</td>';
				echo '</tr></table></td>';
			endfor; ?>
		</tr>

	</table>
</div>


<table class="cl_comment">
	<?php // -------- leave comment ---------- ?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th><?php p($l->t('Comment')); ?></th>
		<td><textarea cols="50" rows="5" id="comment_box"></textarea></td>
	</tr>
	<?php // -------- submit ----------- ?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<?php // linkToRoute is to remove "/goto" if user came here directly ?>
		<form name="finish_poll" action="<?php echo \OCP\Util::linkToRoute('polls_index'); ?>" method="POST">
			<input type="hidden" name="j" id="j" value="finish" />
			<input type="hidden" name="poll_type" value="text" />
			<input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>" />
			<input type="hidden" name="options" />
			<td colspan="2" style "background-color: white">
			<input type="button" id="submit_finish_poll" value="<?php p($l->t('Send')); ?>" />
			<input type="button" id="button_home" value="<?php p($l->t('Home')); ?>" />
			</td>
		</form>
	</tr>
</table>
<?php // -------- comments ---------- ?>
<h2><?php p($l->t('Comments')); ?></h2>
<table class="cl_user_comments">
	<?php if (isset($comments)) : ?>
	<?php foreach ($comments as $obj) : ?>
		<tr>
			<th>
				<div id="id_user_name"><?php echo \OCP\User::getDisplayName($obj->user); ?>:</div>
				<div id="id_user_dt"><?php echo date('d.m.Y H:i', $obj->dt); ?></div>
			</th>
			<td>
				<div class="wordwrap">
					<?php echo $obj->comment; //wordwrap(str_replace("\n", "<br>", $obj->comment), 100, "<br/>", true); ?>
				</div>
			</td>
		</tr>
	<?php endforeach; ?>
	<?php endif; ?>
</table>
<?php
\OCP\Util::addStyle('polls', 'main');
\OCP\Util::addScript('polls', 'last');

use \OCP\DB;
use \OCP\User;

// if this happens during poll creation, $expired doesn't exist
if (!isset($expired)) $expired = false;

if ($poll_type === 'datetime') {
// count how many times in each date
	$arr_dates = null;  // will be like: [21.02] => 3
	$arr_years = null;  // [1992] => 6

	for ($i = 0; $i < count($chosen); $i++) {
		//$ch_obj = $chosen[$i];
		$arr = explode('.', $chosen[$i]->date);
		$day_month = $arr[0] . '.' . $arr[1] . '.'; // 21.02
		$year = $arr[2];                      // 1992

		if (isset($arr_dates[$day_month])) {
			$arr_dates[$day_month] += 1;
		} else {
			$arr_dates[$day_month] = 1;
		}

		// -----
		if (isset($arr_years[$year])) {
			$arr_years[$year] += 1;
		} else {
			$arr_years[$year] = 1;
		}

	}

	$for_string_dates = '';
	foreach (array_keys($arr_dates) as $dt) {                           // date (13.09)
		$for_string_dates .= '<th colspan="' . $arr_dates[$dt] . '">' . $dt . '</th>';
	}

	$for_string_years = '';
	foreach (array_keys($arr_years) as $year) {                         // year (1992)
		$for_string_years .= '<th colspan="' . $arr_years[$year] . '">' . $year . '</th>';
	}

}


$line = str_replace("\n", '<br>', $desc);

// ----------- title / descr --------
?>

	<?php if(!User::isLoggedIn()) : ?>
<p>
		<header>
		        <div id="header">
				<a href="<?php print_unescaped(link_to('', 'index.php')); ?>"
        	                	title="" id="owncloud">
					<div class="logo-wide svg"></div>
				</a>
	                	<div id="logo-claim" style="display:none;"><?php p($theme->getLogoClaim()); ?></div>
        	        	<div class="header-right">
					<?php p($l->t('Already have an account?')); ?>
					<?php $url = OC_Helper::linkToAbsolute( '', 'index.php' ).'?redirect_url='.OCP\Util::linkToRoute('polls_goto', array('poll_id' => $poll_id)); ?>
					<a href="<?php echo $url; ?>"><?php p($l->t('Login')); ?></a>
				</div>
			</div>
		</header>
</p>
		<p>&nbsp;</p><p>&nbsp;</p> <?php // for some reason the header covers the title otherwise ?>
	<?php endif; ?>


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
			<th <?php if ($poll_type === 'datetime') echo 'rowspan="3"'; ?>>&nbsp;</th> <?php // upper left header rectangle ?>

		<?php
		if ($poll_type === 'datetime') {
			echo $for_string_years;
		}
		else {
			foreach ($chosen as $el) {
				echo '<th title="' . $el->desc . '">' . $el->dt . '</th>';
			}
		}
		?>


		</tr>
		<?php
		if ($poll_type === 'datetime'){
			echo '<tr>' .  $for_string_dates . '</tr>';

			echo '<tr>';
			for ($i = 0; $i < count($chosen); $i++) {
				$ch_obj = $chosen[$i];
				echo '<th>' . $ch_obj->time . '</th>';
			}
			echo '</tr>';
		}

		// init array for counting 'yes'-votes for each dt
		$total_y = array();
		$total_n = array();
		//$total_m = array();
		for ($i = 0; $i < count($chosen); $i++){
			$total_y[$i] = 0;
			$total_n[$i] = 0;
			//$total_m[$i] = 1;
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
					// if poll expired, just puth current user among the others;
					// otherwise skip here to add current user as last row (to vote)
					if (!$expired) {
						$user_voted = $others[$usr];
						continue;
					}
				}
				echo '<th>' . User::getDisplayName($usr) . '</th>';
				$i_tot = -1;

				// loop over dts
				foreach($chosen as $dt) {

					$i_tot++;

					$cl = 'cl_maybe';

					$arr = $others[$usr];
					if ($poll_type === 'datetime') {
						$str = $dt->date . '_' . $dt->time;
					}
					else {
						$str = $dt->dt;
					}


					// look what user voted for this dts
					foreach ($others[$usr] as $obj) {
						if ($str === $obj->dt) {

							if ($obj->ok === 'yes') {
								$cl = 'cl_yes';
								$total_y[$i_tot]++;
							}
							else if ($obj->ok === 'no') {
								$total_n[$i_tot]++;
								$cl = 'cl_no';
							}/* else {
								$total_m[$i_tot]++;
							}*/
							break;
						}
					}

					if ($poll_type === 'datetime') {
						echo '<td class="' . $cl . '">';
					}
					else {
						echo '<td class="' . $cl . '">';
					}
					echo '<input type="hidden" value="' . $str .   '" />';
					echo '</td>';
				}
				echo '</tr>';
			}

		}
		
		// -------------- current user --------------
		?>

		<tr>

		<?php
		if (!$expired) {
			if (User::isLoggedIn()) {
				echo '<th>' . User::getDisplayName() . '</th>';
			} else {
				echo '<th id="id_ac_detected" ><input type="text" name="user_name" id="user_name" /></th>';
			}
			$i_tot = -1;
			foreach ($chosen as $dt) {
				$i_tot++;
				if ($poll_type === 'datetime') {
					$str = $dt->date . '_' . $dt->time;
				} else {
					$str = $dt->dt;
				}

				// see if user already has data for this event
				$cl = 'cl_maybe';
				if (isset($user_voted)) {
					foreach ($user_voted as $obj) {
						if ($obj->dt === $str) {
							if ($obj->ok === 'yes') {
								$cl = 'cl_yes';
								$total_y[$i_tot]++;
							} else if ($obj->ok === 'no') {
								$cl = 'cl_no';
								$total_n[$i_tot]++;
							}/* else {
							$total_m[$i_tot]++;
						}*/
						}
					}
				}

				echo '<td class="cl_click ' . $cl . '">&nbsp;';

				echo '<input type="hidden" value="' . $str . '" />';
				echo '</td>';
			}
		}
		?>
		</tr>
		<?php // --------------- total -------------------- ?>

		<?php
			$diff_array = $total_y;
			for($i = 0; $i < count($diff_array); $i++){
				$diff_array[$i] = ($total_y[$i] - $total_n[$i]);
			}
			$max_votes = max($diff_array);
			//$max_votes = max($total_y);
		?>
		<tr>
			<th><?php p($l->t('Total')); ?>:</th>
			<?php for ($i = 0; $i < count($chosen); $i++) : ?>
				<td>
					<table id="id_tab_total">
						<tr>
							<td id="id_y_<?php echo $i; ?>" class="cl_total_y">
								<?php if(isset($total_y[$i])) : ?>
									<?php if( $total_y[$i] - $total_n[$i] == $max_votes) : ?>
											<?php echo '<strong>' . $total_y[$i] . '</strong>'; ?>
									<?php else : ?>
										<?php echo $total_y[$i]; ?>
									<?php endif; ?>
								<?php else : ?>
									0
								<?php endif; ?>
							</td>
						</tr>
						<!--<tr>
							<td id="id_m_<?php echo $i; ?>" class="cl_total_m"><?php echo isset($total_m[$i]) ? $total_m[$i] : '0'; ?></td>
						</tr>-->
						<tr>
							<td id="id_n_<?php echo $i; ?>" class="cl_total_n"><?php echo isset($total_n[$i]) ? $total_n[$i] : '0'; ?></td>
						</tr>
					</table>
				</td>
			<?php endfor; ?>
		</tr>

		<?php // ------------ winner ----------------------- ?>
		<tr>
			<th><?php p($l->t('Win:')); ?></th>
			<?php for ($i = 0; $i < count($chosen); $i++) :

				$str = '';

				if ($total_y[$i] - $total_n[$i] == $max_votes){
					$str = '&#10004;';
				}

				echo '<td class="win_row" id="id_total_' . $i . '">' . $str . '</td>';

			endfor; ?>
		</tr>


	</table>
</div>

<?php if($expired) : ?>
<div id="expired_info">
	<h2><?php p($l->t('Poll expired')); ?></h2>
	<p>
		<?php p($l->t('The poll expired on %s. Voting is disabled, but you can still comment.', array(date('d.m.Y H:i',$expire)))); ?>
	</p>
</div>
<?php endif; ?>

<table class="cl_comment">
	<?php // -------- leave comment ---------- ?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<th><?php p($l->t('Comment')); ?></th>
		<td><textarea style="width: 200px;" cols="50" rows="5" id="comment_box"></textarea></td>
	</tr>
	<?php // -------- submit ----------- ?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<?php // -------- email notification -----
		// only display checkbox if user is logged in (not for public/anon)
		if (User::isLoggedIn()) {
			$notif_checked = false;
			$query = DB::prepare('SELECT user FROM *PREFIX*polls_notif WHERE id=?');
			$result = $query->execute(array($poll_id));
			while($row = $result->fetchRow()) {
				if ($row['user'] === User::getUser()){
					$notif_checked = true;
					break;
				}
			}
		}
		if (User::isLoggedIn()) :
			// --- checkbox "receive notif" ---
		?>
		<td colspan="2" style "background-color: white">
			<input type="checkbox" id="check_notif" <?php if($notif_checked) p(' checked'); ?> />
			<label for="check_notif"><?php p($l->t('Receive notification email on activity')); ?></label>
		</td>
		<?php endif; ?>

	</tr>
	<tr>
		<?php // linkToRoute is to remove "/goto" if user came here directly ?>
		<form name="finish_poll" action="<?php echo \OCP\Util::linkToRoute('polls_index'); ?>" method="POST">
			<input type="hidden" name="j" id="j" value="finish" />
			<input type="hidden" name="poll_type" value="<?php echo $poll_type; ?>" />
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
					<?php echo $obj->comment; ?>
				</div>
			</td>
		</tr>
	<?php endforeach; ?>
	<?php endif; ?>
</table>

<?php include 'footer.php'; ?>

<?php
\OCP\Util::addStyle('polls', 'main');
\OCP\Util::addScript('polls', 'select_text_items');

$arr = explode("\n", $desc);
?>
<table>
	<tr>
		<th class="cl_title_header" rowspan="<?php count($arr); ?>"><em><div id="id_title"><?php echo $title; ?></div></em></th>

	<tr>
		<th class="cl_desc_header"><em><div class="wordwrap" id="id_descr"><?php echo $desc; ?></div></em></th>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>

	<tr>
		<td class="cl_pad_left">
			<table>
				<tr>
					<td><em><?php p($l->t('Text item')); ?></em></td>
				</tr>
				<tr>
					<td><input type="text" style="width: 200px;" class="input_field" id="input_text_item" name="text_title"/></td>
					<td class="cl_pad_left" rowspan="2"><input type="button" id="id_add_text_item" value="add ->"></td>
				</tr>
				<tr>
					<td><em><?php p($l->t('Description')); ?></em></td>
				</tr>
				<tr>
					<td><textarea cols="50" rows="3" style="width: 200px;" class="input_field" id="input_text_desc" name="text_desc"></textarea></td>
				</tr>
			</table>
		</td>
		<td class="cl_pad_left">
			<table id="id_table_text_items">
				<tr>
					<th>&nbsp;</th>
					<th><em><?php p($l->t('Text item')); ?></em></th>
					<th><em><?php p($l->t('Description (will be shown as tooltip on the summary page)')); ?></em></th>
				</tr>

			</table>
		</td>
	</tr>

	<tr>
		<form name="finish_poll" method="POST">
			<input type="hidden" name="j" value="page2_text"/>
			<input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>" />
			<input type="hidden" name="items" />
			<td colspan="2">
				<input type="button" id="submit_finish_poll" value="<?php p($l->t('Next')); ?>" />
			</td>
		</form>
	</tr>

</table>
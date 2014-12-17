<?php
	\OCP\Util::addStyle('polls', 'main');
	\OCP\Util::addScript('polls', 'start');
?>

<form name="create_poll" method="POST">
	<input type="hidden" name="j" value="page1"/>

	<div class="new_poll">
		<h1><?php p($l->t('Create new poll')); ?></h1>
		<label for="text_title" class="label_h1 input_title"><?php p($l->t('Title')); ?></label>
		<input type="text" class="input_field" id="text_title" name="text_title"/>
		<label for="text_desc" class="label_h1 input_title"><?php p($l->t('Description')); ?></label>
		<textarea cols="50" rows="5" style="width: auto;" class="input_field" id="text_desc" name="text_desc"></textarea>

		<div class="input_title"><?php p($l->t('Access')); ?></div>

		<input type="radio" name="radio_pub" id="private" value="registered" checked />
		<label for="private"><?php p($l->t('Registered users only')); ?></label>
		
		<input type="radio" name="radio_pub" id="hidden" value="hidden" />
		<label for="hidden"><?php p($l->t('hidden')); ?></label>

		<input type="radio" name="radio_pub" id="public" value="public">
		<label for="public"><?php p($l->t('Public access')); ?></label>

		<input type="radio" name="radio_pub" id="select" value="select">
		<label for="select"><?php p($l->t('Select')); ?></label>
		<span id="id_label_select">...</span>

		<input type="hidden" name="access_ids" value="" />


		<div class="input_title"><?php p($l->t('Type')); ?></div>

		<input type="radio" name="radio_type" id="event" value="event" checked />
		<label for="event"><?php p($l->t('Event schedule')); ?></label>

		<input type="radio" name="radio_type" id="text" value="text">
		<label for="text"><?php p($l->t('Text based')); ?></label>


		<br />
		<input id="id_expire_set" name="check_expire" type="checkbox" value="false">
		<label for="id_expire_set"><?php p($l->t('Expires')); ?></label>

		<input id="id_expire_date" type="text" required="" value="" name="expire_date" disabled="true">

        <br/>
		<input type="submit" id="submit_cancel_poll" value="<?php p($l->t('Cancel')); ?>" />
		<input type="submit" id="submit_create_poll" value="<?php p($l->t('Next')); ?>" />
	</div>

	<?php include 'access_dialog.php'; ?>

</form>

<?php include 'footer.php'; ?>

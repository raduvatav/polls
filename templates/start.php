<?php
\OCP\Util::addStyle('polls', 'page0');
\OCP\Util::addScript('polls', 'page0');
?>

<form name="create_poll" method="POST">
	<input type="hidden" name="j" value="page1"/>

	<div class="new_poll">
		<h1>Create new poll</h1>
		<label for="text_title" class="label_h1 input_title">Title</label>
		<input type="text" id="text_title" name="text_title"/>
		<label for="text_desc" class="label_h1 input_title">Description</label>
		<textarea cols="50" rows="5" id="text_desc" name="text_desc"></textarea>

		<div class="input_title">Access</div>

		<input type="radio" name="radio_pub" id="private" value="registered" checked />
		<label for="private">Registered users only</label>

		<input type="radio" name="radio_pub" id="public" value="public">
		<label for="public">Public access</label>

		<input type="submit" id="submit_create_poll" value="...next" />
	</div>
</form>


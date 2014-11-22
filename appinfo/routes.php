<?php
/**
 * Copyright (c) 2014, Radu Vatav
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

/** @var $this \OCP\Route\IRouter */
$this->create('polls_index', '/')
	->actionInclude('polls/index.php');

$this->create('polls_goto', '/goto/{poll_id}')
//	->post()
	->actionInclude('polls/index.php');


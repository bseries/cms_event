<?php
/**
 * Bureau Event
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\core\Environment;
use lithium\g11n\Message;

extract(Message::aliases());

Environment::set(true, array(
	'modules' => array(
		'events' => array(
			'library' => 'cms_event', 'title' => $t('Events'), 'name' => 'events', 'slug' => 'events'
		)
	)
));

?>
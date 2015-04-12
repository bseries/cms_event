<?php
/**
 * CMS Event
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use base_core\extensions\cms\Jobs;
use cms_event\models\Events;

Jobs::recur('cms_event:stream', function() {
	Events::poll();
}, [
	'frequency' => Jobs::FREQUENCY_MEDIUM
]);

?>
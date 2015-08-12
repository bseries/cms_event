<?php
/**
 * CMS Event
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace cms_event\config;

use base_core\extensions\cms\Settings;

Settings::register('service.artistData.default', [
	'stream' => false,
	'autopublish' => false,
	'username' => null
]);

Settings::register('event.enableTickets', false);

?>
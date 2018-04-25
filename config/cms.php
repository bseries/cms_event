<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace cms_event\config;

use base_core\extensions\cms\Settings;

Settings::register('service.artistData.default', [
	'stream' => false,
	'autopublish' => false,
	'username' => null
]);

Settings::register('event.enableTickets', false);
Settings::register('event.enableTime', false);

?>
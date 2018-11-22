<?php
/**
 * Copyright 2018 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace cms_event\config;

use base_core\extensions\cms\Settings;

Settings::register('service.bandsintown.default', [
	'appId' => null,
	'stream' => false,
	'autopublish' => false,
	'site' => null
]);

?>
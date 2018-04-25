<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace cms_event\config;

use base_core\async\Jobs;
use cms_event\models\Events;

Jobs::recur('cms_event:stream', function() {
	return Events::poll();
}, [
	'frequency' => Jobs::FREQUENCY_MEDIUM
]);

?>
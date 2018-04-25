<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace cms_event\config;

use base_core\extensions\cms\Panes;
use lithium\g11n\Message;

extract(Message::aliases());

Panes::register('cms.events', [
	'title' => $t('Events', ['scope' => 'cms_event']),
	'url' => ['controller' => 'events', 'action' => 'index', 'library' => 'cms_event', 'admin' => true],
	'weight' => 50
]);

?>
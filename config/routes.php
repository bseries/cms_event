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

use lithium\net\http\Router;

$persist = ['persist' => ['admin', 'controller']];

Router::connect('/admin/events/{:id:[0-9]+}', [
	'controller' => 'events', 'library' => 'cms_event', 'action' => 'view', 'admin' => true
], $persist);
Router::connect('/admin/events/{:action}', [
	'controller' => 'events', 'library' => 'cms_event', 'admin' => true
], $persist);
Router::connect('/admin/events/{:action}/{:id:[0-9]+}', [
	'controller' => 'events', 'library' => 'cms_event', 'admin' => true
], $persist);

?>
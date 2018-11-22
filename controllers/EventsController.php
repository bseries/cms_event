<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */
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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace cms_event\controllers;

use base_core\extensions\cms\Settings;
use base_core\models\Timezones;
use cms_event\models\Events;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

class EventsController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;
	use \base_core\controllers\AdminDeleteTrait;
	use \base_core\controllers\AdminPublishTrait;
	use \base_core\controllers\AdminPromoteTrait;
	use \base_core\controllers\DownloadTrait;

	public function admin_export_ical() {
		$item = Events::find('first', [
			'conditions' => [
				'id' => $this->request->id
			]
		]);
		$this->_renderDownload(
			$stream = $item->exportAsICal(),
			'text/calendar',
			'UTF-8'
		);
		fclose($stream);
	}

	public function admin_poll() {
		set_time_limit(60 * 5);

		extract(Message::aliases());

		if (Events::poll()) {
			FlashMessage::write($t('Successfully polled.', ['scope' => 'cms_event']), [
				'level' => 'success'
			]);
		} else {
			FlashMessage::write($t('Failed polling.', ['scope' => 'cms_event']), [
				'level' => 'error'
			]);
		}
		return $this->redirect(['action' => 'index', 'library' => 'cms_event']);
	}

	protected function _selects($item = null) {
		if ($item) {
			$timezones = Timezones::find('list');
		} else {
			$usePolling = Events::hasExternalSources();
		}
		$useTicketing = Settings::read('event.useTicketing');

		return compact('timezones', 'usePolling', 'useTicketing');
	}
}

?>
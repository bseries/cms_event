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

namespace cms_event\controllers;

use cms_event\models\Events;

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
		$stream = $item->exportAsICal();

		$this->_renderDownload(
			$this->_downloadBasename(
				null,
				'event',
				$item->id . '.ics'
			),
			$stream
		);
		fclose($stream);
	}
}

?>
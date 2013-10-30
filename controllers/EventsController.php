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

namespace cms_event\controllers;

use cms_film\models\Films;
use cms_event\models\Events;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\core\Environment;

class EventsController extends \lithium\action\Controller {

	public function admin_index() {

		$query = [
			'with' => ['CoverMedia'],
			'order' => ['created' => 'DESC']
		];
		$data = Events::find('all', $query);
		return compact('data');
	}

	public function admin_add() {
		extract(Message::aliases());

		$item = Events::create();

		if ($this->request->data) {
			if ($item->save($this->request->data)) {
				FlashMessage::write($t('Successfully saved.'));
				return $this->redirect(['action' => 'index', 'library' => 'cms_event']);
			} else {
				FlashMessage::write($t('Failed to save.'));
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selectData();
	}

	public function admin_edit() {
		extract(Message::aliases());

		$item = Events::find($this->request->id);

		if ($this->request->data) {
			if ($item->save($this->request->data)) {
				FlashMessage::write($t('Successfully saved.'));
				return $this->redirect(['action' => 'index', 'library' => 'cms_event']);
			} else {
				FlashMessage::write($t('Failed to save.'));
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selectData();
	}

	protected function _selectData() {
		$features = Environment::get('features');

		if (!$features['connectFilmsWithEvents']) {
			return [];
		}
		$films = Films::find('list');

		return compact('films');
	}

	public function admin_delete() {
		extract(Message::aliases());

		$item = Events::find($this->request->id);
		$item->delete();
		FlashMessage::write($t('Successfully deleted.'));

		return $this->redirect(['action' => 'index', 'library' => 'cms_event']);
	}
}

?>
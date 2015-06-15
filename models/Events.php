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

namespace cms_event\models;

use lithium\analysis\Logger;
use lithium\core\Environment;
use lithium\util\Validator;
use lithium\util\Set;
use DateTime;
use lithium\g11n\Message;
use base_core\extensions\cms\Settings;
use cms_event\models\ArtistDataShows;

class Events extends \base_core\models\Base {

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'base_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	protected static $_actsAs = [
		'base_media\extensions\data\behavior\Sluggable',
		'base_media\extensions\data\behavior\Coupler' => [
			'bindings' => [
				'cover' => [
					'type' => 'direct',
					'to' => 'cover_media_id'
				]
			]
		],
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'title',
				'tags'
			]
		]
	];

	public static function init() {
		$model = static::_object();
		extract(Message::aliases());

		$model->validates['title'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('This field cannot be left blank.', ['scope' => 'cms_event'])
			]
		];
		$model->validates['start'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('This field cannot be left blank.', ['scope' => 'cms_event'])
			]
		];
		$model->validates['tags'] = [
			[
				'noSpacesInTags',
				'on' => ['create', 'update'],
				'required' => false,
				'message' => $t('Spaces cannot be used inside tags.', ['scope' => 'cms_event'])
			]
		];
		Validator::add('noSpacesInTags', function($value, $format, $options) {
			return empty($value) || preg_match('/^([a-z0-9]+)(\s?,\s?[a-z0-9]+)*$/i', $value);
		});
	}

	// Start and end date are inclusive.
	public static function current(array $query = []) {
		return static::find('all', Set::merge([
			'conditions' => [
				'OR' => [
					// Either the event has no end date, then
					// its active just on the current day.
					[
						'start' => date('Y-m-d'),
						'end' => null
					],
					// Or it has an end date and is in range.
					[
						'start' => ['<=' => date('Y-m-d')],
						'end' => ['>=' => date('Y-m-d')],
					]
				]
			],
			'order' => ['start' => 'DESC']
		], $query));
	}

	public static function previous(array $query = []) {
		return static::find('all', Set::merge([
			'conditions' => [
				'OR' => [
					// Either the event has no end date, then
					// its active just on the current day.
					[
						'start' => ['<' => date('Y-m-d')],
						'end' => null
					],
					// Or it has an end date and is in range.
					[
						'end' => ['<' => date('Y-m-d')],
					]
				]
			],
			'order' => ['start' => 'DESC']
		], $query));
	}

	public static function poll() {
		$settings = Settings::read('service.artistData');

		foreach ($settings as $s) {
			if ($s['stream']) {
				static::_pollArtistData($s);
			}
		}
	}

	public static function upcoming(array $query = []) {
		return static::find('all', Set::merge([
			'conditions' => [
				// Once start is equal today event becomes current.
				'start' => ['>' => date('Y-m-d')],
				// Assumes that if start didn't happen already
				// end also didn't happen, as start should be before end.
			],
			'order' => ['start' => 'DESC']
		], $query));
	}

	protected static function _pollArtistData($config) {
		$results = ArtistDataShows::all($config);

		if (!$results) {
			return $results;
		}
		foreach ($results as $result) {
			$item = Events::find('first', [
				'conditions' => [
					'title' => $result->title,
					'location' => $result->location,
					'start' => $result->start,
					'end' => $result->end
				]
			]);
			if (!$item) {
				$item = Events::create([
					// Moved here as when autopublish is enabled it would otherwise
					// force manually unpublised items to become published again.
					'is_published' => $config['autopublish']
				]);
			}
			if (!$item->save($result->data())) {
				Logger::notice('Failed to save artist data event: '. var_export($item->data(), true));
			}
		}
	}

	// Canonical sort date.
	public function date($entity) {
		return $this->start($entity);
	}

	public function start($entity) {
		return DateTime::createFromFormat('Y-m-d', $entity->start);
	}

	public function end($entity) {
		return $entity->end ? DateTime::createFromFormat('Y-m-d', $entity->end) : null;
	}

	// FIXME Handle end/start ranges
	public function isPrevious($entity) {
		$now = new DateTime();
		return $now->diff($entity->start())->days < 0;
	}

	public function isUpcoming($entity) {
		$now = new DateTime();
		return $now->diff($entity->start())->days > 0;
	}
}
Events::init();

?>
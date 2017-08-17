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

namespace cms_event\models;

use DateTime;
use Eluceo\iCal\Component\Calendar as iCalCalendar;
use Eluceo\iCal\Component\Event as iCalEvent;
use base_core\extensions\cms\Settings;
use cms_event\models\ArtistDataShows;
use lithium\analysis\Logger;
use lithium\core\Environment;
use lithium\g11n\Message;
use lithium\util\Set;

class Events extends \base_core\models\Base {

	public $belongsTo = [
		'Owner' => [
			'to' => 'base_core\models\Users',
			'key' => 'owner_id'
		],
		'CoverMedia' => [
			'to' => 'base_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	public $hasMany = [
		'Invites' => [
			'to' => 'cms_rsvp\models\Invites',
			'key' => 'event_id'
		]
	];

	protected $_actsAs = [
		'base_core\extensions\data\behavior\Ownable',
		'base_core\extensions\data\behavior\Sluggable',
		'base_media\extensions\data\behavior\Coupler' => [
			'bindings' => [
				'cover' => [
					'type' => 'direct',
					'to' => 'cover_media_id'
				],
				'media' => [
					'type' => 'joined',
					'to' => 'base_media\models\MediaAttachments'
				],
				'bodyMedia' => [
					'type' => 'inline',
					'to' => 'body'
				]
			]
		],
		'base_core\extensions\data\behavior\Timestamp',
		'li3_taggable\extensions\data\behavior\Taggable' => [
			'field' => 'tags',
			'tagsModel' => 'base_tag\models\Tags',
			'filters' => ['strtolower']
		],
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'Owner.name',
				'Owner.number',
				'title',
				'tags'
			]
		]
	];

	public static function init() {
		// Deprecated / FC
		if (!static::hasField('is_promoted')) {
			trigger_error('Field is_promoted is missing, you may add it now (or not), it becomes required in 1.5.', E_USER_NOTICE);
		}
		if (static::schema('start')['type'] !== 'datetime') {
			trigger_error('Field start has not type datetime, you may change it now (or not), it becomes required in 1.5.', E_USER_NOTICE);
		}
		if (static::schema('end')['type'] !== 'datetime') {
			trigger_error('Field end has not type datetime, you may change it now (or not), it becomes required in 1.5.', E_USER_NOTICE);
		}
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
				'message' => $t('Tags cannot contain spaces.', ['scope' => 'cms_event'])
			]
		];

		if (PROJECT_LOCALE !== PROJECT_LOCALES) {
			static::bindBehavior('li3_translate\extensions\data\behavior\Translatable', [
				'fields' => ['title', 'teaser', 'body'],
				'locale' => PROJECT_LOCALE,
				'locales' => explode(' ', PROJECT_LOCALES),
				'strategy' => 'inline'
			]);
		}

		// Start and end date are inclusive.
		static::finder('current', function($self, $params, $chain) {
			if (isset($params['options']['conditions']['or']) || isset($params['options']['conditions']['OR'])) {
				trigger_error('Potential query conditions overlap.', E_USER_WARNING);
			}
			$params['options']['conditions']['OR'] = [
				// Either the event has no end date, then
				// its active just on the current day.
				[
					'AND' => [
						'DATE(start)' => date('Y-m-d'),
						'end' => null
					]
				],
				// Or it has an end date and is in range.
				[
					'AND' => [
						'start' => ['<=' => date('Y-m-d')],
						'end' => ['>=' => date('Y-m-d')],
					]
				]
			];
			return $chain->next($self, $params, $chain);
		});

		static::finder('previous', function($self, $params, $chain) {
			if (isset($params['options']['conditions']['or']) || isset($params['options']['conditions']['OR'])) {
				trigger_error('Potential query conditions overlap.', E_USER_WARNING);
			}
			$params['options']['conditions']['OR'] = [
				// Either the event has no end date, then
				// its active just on the current day.
				[
					'AND' => [
						'start' => ['<' => date('Y-m-d')],
						'end' => null
					]
				],
				// Or it has an end date and is in range.
				[
					'end' => ['<' => date('Y-m-d')],
				]
			];
			return $chain->next($self, $params, $chain);
		});

		static::finder('upcoming', function($self, $params, $chain) {
			if (isset($params['options']['conditions']['start'])) {
				trigger_error('Potential query conditions overlap.', E_USER_WARNING);
			}
			$params['options']['conditions']['start'] = [
				// Once start is equal today event becomes current.
				'>' => date('Y-m-d')
				// Assumes that if start didn't happen already
				// end also didn't happen, as start should be before end.
			];
			return $chain->next($self, $params, $chain);
		});
	}

	public static function poll() {
		foreach (Settings::read('service.artistData') as $s) {
			if ($s['stream']) {
				// FIXME Disabled as AD changed API endpoint, re-enable once
				// we have refitted onto new API.
				// static::_pollArtistData($s);
			}
		}
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
		return DateTime::createFromFormat('Y-m-d H:i:s', $entity->start);
	}

	public function end($entity) {
		return $entity->end ? DateTime::createFromFormat('Y-m-d H:i:s', $entity->end) : null;
	}

	public function hasStartTime($entity) {
		if (!$entity->start || !Settings::read('event.enableTime')) {
			return false;
		}
		return !preg_match('/00:00:00$/', $entity->start);
	}

	public function hasEndTime($entity) {
		if (!$entity->end || !Settings::read('event.enableTime')) {
			return false;
		}
		return !preg_match('/00:00:00$/', $entity->end);
	}

	public function isPrevious($entity) {
		$now = new DateTime();

		if ($entity->end) {
			return $now->diff($entity->start())->days < 0;
		}
		return $now->diff($entity->start())->days < 0;
	}

	public function isUpcoming($entity) {
		return $entity->start()->getTimestamp() - time() > 0;
	}

	public function exportAsICal($entity) {
		$stream = fopen('php://temp', 'w+');

		$calendar = new iCalCalendar(PROJECT_NAME);
		$event = new iCalEvent();

		$event->setDtStart($entity->start());

		if (!$entity->hasStartTime() || !$entity->hasEndTime()) {
			$event->setNoTime(true);
		}
		if ($entity->end) {
			$event->setDtEnd($entity->end());
		}
		$event->setSummary($entity->title);

		if ($entity->location) {
			$event->setLocation($entity->location);
		}
		$calendar->addEvent($event);

		fwrite($stream, $calendar->render());
		rewind($stream);
		return $stream;
	}

	/* Deprecated / BC */

	public static function current(array $query = []) {
		trigger_error('::current() is deprecated in favor of find(current)', E_USER_DEPRECATED);
		return static::find('current', $query);
	}

	public static function previous(array $query = []) {
		trigger_error('::previous() is deprecated in favor of find(previous)', E_USER_DEPRECATED);
		return static::find('previous', $query);
	}

	public static function upcoming(array $query = []) {
		trigger_error('::upcoming() is deprecated in favor of find(upcoming)', E_USER_DEPRECATED);
		return static::find('upcoming', $query);
	}
}

Events::init();

?>
<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace cms_event\models;

use DateTime;
use DateTimeZone;
use Eluceo\iCal\Component\Calendar as iCalCalendar;
use Eluceo\iCal\Component\Event as iCalEvent;
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
		$model = static::object();
		extract(Message::aliases());

		$model->validates['title'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('This field cannot be empty.', ['scope' => 'cms_event'])
			]
		];
		$model->validates['start_date'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => $t('This field cannot be empty.', ['scope' => 'cms_event'])
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

	// Returns the start date (and when available time. The start date is required.
	public function start($entity) {
		$date = DateTime::createFromFormat(
			'Y-m-d',
			$entity->start_date,
			new DateTimeZone($entity->timezone)
		);

		if ($entity->start_time) {
			$time = explode(':', $entity->start_time);
			$date->setTime($time[0], $time[1], $time[2]);
		}
		return $date;
	}

	// Returns the end date (and when available time). The end date is optional.
	public function end($entity) {
		if (!$entity->end_date) {
			return null;
		}
		$date = DateTime::createFromFormat(
			'Y-m-d',
			$entity->end_date,
			new DateTimeZone($entity->timezone)
		);

		if ($entity->end_time) {
			$time = explode(':', $entity->end_time);
			$date->setTime($time[0], $time[1], $time[2]);
		}
		return $date;
	}

	public function hasStartTime($entity) {
		return (boolean) $entity->start_time;
	}

	public function hasEndTime($entity) {
		return (boolean) $entity->end_time;
	}

	public function isPrevious($entity) {
		$now = new DateTime();

		if ($end = $entity->end()) {
			return $now->diff($end)->days < 0;
		}
		return $now->diff($entity->start())->days < 0;
	}

	public function isUpcoming($entity) {
		$now = new DateTime();

		return $entity->start() < $now;
	}

	public function exportAsICal($entity) {
		$stream = fopen('php://temp', 'w+');

		$calendar = new iCalCalendar(PROJECT_NAME);
		$event = new iCalEvent();

		// Time is only used when both start and end time are given or
		// only start is used and has time.
		$useTime = $entity->hasStartTime() && $entity->hasEndTime();
		$useTime = $useTime || (!$entity->end && $entity->hasStartTime());
		if (!$useTime) {
			$event->setNoTime(true);
		}
		$event->setUseTimezone(true);

		if ($end = $entity->end()) {
			$event->setDtEnd($end);
		}
		$event->setDtStart($entity->start());

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

	// Canonical sort date.
	public function date($entity) {
		trigger_error('date() is deprecated in favor of start()', E_USER_DEPRECATED);
		return $this->start($entity);
	}

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
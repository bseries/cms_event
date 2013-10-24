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

namespace cms_event\models;

use lithium\util\Validator;
use DateTime;

class Events extends \lithium\data\Model {

	use \li3_behaviors\data\model\Behaviors;

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'cms_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	protected $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp',
		'li3_taggable\extensions\data\behavior\Taggable' => [
			'field' => 'tags',
			'tagModel' => false,
			'filters' => ['strtolower']
		]
	];

	public static function __init() {
		$model = static::_object();

		$model->validates['title'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => 'Dieses Feld darf nicht leer sein.'
			]
		];
		$model->validates['start'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => 'Es muss ein Startdatum angegeben werden.'
			]
		];
		$model->validates['body'] = [
			[
				'notEmpty',
				'on' => ['create', 'update'],
				'message' => 'Dieses Feld darf nicht leer sein.'
			]
		];
		$model->validates['tags'] = [
			[
				'noSpacesInTags',
				'on' => ['create', 'update'],
				'message' => 'Es sind keine Leerzeichen innerhalb von Tags erlaubt.'
			]
		];
		Validator::add('noSpacesInTags', function($value, $format, $options) {
			return empty($value) || preg_match('/^([a-z0-9]+)(\s?,\s?[a-z0-9]+)*$/i', $value);
		});
	}

	public function date($entity) {
		return DateTime::createFromFormat('Y-m-d', $entity->start);
	}

	// Canonical URL for the event.
	public function url($entity) {
		if ($entity->url && !$entity->body) {
			return $entity->url;
		}
		return array(
			'controller' => 'events', 'action' => 'view',
			'id' => $entity->id, 'library' => 'app'
		);
	}

	public static function upcoming() {
		return static::find('all', [
			'conditions' => [
				'start' => '> ' . date('Y-m-d')
			],
			'order' => ['start' => 'ASC']
		]);
	}

	public static function current() {
		return static::find('all', [
			'conditions' => [
				'start' => '< ' . date('Y-m-d'),
				'or' => [
					'end' => '> ' . date('Y-m-d'),
					'is_open_end' => true
				]
			],
			'order' => ['start' => 'ASC']
		]);
	}

	public static function previous() {
		return static::find('all', [
			'conditions' => [
				'start' => '< ' . date('Y-m-d'),
				'or' => [
					'end' => '< ' . date('Y-m-d'),
					'is_open_end' => false
				]
			],
			'order' => ['start' => 'ASC']
		]);
	}
}

?>
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

use lithium\core\Environment;
use lithium\util\Validator;
use lithium\util\Set;
use DateTime;
use lithium\g11n\Message;

class Events extends \base_core\models\Base {

	use \base_core\models\SlugTrait;

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'base_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	protected static $_actsAs = [
		'base_media\extensions\data\behavior\Coupler' => [
			'bindings' => [
				'cover' => [
					'type' => 'direct',
					'to' => 'cover_media_id'
				]
			]
		],
		'base_core\extensions\data\behavior\Timestamp',
		'li3_taggable\extensions\data\behavior\Taggable' => [
			'field' => 'tags',
			'tagModel' => false,
			'filters' => ['strtolower']
		],
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
				'message' => $t('Spaces cannot be used inside tags.', ['scope' => 'cms_event'])
			]
		];
		Validator::add('noSpacesInTags', function($value, $format, $options) {
			return empty($value) || preg_match('/^([a-z0-9]+)(\s?,\s?[a-z0-9]+)*$/i', $value);
		});
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

	public static function upcoming(array $query = []) {
		return static::find('all', Set::merge([
			'conditions' => [
				'start' => ['>' => date('Y-m-d')]
			],
			'order' => ['start' => 'DESC']
		], $query));
	}

	public static function current(array $query = []) {
		return static::find('all', Set::merge([
			'conditions' => [
				'start' => ['<' => date('Y-m-d')],
				'or' => [
					'end' => ['>' => date('Y-m-d')],
					'is_open_end' => true
				]
			],
			'order' => ['start' => 'DESC']
		], $query));
	}

	public static function previous(array $query = []) {
		return static::find('all', Set::merge([
			'conditions' => [
				'start' => ['<' => date('Y-m-d')],
				'or' => [
					'end' => ['<' => date('Y-m-d')],
					'is_open_end' => false
				]
			],
			'order' => ['start' => 'DESC']
		], $query));
	}
}
Events::init();

?>
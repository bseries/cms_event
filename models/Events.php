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

use cms_film\models\FilmFeatures;
use cms_film\models\Film;
use cms_film\models\FilmsEvents;
use lithium\core\Environment;
use lithium\util\Validator;
use lithium\util\Set;
use DateTime;

class Events extends \lithium\data\Model {

	use \li3_behaviors\data\model\Behaviors;

	public $belongsTo = [
		'CoverMedia' => [
			'to' => 'cms_media\models\Media',
			'key' => 'cover_media_id'
		]
	];

	public $hasMany = [
		'FilmsEvents' => [
			'to' => 'cms_film\models\FilmsEvents'
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
		$features = Environment::get('features');

		$model = static::_object();

		if ($features['connectFilmsWithEvents']) {
			$model->hasMany['FilmsEvents'] = [
				'to' => 'cms_film\models\FilmsEvents'
			];
		}

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

	public function filmFeatures($entity) {
		$results = [];

		if (!$entity->films_events) {
			return $results;
		}
		foreach ($entity->films_events as $join) {
			$results[$join->film->film_feature->id] = $join->film->film_feature;
		}
		return $results;
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

Events::applyFilter('save', function($self, $params, $chain) {
	$data =& $params['data'];

	$films = [];
	if (isset($data['films'])) {
		$films = $data['films'];
		unset($data['films']);
	}
	$result = $chain->next($self, $params, $chain);

	if (!$result) {
		return false;
	}
	if ($films) {
		$results = FilmsEvents::find('all', [
			'conditions' => ['event_id' => $params['entity']->id]
		]);
		$results->delete();

		foreach ($films as $id) {
			$join = FilmsEvents::create(['film_id' => $id, 'event_id' => $params['entity']->id]);
			$join->save();
		}
	}
	return true;
});

?>
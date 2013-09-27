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
			'tagModel' => false
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
			],
			[
				'noUppercasing',
				'on' => ['create', 'update'],
				'message' => 'Für Tags muss Kleinschreibung benutzt werden.'
			]
		];
		Validator::add('noSpacesInTags', function($value, $format, $options) {
			return empty($value) || preg_match('/^([a-z]+)(\s?,\s?[a-z]+)*$/i', $value);
		});
		Validator::add('noUppercasing', function($value, $format, $options) {
			return !preg_match('/[A-Z]+/', $value);
		});
	}
}

?>
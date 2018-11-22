<?php
/**
 * Copyright 2018 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace cms_event\models;

use DateTime;
use GuzzleHttp\Client;
use base_core\extensions\cms\Settings;
use lithium\analysis\Logger;
use lithium\util\Collection;

class BandsintownEvents extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	public static function all(array $config) {
		$results = static::_api('/artists/' . urlencode($config['artist']) . '/events', $config);

		if (!$results) {
			return $results;
		}
		$data = [];

		foreach ($results as $result) {
			$datetime = DateTime::createFromFormat('Y-m-d\TH:i:s', $result['datetime']);
			$title = $result['venue']['name'];

			$extras = array_filter($result['lineup'], function($v) use($config) {
				return $v !== $config['artist'];
			});
			if ($extras) {
				$title .= ' & ' . implode(', ', $extras);
			}

			$item = [
				'title' => $title,
				'location' => rtrim($result['venue']['name']) . ', ' . $result['venue']['city'] . ', ' . $result['venue']['country'],
				'start_date' => $datetime->format('Y-m-d'),
				'start_time' => $datetime->format('H:i:s'),
				'timezone' => PROJECT_TIMEZONE
			];
			if (Settings::read('useSites')) {
				$item['site'] = $config['site'];
			}
			foreach ($result['offers'] as $offer) {
				if ($offer['type'] === 'Tickets') {
					$item['ticket_url'] = $offer['url'];
					$item['is_sold_out'] = $offer['status'] !== 'available';
					break;
				}
			}
			$data[] = static::create($item);
		}
		$data = static::_merge($data);

		return new Collection(compact('data'));
	}

	protected static function _merge($data) {
		$results = [];

		$searchSimilar = function($results, $item) {
			// Find similar from existing and return the key of the similar result.
			foreach ($results as $k => $result) {
				if (soundex($item->title) != soundex($result->title)) {
					continue;
				}
				if (soundex($item->location) != soundex($result->location)) {
					continue;
				}
				$a = DateTime::createFromFormat('Y-m-d', $item->start);
				$b = DateTime::createFromFormat('Y-m-d', $result->start);

				if ($a->diff($b)->format('%d') > 10) {
					continue;
				}
				return $k;
			}
			return false;
		};

		foreach ($data as $item) {
			if (($key = $searchSimilar($results, $item)) !== false) {
				$results[$key]->end = $item->start;
			} else {
				$results[] = $item;
			}

		}
		return $results;
	}

	protected static function _api($url, array $config, array $params = []) {
		$client = new Client([
			'base_uri' => 'https://rest.bandsintown.com/',
			'query' => [
				'app_id' => $config['appId']
			]
		]);

		try {
			$response = $client->request('GET', $url);
		} catch (\Exception $e) {
			Logger::notice('Failed Bandsintown API request: ' . $e->getMessage());
			return false;
		}
		return json_decode($response->getBody(), true);
	}
}

?>
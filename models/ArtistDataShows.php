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

use Guzzle\Http\Client;
use lithium\analysis\Logger;
use lithium\util\Collection;
use DateTime;

class ArtistDataShows extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	public static function all(array $config) {
		$results = static::_api("{$config['username']}/shows/xml", $config);

		if (!$results) {
			return $results;
		}
		$data = [];

		foreach (static::_toArray($results) as $result) {
			$data[] = static::create([
				'title' => $result['name'],
				'location' => rtrim($result['venueName']) . ', ' . $result['city'] . ', ' . $result['country'],
				'modified' => $result['lastUpdate'],
				// We cannot derive sold out status from presence of ticket link,
				// as usage is insonsisten.
				// 'is_sold_out' => !$result['ticketURI'],
				'is_sold_out' => false,
				'ticket_url' => $result['ticketURI'],
				'start' => $result['date'],
				'end' => null
			]);
		}
		$data = static::_merge($data);

		return new Collection(compact('data'));
	}

	protected static function _merge($data) {
		$results = [];

		$searchSimilar = function($results, $item) {
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

	protected static function _toArray($data) {
		$results = [];

		foreach ($data as $item) {
			$item = (array) $item;

			array_walk($item, function(&$item, $key) {
				$item = (string) $item;
			});
			$results[] = $item;
		}
		return $results;
	}

	protected static function _api($url, array $config, array $params = []) {
		$client = new Client('http://artistdata.sonicbids.com/');
		$request = $client->get($url);

		try {
			$response = $request->send();
		} catch (\Exception $e) {
			Logger::notice('Failed ArtistData-Feed request: ' . $e->getMessage());
			return false;
		}
		return simplexml_load_string($response->getBody(), null, LIBXML_NOCDATA);
	}

}

?>
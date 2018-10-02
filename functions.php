<?php


	function getFeeds() {
		$feeds	= array(1002, 1001, 1032, 1039);
		/*	other valid feed ids:
			3, 2, 35
			always seem to be empty
			rss versions are very much not though
		*/

		$out	= array('feeds' => array(), 'stories' => array());

		foreach ($feeds as $feedId) {

			$json	= file_get_contents("https://www.npr.org/feeds/{$feedId}/feed.json");
			$obj	= json_decode($json);

			$out['feeds'][$feedId]	= [
				'title'		=> $obj->title,
				'stories'	=> []
			];

			foreach ($obj->items as $item) {
				$story	= [
					'title'		=> $item->title,
					'summary'	=> $item->summary,
					'date'		=> [
						'published'	=> $item->date_published,
						'modified'	=> $item->date_modified,
					],
					'tags'		=> $item->tags,
					'image'		=> $item->image,
				];

				$out['stories'][$item->id]			= $story;
				$out['feeds'][$feedId]['stories'][]	= $item->id;
			}
		}

		return $out;
	}

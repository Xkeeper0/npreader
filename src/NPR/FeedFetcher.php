<?php

	namespace NPR;
	use NPR\Data\Author;
	use Log;

	class FeedFetcher {

		/**
		 * Default feeds to fetch, if not given any
		 *
		 * Feeds 3, 2, 35 seem to exist but are always empty
		 * (perhaps discontinued programs and such)
		 */
		//protected $defaultFeeds	= [1002, 1001, 1032, 1039];
		protected $defaultFeeds	= [1002];

		// Data collected
		protected $data			= null;



		public function __construct($feeds = []) {
			if (!$feeds) {
				$feeds	= $this->defaultFeeds;
			}
		}



		protected function init() {
			$this->data	= [
					'feeds'		=> [],
					'stories'	=> [],
				];
		}



		public function fetch($feed = null) {

			if (is_integer($feed)) {
				$feeds	= [$feed];
			} elseif (is_array($feed)) {
				$feeds	= $feed;
			} elseif ($feed === null) {
				$feeds	= $this->defaultFeeds;
			}

			if ($this->data === null) {
				$this->init();
			}


			foreach ($feeds as $feedId) {

				Log::message("Fetching JSON feed {$feedId}");
				$json	= file_get_contents("https://www.npr.org/feeds/{$feedId}/feed.json");
				$obj	= json_decode($json);

				$this->data['feeds'][$feedId]	= [
					'title'		=> $obj->title,
					'stories'	=> []
				];

				Log::message("Got feed: {$obj->title}");

				foreach ($obj->items as $item) {
					$author			= Author::getFromData($item->author);
					Log::message("  Got story id {$item->id}: Author {$author->authorId} - {$item->title}");
					$story	= [
						'title'		=> $item->title,
						'summary'	=> d($item->summary),
						'date'		=> [
							'published'	=> d($item->date_published),
							'modified'	=> d($item->date_modified),
						],
						'authorId'	=> $author->authorId,
						'tags'		=> d($item->tags),
						'image'		=> d($item->image),
					];

					$this->data['stories'][(int)$item->id]		= $story;
					$this->data['feeds'][$feedId]['stories'][]	= (int)$item->id;
				}
			}

			return $this->data;
		}
	}

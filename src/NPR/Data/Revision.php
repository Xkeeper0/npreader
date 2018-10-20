<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;

	class Revision {

		public $revisionId	= null;
		public $storyId		= null;
		public $story		= null;
		public $text		= null;


		public function __construct(Story $story = null) {
			if ($story) {
				$this->story	= $story;
				$this->storyId	= $story->storyId;

			} elseif (!$this->storyId) {
				// @todo better exception/message
				throw new \Exception("you, uh, can't do this");
			}
		}


		public function fetchAndUpdate() {
			$database	= Database::getDatabase();

			$this->fetch();

			$query		= $database->prepare("
								INSERT INTO `revisions`
								(
									`revisionId`,
									`storyId`,
									`fetched`,
									`text`
								) VALUES (
									NULL,
									:storyId,
									datetime('now'),
									:text
								)
							");
			$query->execute([
					'storyId'	=> $this->story->storyId,
					'text'		=> $this->text,
				]);

			$this->revisionId	= $database->lastInsertId();
			Log::message("  Inserted new revision [". $this->revisionId ."]");

			$this->story->addRevision($this);

		}

		public function fetch() {
			Log::message("  Fetching story text for story [". $this->story->storyId ."]");
			$this->text	= static::getText($this->story->storyId);
			return $this->text;
		}


		protected static function getText($storyId) {
			$text	= file_get_contents("https://text.npr.org/s.php?sId=$storyId");
			Log::message("    Story text for [$storyId]: ". strlen($text) ." byte(s)");
			return $text;
		}

	}

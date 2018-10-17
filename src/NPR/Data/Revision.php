<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;

	class Revision {

		public $revisionId	= null;
		public $story		= null;
		public $text		= null;


		public function __construct(Story $story) {
			$this->story	= $story;
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

		}

		public function fetch() {
			$this->text	= static::getText($this->story->storyId);
			return $this->text;
		}


		protected static function getText($storyId) {
			$text	= file_get_contents("https://text.npr.org/s.php?sId=$storyId");
			return $text;
		}

	}

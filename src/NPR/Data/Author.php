<?php

	namespace NPR\Data;
	use NPR\Database;

	class Author {

		public $authorId			= null;
		public $name				= null;


		public function __construct($authorId = null, $name = null) {
			if ($authorId === null && $name === null) {
				$this->authorId		= intval($this->authorId);
				return;
			}

			$this->authorId			= $authorId;
			$this->name				= $name;

			$this->insert();

		}


		public static function fromDatabase($authorId, $name) {

			$result		= static::findInDatabase($authorId, $name);
			
			if ($result) {
				if ($result->name !== $name) {
					$result->update($name);
				}
				return $result;
			}

			// Not found, make new one
			return new Author($authorId, $name);

		}



		protected static function findInDatabase($authorId, $name) {

			$database	= Database::getDatabase();

			// @todo This can probably be done better but eh

			if ($authorId) {
				// Find by id
				$query		= $database->prepare("SELECT * FROM `authors` WHERE `authorId` = :authorId");
				$query->execute([
					'authorId'	=> $authorId,
					]);

			} elseif (!$authorId && $name) {
				// Find author by name, if any exist
				$query		= $database->prepare("SELECT * FROM `authors` WHERE `name` = :name AND `authorId` < 0");
				$query->execute([
					'name'		=> $name,
					]);

			} else {
				// this should never happen
				throw new \BadMethodCallException("No authorId or name specified for author");
			}

			return $query->fetchObject(__CLASS__);

		}


		/**
		 * Update an author's name
		 */
		public function update($name) {
			if ($this->authorId === null || $this->authorId < 0) {
				throw new \RuntimeException("Tried to update non-id author");
			}

			$this->name	= $name;
			$this->insert();
		}


		/**
		 * Insert author into db
		 */
		protected function insert() {
			$database	= Database::getDatabase();

			if ($this->authorId === null) {
				// If there is no ID for this author, generate a new one for them
				$query			= $database->query("SELECT MIN(`authorId`) AS 'min' FROM `authors`");
				$result			= $query->fetch();
				$this->authorId	= min(0, intval($result['min'])) - 1;
			}

			$query		= $database->prepare("REPLACE INTO `authors` (`authorId`, `name`) VALUES (:authorId, :name)");
			$query->execute([
				'authorId'	=> $this->authorId,
				'name'		=> $this->name,
				]);

		}


	}

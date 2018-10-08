<?php

	namespace NPR\Data;
	use NPR\Database;
	use Log;

	class Author {

		public $authorId			= null;
		public $name				= null;


		// Known authors
		protected static $authors	= [
				'linked'	=> [],
				'unlinked'	=> [],
			];



		public function __construct($authorId = null, $name = null) {
			if ($authorId === null && $name === null) {
				$this->authorId		= intval($this->authorId);
				return;
			}

			Log::message("Adding author [{$authorId}] '$name'");
			$this->authorId			= $authorId;
			$this->name				= $name;

			$this->insert();

		}






		public static function getAuthorFromData($authorData) {
			$name	= \d($authorData->name);
			$url	= \d($authorData->url);
			$id		= null;

			if ($url !== null) {
				$id	= static::extractIdFromUrl($url);
			}

			// Author has an ID, see if we know about it
			if ($id) {
				$res	= \d(static::$authors['linked'][$id]);
				if ($res) {
					return $res;
				} else {
					return static::createAuthor($id, $name);
				}

			} else {
				$res	= \d(static::$authors['unlinked'][$name]);
				if ($res) {
					return $res;
				} else {
					return static::createAuthor(null, $name);
				}

			}

		}


		protected static function createAuthor($authorId, $authorName) {

			$author	= static::fromDatabase($authorId, $authorName);

			if ($authorId === null) {
				// If unknown, insert into both holding areas
				static::$authors['unlinked'][$authorName]			= $author;
				static::$authors['linked'][$author->authorId]		= $author;

			} else {
				// Insert only into the 'known' one
				static::$authors['linked'][$author->authorId]		= $author;				
			}

			return $author;
		}




		public static function fromDatabase($authorId, $name) {

			$result		= static::findInDatabase($authorId, $name);
			
			if ($result) {
				if ($result->name !== $name) {
					Log::message("Author [{$authorId}] changed names? From '{$result->name}' to '$name'");
					$result->update($name);
				}
				return $result;
			}

			// Not found, make new one
			return new static($authorId, $name);

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









		/**
		 * Gets the ID from a NPR author URL (most of the time)
		 * 
		 * @param string $url URL to extract from
		 * @return int|null author id (if found), otherwise null
		 */
		public static function extractIdFromUrl($url) {
			// 
			$matches	= [];
			$matched	= preg_match('#npr\.org/people/([0-9]+)/#is', $url, $matches);
			if ($matched) {
				return $matches[1];
			} else {
				return null;
			}
		}


		/**
		 * Gets an author's URL
		 * 
		 * @param int $id Author ID
		 * @return string URL to view that author on NPR
		 */
		public function getUrl($id = null) {
			if ($id === null && isset($this) && $this instanceof static) {
				return ($this->authorId > 0 ? "https://www.npr.org/people/{$this->authorId}/" : null);
			}

			return "https://www.npr.org/people/$id/";
		}




	}

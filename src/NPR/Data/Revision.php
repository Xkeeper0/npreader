<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;

	class Revision {

		public $revisionId	= null;
		public $storyId		= null;
		public $parsedText	= null;
		public $text		= null;

		public $story		= null;


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



		public function parseToMarkdown() {
			$doc		= static::cleanupHTML($this->text);
			$cleanDoc	= static::extractHTMLBody($doc);
			return trim(html2markdown($cleanDoc->saveHTML())) ."\r\n";
		}


		protected static function cleanupHTML($html) {

			$doc						= new \DOMDocument();
			$doc->preserveWhiteSpace	= false;
			$doc->formatOutput			= true;
			@$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
			$doc->normalizeDocument();
			$root	= $doc->documentElement;
			$root->normalize();
			$body	= $root->getElementsByTagName("body")->item(0);
			$body->normalize();

			static::deleteAllTags($body, "script");

			$delort	= [];
			foreach ($body->childNodes as $node) {
				if ($node->nodeName === "#text" && trim($node->nodeValue) === "") {
					$delort[]	= $node;
				}
			}

			foreach ($delort as $deleted) {
				$body->removeChild($deleted);
			}

			$delort	= [];
			foreach ($body->childNodes as $node) {
				if ($node->nodeName === "p" && trim($node->nodeValue) === "" && !$node->hasChildNodes()) {
					print "Deleting extraneous ". $node->nodeName ." element\n";
					$delort[]	= $node;
				}
			}
			foreach ($delort as $deleted) {
				$body->removeChild($deleted);
			}

			$body->removeChild($body->firstChild);
			$body->removeChild($body->firstChild);

			$body->removeChild($body->lastChild);
			$body->removeChild($body->lastChild);


			$elements	= [];
			foreach ($body->childNodes as $node) {
				$elements[]	= ['type' => $node->nodeName, 'contents' => $node->nodeValue, 'node' => $node];
				//print "N: ". $node->nodeName . "   C: ". $node->nodeValue ."\n";
			}

			return $doc;

		}


		protected static function extractHTMLBody($document) {

			$body	= $document->getElementsByTagName("body")->item(0);
			$clone	= $body->cloneNode(true);

			$clean	= new \DOMDocument();
			$clean->preserveWhiteSpace	= false;
			$clean->formatOutput		= true;
			$clean->appendChild($clean->importNode($clone, true));


			return $clean;
		}



		protected static function deleteAllTags($doc, $tagName) {
			$junkA	= $doc->getElementsByTagName($tagName);

			$delete	= [];
			foreach ($junkA as $junk) {
				$delete[]	= $junk;
			}

			foreach ($delete as $now) {
				$now->parentNode->removeChild($now);
			}

			return $doc;

		}


	}

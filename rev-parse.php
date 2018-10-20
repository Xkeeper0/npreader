<?php

	require_once("src/include.php");

	use X\NPR\Database;
	use X\NPR\Data\Revision;


	#$html		= file_get_contents("sandbox/rev-140.html");


	$rev		= getRandomHTML();
	#var_dump($rev);
	#die();

	print $rev->revisionId ."\n-------------\n";

	$obj		= null;
	$obj		= parsePass1($rev->text);
	$test		= parsePass2($obj);


	$htmlOut	= $test->saveHTML();
	$mdOut		= trim(html2markdown($htmlOut));
	file_put_contents(sprintf("stories/rev-%04d.md", $rev->revisionId), $mdOut);
	print $mdOut ."\n\n";


	function getRandomHTML() {
		$database	= Database::getDatabase();

		// Grab a matching feed from the database...
		$query		= $database->prepare("
							SELECT * FROM `revisions`
							/* WHERE `revisionId` = 271 */
							ORDER BY RANDOM()
							LIMIT 1
						");
		$query->execute();

		// See if it's in the database already...
		$result		= $query->fetchObject(Revision::class);

		return $result;

	}

	function parsePass2($document) {

		$body	= $document->getElementsByTagName("body")->item(0);
		$clone	= $body->cloneNode(true);

		$clean	= new DOMDocument();
		$clean->preserveWhiteSpace	= false;
		$clean->formatOutput		= true;
		$clean->appendChild($clean->importNode($clone, true));


		return $clean;
	}


	function parsePass1($html) {

		$doc						= new DOMDocument();
		$doc->preserveWhiteSpace	= false;
		$doc->formatOutput			= true;
		@$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
		$doc->normalizeDocument();
		$root	= $doc->documentElement;
		$root->normalize();
		$body	= $root->getElementsByTagName("body")->item(0);
		$body->normalize();

		delortAllTag($body, "script");

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

	//die();

/*
*/

	//print $out ."\n\n";



	function delortAllTag($doc, $name) {
		$junkA	= $doc->getElementsByTagName($name);

		$delete	= [];
		foreach ($junkA as $junk) {
			//var_dump($junk);
			$delete[]	= $junk;
		}

		foreach ($delete as $now) {
			//var_dump($now);
			$now->parentNode->removeChild($now);
		}

		return $doc;

	}

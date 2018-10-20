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
	$test		= testParse($rev->text, $obj);


	whatever(array_shift($test));	// Article title
	whatever(array_shift($test));	// Author name, OR (if missing),
	whatever(array_shift($test));	// "NPR.org, [date]" OR "[Program], ", &middot;, then either "Updated ..." OR [first paragraph of story]
	whatever(array_shift($test));	// [first or second paragraph of story, etc.]
	print "------------------------------------------\n";
	whatever(array_pop($test));
	whatever(array_pop($test));


	function whatever($e) {
		$m	= 100;
		if (strlen($e['contents']) > $m) {
			$e['contents']	= substr($e['contents'], 0, $m) ."...";
		}
		//printf("%-4s  %s\n", $e['type'], $e['contents']);
	}

	//$out		= $obj->saveHTML();


	$body	= $obj->getElementsByTagName("body")->item(0);
	$clone	= $body->cloneNode(true);
	$test	= new DOMDocument();
	$test->appendChild($test->importNode($clone, true));

	$test->preserveWhiteSpace	= false;
	$test->formatOutput			= true;

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



	function testParse($html, &$out) {

		$test						= new DOMDocument();
		$test->preserveWhiteSpace	= false;
		$test->formatOutput			= true;
		@$test->loadHTML('<?xml encoding="utf-8" ?>' . $html);
		$test->normalizeDocument();
		$root	= $test->documentElement;
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

		$out	= $test;

		return $elements;

		return $test;

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

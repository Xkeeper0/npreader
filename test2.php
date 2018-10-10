<?php

	//unlink("npr.db");

	require_once("src/include.php");

	
	$feeds	= [1002, 1001, 1032, 1039];
	$feed	= [];
	foreach ($feeds as $id) {
		$feed[$id]		= NPR\Data\Feed::getFromId($id);

	}

	foreach ($feed as $f) {
		$f->parseStories();
	}

	\NPR\Data\Collection\Stories::commit();
	\NPR\Data\Tag::commitTags();


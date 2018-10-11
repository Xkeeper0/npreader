<?php


	if (!file_exists("vendor/autoload.php")) {
		throw new \Error("Missing vendor/autoload.php. Have you run 'composer install'?");
		die();
	}

	require_once("vendor/autoload.php");


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

<?php

	//unlink("npr.db");

	require_once("src/include.php");

	//$test	= \NPR\Database::getInstance();
	//var_dump($test	= \NPR\Data\Authors::extractIdFromUrl('https://www.npr.org/people/14562108/bill-chappell?utm_medium=JSONFeed&utm_campaign=homepagetopstories'));

/*
	var_dump(
		\NPR\Data\Author::getAuthorFromData(
			(object)[
				'name' => 'Bob Test 2',
				'url' => 'https://www.npr.org/people/14562108/bill-chappell?utm_medium=JSONFeed&utm_campaign=homepagetopstories'
				]
			)
	);
*/

//	$fetcher	= new NPR\FeedFetcher();
//	$feeds		= $fetcher->fetch();
	$feeds	= [1002, 1001, 1032, 1039];
	$feed	= [];
	foreach ($feeds as $id) {
		$feed[$id]		= NPR\Data\Feed::getFromId($id);

	}

	foreach ($feed as $f) {
		$f->parseStories();
	}

	NPR\Data\Tag::commitTags();


	#foreach (NPR\Data\Story::$stories as $story) {
	#	var_dump($story->storyId, $story->fetched);
	#}

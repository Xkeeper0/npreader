<?php

	require_once("functions.php");

	//echo "<pre>";
	$data	= getFeeds();
//	var_dump($data);




	foreach ($data['feeds'] as $feedId => $feed) {

		$json	= file_get_contents("https://www.npr.org/feeds/{$feedId}/feed.json");

		echo <<<E
<table>
	<caption>#{$feedId}: {$feed['title']}</caption>
	<thead>
		<tr>
			<th>id</th>
			<th>dates</th>
			<th>title / summary</th>
			<th>image</th>
		</tr>
	</thead>
	<tbody>
E;
		foreach ($feed['stories'] as $storyId) {
			$story	= $data['stories'][$storyId];

			$tags	= ($story['tags'] ? implode(", ", $story['tags']) : "(none)");

			if ($story['image']) {
				$story['image']	= preg_replace('#.([a-z0-9]+)$#i', '-s100-c85.$1', $story['image']);
			}

			echo <<<E
		<tr>
			<th>{$storyId}</th>
			<td style="white-space: nowrap;">
				P: {$story['date']['published']}
				<br>M: {$story['date']['modified']}
			</td>
			<td>
				<strong>{$story['title']}</strong>
				<br>{$story['summary']}
				<br>tags: $tags
			</td>
			<td>
				<!-- <img src="{$story['image']}" style="max-width: 200px;"> -->
			</td>
		</tr>

E;

		}
		echo <<<E

	</tbody>
</table>

E;
			die();
			print "<br>";

		echo "<hr>";

	}


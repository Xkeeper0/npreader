<?php

	require_once("functions.php");

	echo "<pre>";
	var_dump(getFeeds());

/*
	$feeds	= array(1002, 1001, 1032, 1039, 3, 2, 35);

	foreach ($feeds as $feedId) {

		$json	= file_get_contents("https://www.npr.org/feeds/{$feedId}/feed.json");
		$obj	= json_decode($json);

		echo <<<E
<table>
	<caption>#{$feedId}: {$obj->title}</caption>
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
		foreach ($obj->items as $item) {
			$tags	= ($item->tags ? implode(", ", $item->tags) : "(none)");

			if ($item->image) {
				$item->image	= preg_replace('#.([a-z0-9]+)$#i', '-s100-c85.$1', $item->image);
			}

			echo <<<E
		<tr>
			<th>{$item->id}</th>
			<td style="white-space: nowrap;">
				P: {$item->date_published}
				<br>M: {$item->date_modified}
			</td>
			<td>
				<strong>{$item->title}</strong>
				<br>{$item->summary}
				<br>tags: $tags
			</td>
			<td>
				<!-- <img src="{$item->image}" style="max-width: 200px;"> -->
			</td>
		</tr>

E;

		}
		echo <<<E

	</tbody>
</table>

E;

		echo "<hr>";

	}
*/

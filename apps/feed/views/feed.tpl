<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>

<channel>
	<title><?=$title?></title>
	<atom:link href="<?=$feed_address?>" rel="self" type="application/rss+xml" />
	<link><?=$address?></link>
	<description><?=$description?></description>
	<?php if (empty($last_build_date) === false): ?>
	<lastBuildDate><?=$last_build_date?></lastBuildDate>
	<?php endif ?>
	<language><?=$language?></language>
	<?=$items?>
</channel>
</rss>
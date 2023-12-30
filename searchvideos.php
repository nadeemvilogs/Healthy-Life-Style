<?

$maxvideos = 30;

$videohtml = '<div class="videopost"><div class="videopostthumb"><a href="%%link%%"><img src="http://img.youtube.com/vi/%%videoid%%/0.jpg" width="200" height="140" alt="%%title%%" border="0"></a></div><div class="videoposttitle"><a href="%%link%%">%%title%%</a></div><div class="videopostdescription">%%snippet%%</div></div>';


$searchfor = strtolower (trim ($_GET['searchfor']));
if ($searchfor == 'search videos...')  $searchfor = '';
$videolist = @file_get_contents ('searchlist.txt');
if (! $videolist)  die ('No searchlist.txt file');
$videolistlc = strtolower ($videolist);
$page = @file_get_contents ('searchpage.html');
if (! $page)  die ('No searchpage.html file');
$snipset = @file_get_contents ('searchsnippets.txt');

$numfound = 0;
$vidhtml = '';
$p = 0;
if ($searchfor)
{
	while (1)
	{
		$p = strpos ($videolistlc, $searchfor, $p + 1);
		if (! $p)  break;
	
		for ($start = $p; $videolist[$start] != "\n"; $start --) ;
		$end = strpos ($videolist, "\n", $p);
		if (! $start  ||  ! $end)  break;
		$line = trim (substr ($videolist, $start + 1, $end - $start - 1));
	
		$cpos = strpos ($line, '=');
		if (! $cpos)  continue;
		$cpos2 = strpos ($line, '=', $cpos + 1);
		if (! $cpos2)  continue;
		$linepos = $p - $start;
		if ($linepos < $cpos  ||  $linepos > $cpos2)  continue;

		$videoid = substr ($line, 0, $cpos);
		$title = substr ($line, $cpos + 1, $cpos2 - $cpos - 1);
		$link = substr ($line, $cpos2 + 1);

		$snippet = '';
		$sp = strpos ($snipset, $videoid . '=');
		if ($sp)
		{
			$sp += strlen ($videoid) + 1;
			$esp = strpos ($snipset, "\n", $sp);
			if ($esp)  $snippet = substr ($snipset, $sp, $esp - $sp);
		}

		$html = $videohtml;
		$html = str_replace ('%%videoid%%', $videoid, $html);
		$html = str_replace ('%%title%%', $title, $html);
		$html = str_replace ('%%link%%', $link, $html);
		$html = str_replace ('%%snippet%%', $snippet, $html);
		$vidhtml .= $html;

		$p = $end;
		$numfound ++;
		if ($numfound >= $maxvideos)  break;
	}
}

if ($numfound)
{
	$blanks = ($numfound % 3);
	if ($blanks)  $blanks = 3 - $blanks;
	if ($blanks >= 1)  $vidhtml .= "<div class=\"videopostblank\">&nbsp;</div>\r\n";
	if ($blanks == 2)  $vidhtml .= "<div class=\"videopostblank\">&nbsp;</div>\r\n";
	if ($numfound >= $maxvideos)  $vidhtml .= "<BR><BR><BR>Only the first $maxvideos matching videos are shown<BR>";
}
else
{
	$vidhtml = '<BR>No matching videos<BR>';
}

if (! $searchfor)
	$page = str_replace ('%%pagestitle%%', 'No Search String Entered', $page);
else
	$page = str_replace ('%%pagestitle%%', 'Search Results For "' . $searchfor . '"', $page);
$page = str_replace ('%%title%%', 'Video Search Results', $page);
$page = str_replace ('%%sitemap%%', $vidhtml, $page);

echo $page;
?>
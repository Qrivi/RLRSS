<?php header("Content-Type: application/rss+xml; charset=UTF-8");

/* --------- */
$feeditems = !empty($_GET["count"]) ? intval($_GET["count"]) : 10;
$detailed = !empty($_GET["detail"]) ? filter_var($_GET["detail"], FILTER_VALIDATE_BOOLEAN) : true;
$debug = !empty($_GET["debug"]) ? filter_var($_GET["debug"], FILTER_VALIDATE_BOOLEAN) : false;
/* --------- */

libxml_use_internal_errors(!$debug);

$post = new DOMDocument();
$post->preserveWhiteSpace = false;

$site = new DOMDocument();
$site->preserveWhiteSpace = false;
$site->loadHTML(preg_replace("/(<hr>|\s\s+)/", "", file_get_contents("https://www.rocketleague.com/ajax/articles-results")));

$feed = new DOMDocument();
$feed->preserveWhiteSpace = false;
$feed->formatOutput = true;
$feed->version = "1.0";
$feed->encoding = "UTF-8";

$rss = $feed->createElement("rss");
$rss->setAttribute("version", "2.0");
$rss->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
$feed->appendChild($rss);

$channel = $feed->createElement("channel");
$rss->appendChild($channel);

$feedtitle = $feed->createElement("title", "Rocket League News");
$feeddescription = $feed->createElement("description", "RSS feed for Rocket League News");
$feedlink = $feed->createElement("link", "https://rocketleague.com/news");
$language = $feed->createElement("language", "en-us");
$copyright = $feed->createElement("copyright", date("Y") . " Psyonix Inc. All Rights Reserved");
$generator = $feed->createElement("generator", "RLRSS — http://github.com/Qrivi/RLRSS");
$channel->appendChild($feedtitle);
$channel->appendChild($feeddescription);
$channel->appendChild($feedlink);
$channel->appendChild($language);
$channel->appendChild($copyright);
$channel->appendChild($generator);

$atomlink = $feed->createElement("atom:link");
$atomlink->setAttribute("rel", "self");
$atomlink->setAttribute("type", "application/rss+xml");
$atomlink->setAttribute("href", "http://" . $_SERVER["HTTP_HOST"] . substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/")));
$channel->appendChild($atomlink);

$image = $feed->createElement("image");
$channel->appendChild($image);

$imageurl = $feed->createElement("url", "http://" . $_SERVER["HTTP_HOST"] . substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/")) . "/feedlogo.png");
$imagetitle = $feed->createElement("title", "Rocket League News");
$imagelink = $feed->createElement("link", "https://rocketleague.com/news");
$image->appendChild($imageurl);
$image->appendChild($imagetitle);
$image->appendChild($imagelink);

$lastYear = date("Y");

foreach ($site->getElementsByTagName("div") as $node) {
    if (!strcmp($node->getAttribute("class"), "headline")) {
        if ($feeditems !== -1 && --$feeditems < 0) {
            break;
        }

        $t = $node->lastChild->textContent;
        $l = "https://rocketleague.com" . $node->lastChild->firstChild->getAttribute("href");
        $p = explode(" ", $node->firstChild->textContent);

        $date = DateTime::createFromFormat("j M Y", $p[2] . " " . $p[1] . " " . $lastYear);
        while (strcmp($date->format("D"), $p[0]) !== 0) {
            $lastYear--;
            $date->modify("-1 year");
        }

        $item = $feed->createElement("item");
        $channel->appendChild($item);

        $title = $feed->createElement("title", $t);
        $link = $feed->createElement("link", $l);
        $guid = $feed->createElement("guid", $l);
        $pubDate = $feed->createElement("pubDate", $date->format("r"));
        $item->appendChild($title);
        $item->appendChild($link);
        $item->appendChild($guid);
        $item->appendChild($pubDate);

        if ($detailed) {
            $post->loadHTML(preg_replace("/\s\s+/", "", file_get_contents($l)));

            $a = null;
            $d = null;

            foreach ($post->getElementsByTagName("a") as $link) {
                if (!strcmp($link->getAttribute("rel"), "author")) {
                    $a = "support@psyonix.com (" . $link->textContent . ")";
                }
            }

            foreach ($post->getElementsByTagName("div") as $article) {
                if (strpos($article->getAttribute("class"), "article") !== false) {
                    $article->removeChild($article->lastChild); // remove share buttons
                    $d = $post->saveHTML($article);
                }
            }

            $author = $feed->createElement("author", $a);
            $cdata = $feed->createCDATASection($d);
            $description = $feed->createElement("description");
            $item->appendChild($author);
            $item->appendChild($description);
            $description->appendChild($cdata);
        }
    }
}

print $feed->saveXML();

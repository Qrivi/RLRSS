<?php header("Content-Type: application/rss+xml; charset=UTF-8");

// parse request variables set by user
$feeditems = isset($_GET["count"]) ? intval($_GET["count"]) : 10;
$detailed = isset($_GET["detail"]) ? filter_var($_GET["detail"], FILTER_VALIDATE_BOOLEAN) : true;
$debug = isset($_GET["debug"]) ? filter_var($_GET["debug"], FILTER_VALIDATE_BOOLEAN) : false;

$pages = $feeditems === -1 ? PHP_INT_MAX : ceil($feeditems / 12);
$url = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$aurl = rtrim(preg_replace("/(\w*\.php|\?).*/", "", $url), "/");

libxml_use_internal_errors(!$debug);
set_time_limit(0);

// method to get HTML as a string and strip some tags so DOMDocument plays nice with it
function getHTML($href)
{
    return mb_convert_encoding(preg_replace("/(\s\s+)/", "", file_get_contents($href)), "HTML-ENTITIES", "UTF-8");
}

// container for news article details page
$post = new DOMDocument();
$post->preserveWhiteSpace = false;

// container for news article overview page
$site = new DOMDocument();
$site->preserveWhiteSpace = false;

// structure for the feed we'll output
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
$atomlink->setAttribute("href", $url);
$channel->appendChild($atomlink);

$image = $feed->createElement("image");
$channel->appendChild($image);

$imageurl = $feed->createElement("url", "{$aurl}/favicon.png");
$imagetitle = $feed->createElement("title", "Rocket League News");
$imagelink = $feed->createElement("link", "https://rocketleague.com/news");
$image->appendChild($imageurl);
$image->appendChild($imagetitle);
$image->appendChild($imagelink);

// loop over every news article overview page
for ($i = 0; $i < $pages; $i++) {
    $site->loadHTML(getHTML("https://www.rocketleague.com/ajax/articles-results?p=" . $i * 12));

    // if there are no links on the overview page, there are no news articles
    if($site->getElementsByTagName("a")->length === 0) {
        error_log("Stopping. There is no more news than what's already been fetched.");
        $i = $pages;
        break;
    }

    // loop over all div containers on the page 
    foreach ($site->getElementsByTagName("div") as $node) {
        // only consider divs that contain article data
        if (strpos($node->getAttribute("class"), "tile small") !== false) {
            // but stop if we have already fetched enough articles
            if ($feeditems !== -1 && --$feeditems < 0) {
                error_log("Stopping. Maximum amount of articles to fetch reached.");
                $i = $pages;
                break;
            }
            
            // only work with data that is interesting to us
            $contentNode = $node->firstChild->firstChild->firstChild->nextSibling->nextSibling->firstChild;

            // scrape and parse some data then put it in variables for easy access
            $t = $contentNode->firstChild->textContent;
            $s = $contentNode->firstChild->nextSibling->textContent;
            $l = "https://rocketleague.com" . $node->firstChild->getAttribute("href");
            $p = DateTime::createFromFormat("F j, Y", $contentNode->lastChild->firstChild->firstChild->textContent);

            // structure for the feed's items
            $item = $feed->createElement("item");
            $channel->appendChild($item);

            $title = $feed->createElement("title", $t);
            $link = $feed->createElement("link", $l);
            $guid = $feed->createElement("guid", $l);
            $pubDate = $feed->createElement("pubDate", $p->format("r"));
            $item->appendChild($title);
            $item->appendChild($link);
            $item->appendChild($guid);
            $item->appendChild($pubDate);

            // fetch article details if that's what the user wants
            if ($detailed) {
                $post->loadHTML(getHTML($l));

                // get the article author
                $a = "support@psyonix.com (Psyonix)";
                foreach ($post->getElementsByTagName("a") as $link) {
                    if (!strcmp($link->getAttribute("rel"), "author")) {
                        $a = "support@psyonix.com ({$link->textContent})";
                    }
                }

                // get the article contents
                foreach ($post->getElementsByTagName("div") as $article) {
                    if (strpos($article->getAttribute("class"), "article") !== false) {
                        //$article->removeChild($article->lastChild); // remove share buttons
                        $d = preg_replace("/(\r\n|\r|\n| class=\".*\")/", "", $post->saveHTML($article)); // broken
                    }
                }

                // add it to the feed item
                $author = $feed->createElement("author", $a);
                $cdata = $feed->createCDATASection($d);
                $description = $feed->createElement("description");
                $item->appendChild($author);
                $item->appendChild($description);
                $description->appendChild($cdata);
            } else {
                // add a basic description 
                $description = $feed->createElement("description", $s);
                $item->appendChild($description);
            }
        }
    }
}

// profit
echo $feed->saveXML();

⚠️ **Please deploy RLRSS to your own Heroku instance.**

> Too many people are frequently polling the Heroku url mentioned in this readme. If you get an "application error" near the end of the month: no need to report it. It means the demo Heroku account ran out of free dynos and stopped running. It'll be back up the next month.

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)


---

<p align="center">
  <img alt="RLRSS" src="https://github.com/Qrivi/RLRSS/blob/master/favicon.png" />
</p>

Decided to write this script because [the official Rocket League news site](https://www.rocketleague.com/news/) does not provide an RSS news feed. PHP was the language of choice so I could write it quickly and run it on my cheap PHP server — though I have since moved to [Heroku](https://www.heroku.com).

**Since this script scrapes content from Rocket League's news pages, it is very prone to breaking e.g. when Psyonix update their layout, which they have done a couple of times recently.** If you notice that the script is no longer working properly (which means you did before I did), please [create an issue](https://github.com/Qrivi/RLRSS/issues/new) (or fix it and submit a PR). Thanks!

The script generates a very complete and valid RSS feed on the fly and takes 2 (well, actually 3) optional parameters.

* * *

## Parameters

| Name   | Default | Description |
| ------ | ------- | ----------- |
| limit  | `10`    | The amount of recent news items to include in the feed. `-1` will include all items, and `0` will make the feed pretty useless. |
| detail | `true`  | Generates rich `<description>` and `<author>` fields for feed items when set to `true`, but be careful: may significantly slow down feed generation when `limit` is a high number **and on Heroku you might hit a request timeout**! |
| debug  | `false` | Only thing this does is tell libxml2 to send output through PHP. You likely won't want this, because till libxml2 supports HTML5, your log will be flooded with libxml2 warnings. |

Examples:

-   [https://rlrss.herokuapp.com?limit=50&detail=no](https://rlrss.herokuapp.com?limit=50&detail=no)
-   [https://rlrss.herokuapp.com?limit=5&detail=1](https://rlrss.herokuapp.com?limit=5&detail=1)
-   [https://rlrss.herokuapp.com?limit=420&detail=off](https://rlrss.herokuapp.com?limit=420&detail=off)
-   [https://rlrss.herokuapp.com?limit=10&detail=true](https://rlrss.herokuapp.com?limit=10&detail=true) (default)

Note that since boolean parameters are parsed by PHP's `filter_var()`, most things that should generate `true` will generate `true` (eg. `yes`, `1`, `ON`, ...) but stick to just `true` to play it safe. Likewise, same rule applies to `false`.

## Output

Example output with details:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Rocket League News</title>
    <description>RSS feed for Rocket League News</description>
    <link>https://rocketleague.com/news</link>
    <language>en-us</language>
    <copyright>2018 Psyonix Inc. All Rights Reserved</copyright>
    <generator>RLRSS — http://github.com/Qrivi/RLRSS</generator>
    <atom:link rel="self" type="application/rss+xml" href="https://rlrss.herokuapp.com"/>
    <image>
      <url>https://rlrss.herokuapp.com/favicon.png</url>
      <title>Rocket League News</title>
      <link>https://rocketleague.com/news</link>
    </image>
    <item>
      <title>Spring Fever Hits Rocket League on March 19</title>
      <link>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</link>
      <guid>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</guid>
      <pubDate>Fri, 16 Mar 2018 13:10:49 +0100</pubDate>
      <author>support@psyonix.com (Devin Connors)</author>
      <description><![CDATA[(<p>The whole article here, as <b>rich</b>, <em>formatted</em> HTML.</p>)]]></description>
  </item>
  </channel>
</rss>
```

... and without — pretty straightforward:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Rocket League News</title>
    <description>RSS feed for Rocket League News</description>
    <link>https://rocketleague.com/news</link>
    <language>en-us</language>
    <copyright>2018 Psyonix Inc. All Rights Reserved</copyright>
    <generator>RLRSS — http://github.com/Qrivi/RLRSS</generator>
    <atom:link rel="self" type="application/rss+xml" href="https://rlrss.herokuapp.com"/>
    <image>
      <url>https://rlrss.herokuapp.com/favicon.png</url>
      <title>Rocket League News</title>
      <link>https://rocketleague.com/news</link>
    </image>
    <item>
      <title>Spring Fever Hits Rocket League on March 19</title>
      <link>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</link>
      <guid>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</guid>
      <pubDate>Fri, 16 Mar 2018 13:10:49 +0100</pubDate>
      <description>We’ve got a FEVER, and the only prescription is more Soccar!</description>
    </item>
  </channel>
</rss>
```

And in [Leaf](https://itunes.apple.com/app/id576338668), my preferred RSS client:

![](https://i.imgur.com/VFwGpID.jpg)

## Disclaimer

I'm not affiliated with Psyonix or Rocket League in any way apart from the fact that I love their game (pls bring cross platform inventories k thx bye). If I did anything anyone's not ok with here, please slide in my DMs before taking legal action.

⚽️🚙🚗 GG

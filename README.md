<p align="center">
  <img alt="RLRSS" src="https://github.com/Qrivi/RLRSS/blob/master/feedlogo.png" />
</p>

Decided to write this script since [the official Rocket League news site](https://www.rocketleague.com/news/) does not provide an RSS feed. PHP was the language of choice so I could run it on my cheap PHP server. 👍

The script generates a very complete and valid RSS feed on the fly and takes 2 (well, actually 3) optional parameters.

* * *

## Parameters

| Name   | Default | Description                                                                                                                                                                       |
| ------ | ------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| count  | `10`    | The number of recent news items to include in the feed. `-1` will include all items, and `0` will make the feed pretty useless.                                                   |
| detail | `true`  | Generates rich `<description>` and `<author>` fields for feed items when set to `true`, but careful: may significantly slow down feed generation when `count` is a high number!   |
| debug  | `false` | Only thing this does is tell libxml2 to send output through PHP. You likely won't want this, because till libxml2 supports HTML5, your log will be flooded with libxml2 warnings. |

Examples:

-   [labs.krivi.be/RLRSS?count=50&detail=no](http://labs.krivi.be/RLRSS?count=50&detail=no)
-   [labs.krivi.be/RLRSS?count=5&detail=1](http://labs.krivi.be/RLRSS?count=5&detail=1)
-   [labs.krivi.be/RLRSS?count=5&detail=1](http://labs.krivi.be/RLRSS?count=420&detail=off)
-   [labs.krivi.be/RLRSS?count=10&detail=true](http://labs.krivi.be/RLRSS?count=10&detail=true) (default)

Note that since boolean parameters are parsed by PHP's `filter_var()`, most things that should generate `true` will generate `true` (eg. `yes`, `1`, `ON`, ...) but stick to just `true` to play safe.

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
        <generator>RLNRSS — http://github.com/Qrivi/RLRSS</generator>
        <atom:link rel="self" type="application/rss+xml" href="http://labs.krivi.be/RLRSS"/>
        <image>
          <url>http://labs.krivi.be/RLRSS/feedlogo.png</url>
          <title/>
          <link/>
        </image>
        <item>
          <title>Spring Fever Hits Rocket League on March 19</title>
          <link>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</link>
          <guid>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</guid>
          <pubDate>Fri, 16 Mar 2018 13:10:49 +0100</pubDate>
          <author>support@psyonix.com (Devin Connors)</author>
          <description><![CDATA[(The whole article here.)]]></description>
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
        <generator>RLNRSS — http://github.com/Qrivi/RLRSS</generator>
        <atom:link rel="self" type="application/rss+xml" href="http://labs.krivi.be/RLRSS"/>
        <image>
          <url>http://labs.krivi.be/RLRSS/feedlogo.png</url>
          <title/>
          <link/>
        </image>
        <item>
          <title>Spring Fever Hits Rocket League on March 19</title>
          <link>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</link>
          <guid>https://rocketleague.com/news/spring-fever-hits-rocket-league-on-march-19/</guid>
          <pubDate>Fri, 16 Mar 2018 13:10:49 +0100</pubDate>
    </item>
  </channel>
</rss>
```

## Disclaimer

I'm not affiliated with Psyonix or Rocket League in any way apart from the fact that I love their game (pls bring cross platform inventories k thx bye). If I did anything anyone's not ok with here, please slide in my DMs before taking legal action. Cool!

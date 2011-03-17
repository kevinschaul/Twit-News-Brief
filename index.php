<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<! LINK REL=StyleSheet HREF="style.css" TYPE="text/css" MEDIA=screen>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>Twit news brief</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"></style>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
	<script src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
    </head>
<body>
<script>
   $(document).ready(function(){
        $("a[rel^='prettyPhoto']").prettyPhoto();
   
            var count = 0;
            $('a.headlineLink').click(function() {
                event.preventDefault();
                count++;
                if (count < 2) {
                    var url = $(this).attr('href');
                    $('<iframe />', {
                        'name':   'myFrame',
                        'id':     'myFrame',
                        'src':    url,
                        'width':  '800',
                        'height': '600'
                    }).appendTo('head');
                } else {
                    $('iframe').hide("slow");
                    count = 0;
                }
            });
    });
</script>

<div id="wrap">

<?php
// Turn off error reporting:
//error_reporting(0);

/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB.
 */

/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clearsessions.php');
}
/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* If method is set change API call made. Test is called by default. */
$content = $connection->get('account/verify_credentials');

$connection->format = 'xml';

echo '<div id="title">';
echo '<h1>'.$content->name."'s Twit News Brief:".'</h1>';
echo '<br/>';
echo '</div>';

$timeline = $connection->get('statuses/home_timeline');

$twitter_status = new SimpleXMLElement($timeline); 
foreach($twitter_status->status as $status){ // the next few lines find the links in the tweets
	$linkpos = (strpos($status->text, 'http://'));
	if ($linkpos !== false) {
		$linkend = (strpos($status->text, ' ', $linkpos));
		$linklen = abs($linkend - $linkpos);
		$link = substr($status->text, $linkpos, $linklen);
		$vianame = $status->user->name;
        $viausername = $status->user->screen_name;

// we need to find the headlines associated with the links from the pages' sources
		$handle = fopen($link, "r");
		$source_code = file_get_contents($link, 1024);
		fclose($handle);

        if (strpos($source_code, 'meta name="title"')) {
            // <meta name="title" content="HEADLINE">

            $temp = strpos($source_code, 'meta name="title"');
            $begtitle = strpos($source_code, 'content="', $temp) + 9;
            $endtitle = strpos($source_code, '"', $begtitle);
        } else if (strpos($source_code, "meta name='title'")) {
            // <meta name='title' content='HEADLINE'>

            $temp = strpos($source_code, "meta name='title'");
            $begtitle = strpos($source_code, "content='", $temp) + 9;
            $endtitle = strpos($source_code, "'", $begtitle);
        } else if (strpos($source_code, 'class="entry-title"')) {
            // <h1 class="entry-title">HEADLINE</h1>

            $temp = strpos($source_code, 'class="entry-title"');
            $begtitle = strpos($source_code, '>', $temp) + 1;
            $endtitle = strpos($source_code, '</', $begtitle);
        } else if (strpos($source_code, 'property="og:title"')) {
            // property="og:title" content="HEADLINE"

            $temp = strpos($source_code, 'property="og:title"');
            $begtitle = strpos($source_code, 'content="', $temp) + 9;
            $endtitle = strpos($source_code, '"', $begtitle);

        } else {
        // just use the page's title value
		$begtitle = strpos($source_code, '<title>') + 7; //don't include the '<title>' tag
		$endtitle = strpos($source_code, '</title>');
        }
		$titlelen = abs($endtitle - $begtitle);
		$mytitle = substr($source_code, $begtitle, $titlelen);

        if (strlen($mytitle) < 5) {
            // this title sucks
        } else {
            // use this story
		    if (strlen($mytitle) >= 120) {
			    $mytitle = substr($mytitle, 0, 120).' ...';
		    }
            echo '<span class="story">';
			echo '<a class="headlineLink" rel="prettyPhoto" href="'.$link.'?iframe=true&width=800&height=600">'.$mytitle.'</a> ';
			echo '<a class = "retweet" href="http://twitter.com/home?status='.$link.'+via+TwitNewsBrief+by+@foxyNinja7">Retweet</a>';	
			echo '<br/>';
            echo '<div class="via">';
            echo 'via ';
            echo '<a href="http://www.twitter.com/'.$viausername.'">'.$vianame.'</a>';
            echo '</div>';
            echo '</span>';
		}
	}
}


/*
// Testing
$link="http://actualinnovation.com";
$mytitle="New company aims to shake up the news-reading scene New company aims to shake up the news-reading scene";
$viausername="foxyninja7";
$vianame="Kevin Schaul";
            echo $_COOKIE["accessToken"];
            echo '<span class="story">';
            echo '<a class="headline" rel="prettyPhoto" href="'.$link.'?iframe=true&width=800&height=600">'.$mytitle.'</a> ';
			echo '<a class="retweet" href="http://twitter.com/home?status='.$link.'+via+TwitNewsBrief+by+@foxyNinja7">Retweet</a>';	
			echo '<br/>';
            echo '<div class="via">';
            echo 'via ';
            echo '<a href="http://www.twitter.com/'.$viausername.'">'.$vianame.'</a>';
            echo '</div>';
            echo '</span>';
            echo '<myframe>';
            echo '</myframe>';
*/
?>

</div>

<div id="footer">Twit News Brief is a product of <a href="http://actualinnovation.com">ActualInnovation.com</a>  |   
      Created by <a href="http://twitter.com/foxyninja7">@foxyNinja7</a>
</div>
</body>
</html>

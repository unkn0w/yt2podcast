<?php
/*
 * This script will convert Your Youtube playlist into podcast on iTunes
 * Please just modify "config.php" file - nothing to change here :)
 * 
 * Author: Jakub 'unknow' Mrugalski
 *
 */
require_once('./config.php');
require_once('./Youtube.php');

$youtube = new Zarkiel\Media\Youtube();

function getMovies(){
    $apiURL = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId='.PLAYLIST_ID.'&maxResults=50&key='.YOUTUBE_API_KEY;
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $apiURL); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch); 
    curl_close($ch);
    return json_decode($output);
}

function addItem($title,$desc,$vidID){
        global $youtube;
        $myURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data = $youtube->getDownloadLinks($vidID);
        $length = preg_replace('@.+clen=([0-9]+)&.+@','\1',$data['MP4']['Medium Quality - 480x360']);
        echo '<item>
          <title>'.htmlspecialchars($title).'</title>
          <description>'.htmlspecialchars($desc).'</description>
          <link>https://youtube.com/watch?v='.$vidID.'</link>
          <pubDate>'.date('r',strtotime($data['meta']['publishDate'])).'</pubDate>
          <guid isPermaLink="true">https://youtube.com/watch?v='.$vidID.'</guid>
          <enclosure url="'.$myURL.'/getMovie.php/'.$vidID.'.mp4" length="'.$length.'" type="video/mp4" />
          <media:content url="'.$myURL.'/getMovie.php/'.$vidID.'.mp4" fileSize="'.$length.'" type="video/mp4" />
          <itunes:subtitle>'.htmlspecialchars($title).'</itunes:subtitle>
          <itunes:summary />
          <itunes:duration>'.$data['meta']['duration'].'</itunes:duration>
        </item>';
}

$movies = getMovies();

header('Content-Type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
  <channel>
    <title><?php echo PODCAST_NAME; ?></title>
    <description><?php echo PODCAST_DESC; ?></description>
    <link><?php echo PODCAST_URL; ?></link>
    <lastBuildDate><?php echo date('r'); ?></lastBuildDate>
    <pubDate><?php echo date('r'); ?></pubDate>
    <language><?php echo PODCAST_LANG; ?></language>
    <author><?php echo PODCAST_AUTHOR; ?></author>
    <copyright><?php echo PODCAST_AUTHOR; ?></copyright>
    <itunes:subtitle><?php echo PODCAST_DESC; ?></itunes:subtitle>
    <itunes:summary><?php echo PODCAST_DESC; ?></itunes:summary>
    <itunes:explicit>no</itunes:explicit>
    <itunes:author><?php echo PODCAST_AUTHOR; ?></itunes:author>
    <itunes:email><?php echo PODCAST_EMAIL; ?></itunes:email>
    <atom:link href="<?php echo $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" rel="self" type="application/rss+xml" />
    <itunes:category text="Technology" />
    <itunes:image href="<?php echo PODCAST_IMAGE; ?>" />
    <media:thumbnail url="<?php echo PODCAST_IMAGE; ?>" />
    <?php
foreach($movies->items as $movie){
    addItem($movie->snippet->title,
        htmlspecialchars($movie->snippet->description),
        $movie->snippet->resourceId->videoId,
        preg_replace('@^([^ ]+)T([^\.]+)@','\1 \2',$movie->snippet->publishedAt)
    );
}
?>
  </channel>
</rss>

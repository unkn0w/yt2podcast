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

function addItem($title,$desc,$vidID,$pubDate){
        global $youtube;
        $data = $youtube->getDownloadLinks($vidID);
        $length = preg_replace('@.+clen=([0-9]+)&.+@','\1',$data['MP4']['Medium Quality - 480x360']);
        echo '<item>
          <title>'.htmlspecialchars($title).'</title>
          <description>'.htmlspecialchars($desc).'</description>
          <link>https://youtube.com/watch?v='.$vidID.'</link>
          <pubDate>'.date('r',strtotime($pubDate)).'</pubDate>
          <guid isPermaLink="true">https://youtube.com/watch?v='.$vidID.'</guid>
          <enclosure url="https://uw-team.org/ytd/'.$vidID.'.mp4" length="'.$length.'" type="video/mp4" />
          <media:content url="https://uw-team.org/ytd/'.$vidID.'.mp4" fileSize="'.$length.'" type="video/mp4" />
          <itunes:subtitle>'.htmlspecialchars($title).'</itunes:subtitle>
          <itunes:summary />
          <itunes:duration>'.$data['meta']['duration'].'</itunes:duration>
        </item>';
}

$movies = getMovies();

foreach($movies->items as $movie){
    addItem($movie->snippet->title,
        htmlspecialchars($movie->snippet->description),
        $movie->snippet->resourceId->videoId,
        123
    );
}

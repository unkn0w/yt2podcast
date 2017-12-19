<?php
require './Youtube.php';
require './config.php';
$youtube = new Zarkiel\Media\Youtube();
$vID = preg_replace('@.+/([^\.]+)\.mp4$@','\1',$_SERVER['REQUEST_URI']);
$hd = preg_match('@^/ytd_hd@',$_SERVER['REQUEST_URI']);

$links = $youtube->getDownloadLinks($vID);

if (VIDEO_HD==1 && isset($links['MP4']['High Quality - 1280x720'])){
    $quality = 'High Quality - 1280x720';
} else {
    $quality = 'Medium Quality - 480x360';
}

header('Location: '.$links['MP4'][$quality]);
?>

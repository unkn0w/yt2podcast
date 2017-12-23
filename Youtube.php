<?php
namespace Zarkiel\Media;

/**
 * 	This class allows you to get the download links from any youtube video
 * 
 * 	@author Zarkiel
 *      @updatedBy Unknow
 */
class Youtube{
	
	/**
	 * The video map for the results
	 * 
	 * @var array
	 */ 
	private $videoMap = array(
		"13" => array("3GP", "Low Quality - 176x144"),
		"17" => array("3GP", "Medium Quality - 176x144"),
		"36" => array("3GP", "High Quality - 320x240"),
		"5" => array("FLV", "Low Quality - 400x226"),
		"6" => array("FLV", "Medium Quality - 640x360"),
		"34" => array("FLV", "Medium Quality - 640x360"),
		"35" => array("FLV", "High Quality - 854x480"),
		"43" => array("WEBM", "Low Quality - 640x360"),
		"44" => array("WEBM", "Medium Quality - 854x480"),
		"45" => array("WEBM", "High Quality - 1280x720"),
		"18" => array("MP4", "Medium Quality - 480x360"),
		"22" => array("MP4", "High Quality - 1280x720"),
		"37" => array("MP4", "High Quality - 1920x1080"),
		"38" => array("MP4", "High Quality - 4096x2304")
	);
	
	/**
	 * The page that will be used for requests
	 * 
	 * @var string
	 */ 
	private $videoPageUrl = 'https://www.youtube.com/watch?v=';
	
	/**
	 * Returns the video page content
	 * 
	 * @param string  The video id
	 * @return string The video page content 
	 */
	protected function getPageContent($id){
		$page = $this->videoPageUrl.$id;
		//$content = file_get_contents($page);
		$c = curl_init();
                curl_setopt($c, CURLOPT_URL, $page);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT ,35);
		curl_setopt($c, CURLOPT_TIMEOUT, 300);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		$content = curl_exec($c);
		curl_close($c);
		return $content;
	}
	
	/**
	 * Return the download links
	 * 
	 * @param string The video id
	 * @return array The download links
	 */ 
	function getDownloadLinks($id){
		$content = $this->getPageContent($id);
                preg_match_all('@<meta itemprop="duration" content="([^"]+)">@',$content,$durationTMP);
                $publishDate = preg_replace('@.+<meta itemprop="datePublished" content="([^"]+)">.+@ms','\1',$content);
                $vidTitle = preg_replace('@.+<meta itemprop="name" content="([^"]+)">.+@ms','\1',$content);
                $vidDesc = preg_replace('@.+<meta name="description" content="([^"]+)">.+@ms','\1',$content);
                if (isset($durationTMP[1][0])){
                    $hours = '00';
                    list($minutes,$seconds) = explode(':',preg_replace('@PT([0-9]+)M([0-9]+)S@','\1:\2',$durationTMP[1][0]));
                    if ($minutes>60){
                        $hours = floor($minutes/60);
                        $minutes = $minutes%60;
                    }
                    $duration = str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($minutes,2,'0',STR_PAD_LEFT).':'.str_pad($seconds,2,'0',STR_PAD_LEFT);
                } else {
                    die('duration error!');
                }
		$videos = array('MP4' => array(), 'FLV' => array(), '3GP' => array(), 'WEBM' => array());
		
		if(preg_match('/\"url_encoded_fmt_stream_map\":[ ]*\"(.*)\"/iUm', $content, $r)){
			$data = $r[1];
			$data = explode(',', $data);
			
			foreach($data As $cdata){
				$cdata = str_replace('\u0026', '&', $cdata);
				$cdata = explode('&', $cdata);
				
				foreach($cdata As $xdata){
                                        $sig = '';
					if(preg_match('/^sig/', $xdata)){
						$sig = substr($xdata, 4);
					}
					
					if(preg_match('/^url/', $xdata)){
						$url = substr($xdata, 4);
					}
					
					if(preg_match('/^itag/', $xdata)){
						$type = substr($xdata, 5);
					}
				}

				$url = urldecode($url).'&signature='.$sig;
				$videos[$this->videoMap[$type][0]][$this->videoMap[$type][1]] = $url;
                                $videos['vid_by_quality'][$type] = $url;
			}
		}
                $videos['meta'] = ['duration'=>$duration,'publishDate'=>$publishDate,'title'=>$vidTitle,'desc'=>$vidDesc];
		
		return $videos;
	}
}

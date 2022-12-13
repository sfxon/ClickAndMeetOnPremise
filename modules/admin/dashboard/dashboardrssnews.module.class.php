<?php

class cDashboardrssnews extends cModule {
		/////////////////////////////////////////////////////////////////////////////////
		// Render the content. This is the middle part of the page.
		/////////////////////////////////////////////////////////////////////////////////
		public static function getRssNews($template) {
				$rss_stream = 'http://www.mindfav.com/blitz-news-de.rss';
				$rss_path = 'data/tmp/dashboardrssnews/';
				$rss_file_last_update = $rss_path  . 'last-update.txt';
				$rss_file_stream = $rss_path . 'stream.xml';	
				$reload = false;	
				$rss_content = '';		
				
				//Check if there is a new version of the feed.
				if(!file_exists($rss_file_last_update) || !file_exists($rss_file_stream)) {
						$reload = true;
				} else {
						$fp = fopen($rss_file_last_update, 'r');
						$timestamp = fread($fp, 40);
						fclose($fp);
						
						//if the stream is older than one day..
						if($timestamp < strtotime('-1 day')) {
								$reload = true;
						}
				}
				
				//If the stream is to update - load the xml file from the news server and save it local for later use.
				if(true === $reload) {
						//Load the stream
						$ch = curl_init($rss_stream);
						@$fp = fopen($rss_file_stream, 'w');
						
						if($fp) {
								curl_setopt($ch, CURLOPT_FILE, $fp);
								curl_setopt($ch, CURLOPT_HEADER, 0);
								
								curl_exec($ch);
								curl_close($ch);
								fclose($fp);
								
								//Save the time
								$fp = fopen($rss_file_last_update, 'w');
								fwrite($fp, time());
								fclose($fp);
						}
				}
				
				//Now load the news file..
				if(file_exists($rss_file_stream)) {
						$fp = fopen($rss_file_stream, 'r');
						$rss_content = fread($fp, 100000);
						fclose($fp);
				}
				
				$news_data = cDashboardrssnews::parseXML($rss_content);
				
				$renderer = core()->getInstance('cRenderer');
				$renderer->setTemplate($template);
				$renderer->assign('NEWS_DATA', $news_data);
				return $renderer->fetch('site/dashboard/dashboardrssnews.html');
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////
		// Parse XML News String 
		////////////////////////////////////////////////////////////////////////////////////////////////
		public static function parseXML($rss_content) {
				$object = @simplexml_load_string($rss_content);
				
				if(false === $object) {
						return false;
				}
				
				//check for the existance of some objects we require - if they are there - return the object
				if(
						!isset($object->channel) ||
						!isset($object->channel->title) ||
						!isset($object->channel->description) ||
						!isset($object->channel->link) ||
						!isset($object->channel->pubDate) ||
						!isset($object->channel->item) /*||
						!is_array($object->channel->item)*/
				) {
						return false;
				}
				
				return $object;
		}
}

?>
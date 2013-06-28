<?php

if ($search){
	$td = $twitter_data['statuses'];
} else {
	$td = $twitter_data;
}
$arrLen = count($td);
$linkOpts = 'rel="noreferrer" target="_blank"';

for ($i=0; $i<$arrLen; $i++) {
	print(PHP_EOL. '	<entry>'. PHP_EOL);
		print('		<id>tag:twitter.com,' . date("Y-m-d", strtotime($td[$i]['created_at'])) . ':/' . $td[$i]['user']['screen_name'] . '/statuses/' . $td[$i]['id_str'] . '</id>'. PHP_EOL);
		print('		<link href="https://twitter.com/'.$td[$i]['user']['screen_name'].'/statuses/'. $td[$i]['id_str'] .'" rel="alternate" type="text/html"/>'. PHP_EOL);
		
		$summaryContent = $td[$i]['text'];
		$feedContent = '<p>' . $summaryContent . '</p>';
		
		// Loop through the list of links and beautify them for title/summary,
		// and linkify them for the article content
		for ($j = 0; $j < count($td[$i]['entities']['urls']); $j++) {
			$url = $td[$i]['entities']['urls'][$j]['url'];
			$expanded_url = $td[$i]['entities']['urls'][$j]['expanded_url'];
			$display_url = $td[$i]['entities']['urls'][$j]['display_url'];
			$linkstr = '<a href="'.$expanded_url.'" '.$linkOpts.'>'.$display_url.'</a>';
			
			$summaryContent = str_replace($url, $display_url, $summaryContent);
			$feedContent = str_replace($url, $linkstr, $feedContent);
		}
		
		// Now loop through and linkify mentions
		for ($j = 0; $j < count($td[$i]['entities']['user_mentions']); $j++) {
			$screen_name = $td[$i]['entities']['user_mentions'][$j]['screen_name'];
			$linkstr = '<a href="http://twitter.com/'.$screen_name.'" '.$linkOpts.'>@'.$screen_name.'</a>';
			
			$feedContent = str_replace('@'.$screen_name, $linkstr, $feedContent);
		}
		
		// And linkify hashtags
		for ($j = 0; $j < count($td[$i]['entities']['hashtags']); $j++) {
			$hash_text = $td[$i]['entities']['hashtags'][$j]['text'];
			$linkstr = '<a href="http://twitter.com/search?q=%23'.$hash_text.'&src=hash" '.$linkOpts.'>#'.$hash_text.'</a>';
			
			$feedContent = str_replace('#'.$hash_text, $linkstr, $feedContent);
		}
		
		// And embed photos
		for ($j = 0; $j < count($td[$i]['entities']['media']); $j++) {
			if ($td[$i]['entities']['media'][$j]['type'] == 'photo') {
				$url = $td[$i]['entities']['media'][$j]['url'];
				$display_url = $td[$i]['entities']['media'][$j]['display_url'];
				$media_url = $td[$i]['entities']['media'][$j]['media_url'];
				$expanded_url = $td[$i]['entities']['media'][$j]['expanded_url'];
				
				$media_link = '<a href="'.$expanded_url.'" '.$linkOpts.'>'.$display_url.'</a>';
				
				$feedContent = str_replace($url, $media_link, $feedContent);
				$feedContent = $feedContent . PHP_EOL . '<p><img src="'.$media_url.'" /></p>';
			}
		}
		
		// A less-than-ideal way of handling tweets in_reply_to something
		// TODO: Replace this with a JSON request that actually fetches and embeds the parent tweet?
		//       Be sure to limit recursion if you do this.
		if ($td[$i]['in_reply_to_status_id']) {
			$parent_sn = $td[$i]['in_reply_to_screen_name'];
			$parent_link = '<a href="http://twitter.com/'.$parent_sn.'/statuses/'.$td[$i]['in_reply_to_status_id_str'].'" '.$linkOpts.'>@'.$parent_sn.'</a>';
			
			$feedContent = '<p><em>In reply to '.$parent_link.':</em></p>'.PHP_EOL.$feedContent;
		}
		
		
		print('		<title>'.$td[$i]['user']['screen_name'].': '.htmlspecialchars($summaryContent).'</title>'. PHP_EOL);
		print('		<summary type="html"><![CDATA['.$td[$i]['user']['screen_name'].': '.$summaryContent.']]></summary>'. PHP_EOL);
		print('		<content type="html"><![CDATA['.$feedContent.']]></content>'. PHP_EOL);
		print('		<updated>'.date('c', strtotime($td[$i]['created_at'])).'</updated>'. PHP_EOL);
		print('		<author><name>'.$td[$i]['user']['screen_name'].'</name></author>'. PHP_EOL);
		
		$hashLen = count($td[$i]['entities']['hashtags']);
		if ($hashLen > 0){
			for ($j=0; $j<$hashLen; $j++){
				print('		<category term="'.$td[$i]['entities']['hashtags'][$j]['text'].'"/>'. PHP_EOL);
			}
		}
		
	print('	</entry>'. PHP_EOL);
}

print('</feed>'. PHP_EOL);
print('<!-- vim:ft=xml -->');
?>

<?php


class widget_zigtweets extends WP_Widget {


	public function __construct() {
		parent::__construct(false, $name = 'ZigTweets', array('description'=>"Shows recent tweets"), array('width' => 400));
	}


	public function widget($args, $instance) {
		global $zigtweets;
		extract($args);
		if (!empty($instance['title'])) $title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if (!empty($title)) echo $before_title . $title . $after_title;


		//check settings and die if not set
		if(empty($instance['consumerkey']) || empty($instance['consumersecret']) || empty($instance['accesstoken']) || empty($instance['accesstokensecret']) || empty($instance['cachetime']) || empty($instance['username'])) {
			?>
			<strong>Please fill all widget settings!</strong>
			<?php
			echo $after_widget;
			return;
		}


		//check if cache needs update
		$last_cache_time = $zigtweets->options['last_cache_time'];
		$diff = time() - $last_cache_time;
		$crt = $instance['cachetime'] * 3600;

		//	yes, it needs update
		if($diff >= $crt || empty($last_cache_time)) {


			# class includes are here to prevent them being global
			require_once dirname(__FILE__) . '/oauth.php';
			require_once dirname(__FILE__) . '/twitteroauth.php';


			$connection = new TwitterOAuth($instance['consumerkey'], $instance['consumersecret'], $instance['accesstoken'], $instance['accesstokensecret']);
			$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=" . $instance['username'] . "&count=10&exclude_replies=" . $instance['excludereplies']);


			if(!empty($tweets->errors)){
				if($tweets->errors[0]->message == 'Invalid or expired token'){
					echo '<strong>'.$tweets->errors[0]->message.'!</strong><br />You\'ll need to regenerate it <a href="https://apps.twitter.com/" target="_blank">here</a>!' . $after_widget;
				}else{
					echo '<strong>'.$tweets->errors[0]->message.'</strong>' . $after_widget;
				}
				return;
			}

			$tweets_array = array();
			for($i = 0;$i <= count($tweets); $i++){
				if(!empty($tweets[$i])){
					$tweets_array[$i]['created_at'] = $tweets[$i]->created_at;

					//clean tweet text
					$tweets_array[$i]['text'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $tweets[$i]->text);

					if(!empty($tweets[$i]->id_str)){
						$tweets_array[$i]['status_id'] = $tweets[$i]->id_str;
					}
				}
			}

			//save tweets to wp option
			$zigtweets->options['tweets_for_' . $instance['username']] = $tweets_array;
			$zigtweets->options['last_cache_time'] = time();
			update_option('zigtweets', $zigtweets->options);

			echo '<!-- twitter cache has been updated! -->';
		}



		$zigtweets_plugin_tweets = $zigtweets->options['tweets_for_' . $instance['username']];
		if(!empty($zigtweets_plugin_tweets) && is_array($zigtweets_plugin_tweets)){
			print '
						<div class="zigtweets">
							<ul>';
			$fctr = '1';
			foreach($zigtweets_plugin_tweets as $tweet){
				if(!empty($tweet['text'])){
					if(empty($tweet['status_id'])){ $tweet['status_id'] = ''; }
					if(empty($tweet['created_at'])){ $tweet['created_at'] = ''; }

					print '<li><span>'.$zigtweets->convert_links($tweet['text']).'</span><br /><a class="twitter_time" target="_blank" href="http://twitter.com/'.$instance['username'].'/statuses/'.$tweet['status_id'].'">'.$zigtweets->get_relative_time($tweet['created_at']).'</a></li>';
					if($fctr == $instance['tweetstoshow']){ break; }
					$fctr++;
				}
			}

			print '
							</ul>';

			if (@$instance['showfollow']) {
				?>
				<div class="follow"><a class="followlink"><?php echo @$instance['followlinktext'] ?></a></div>
				<?php
			}

			print '</div>';
		}else{
			?>
			<div class="zigtweets"><b>Error!</b> Couldn't retrieve tweets for some reason!</div>
			<?php
		}



		echo $after_widget;
	}


	public function update($new_instance, $old_instance) {
		global $zigtweets;
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['consumerkey'] = strip_tags( $new_instance['consumerkey'] );
		$instance['consumersecret'] = strip_tags( $new_instance['consumersecret'] );
		$instance['accesstoken'] = strip_tags( $new_instance['accesstoken'] );
		$instance['accesstokensecret'] = strip_tags( $new_instance['accesstokensecret'] );
		$instance['cachetime'] = strip_tags( $new_instance['cachetime'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['tweetstoshow'] = strip_tags( $new_instance['tweetstoshow'] );
		$instance['excludereplies'] = strip_tags( $new_instance['excludereplies'] );
		$instance['showfollow'] = strip_tags( $new_instance['showfollow'] );
		$instance['followlinktext'] = strip_tags( $new_instance['followlinktext'] );

		# clear cache when saving options
		$zigtweets->options['last_cache_time'] = '0';

		update_option('zigtweets', $zigtweets->options);

		return $instance;
	}


	public function form($instance) {
		$defaults = array( 'title' => '', 'consumerkey' => '', 'consumersecret' => '', 'accesstoken' => '', 'accesstokensecret' => '', 'cachetime' => '', 'username' => '', 'tweetstoshow' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );

		echo '
				<p>Get your API keys &amp; tokens at <a href="https://apps.twitter.com/" target="_blank">https://apps.twitter.com/</a></p>
				<p><label>Title:</label>
					<input type="text" name="'.$this->get_field_name( 'title' ).'" id="'.$this->get_field_id( 'title' ).'" value="'.esc_attr($instance['title']).'" class="widefat" /></p>
				<p><label>Consumer Key:</label>
					<input type="text" name="'.$this->get_field_name( 'consumerkey' ).'" id="'.$this->get_field_id( 'consumerkey' ).'" value="'.esc_attr($instance['consumerkey']).'" class="widefat" /></p>
				<p><label>Consumer Secret:</label>
					<input type="text" name="'.$this->get_field_name( 'consumersecret' ).'" id="'.$this->get_field_id( 'consumersecret' ).'" value="'.esc_attr($instance['consumersecret']).'" class="widefat" /></p>
				<p><label>Access Token:</label>
					<input type="text" name="'.$this->get_field_name( 'accesstoken' ).'" id="'.$this->get_field_id( 'accesstoken' ).'" value="'.esc_attr($instance['accesstoken']).'" class="widefat" /></p>
				<p><label>Access Token Secret:</label>
					<input type="text" name="'.$this->get_field_name( 'accesstokensecret' ).'" id="'.$this->get_field_id( 'accesstokensecret' ).'" value="'.esc_attr($instance['accesstokensecret']).'" class="widefat" /></p>
				<p><label>Cache tweets every:</label>
					<input type="text" name="'.$this->get_field_name( 'cachetime' ).'" id="'.$this->get_field_id( 'cachetime' ).'" value="'.esc_attr($instance['cachetime']).'" class="small-text" /> hours</p>
				<p><label>Twitter Username:</label>
					<input type="text" name="'.$this->get_field_name( 'username' ).'" id="'.$this->get_field_id( 'username' ).'" value="'.esc_attr($instance['username']).'" class="widefat" /></p>
				<p><label>Tweets to display:</label>
					<select type="text" name="'.$this->get_field_name( 'tweetstoshow' ).'" id="'.$this->get_field_id( 'tweetstoshow' ).'">';
		for($i = 1; $i <= 10; $i++){
			echo '<option value="'.$i.'"'; if($instance['tweetstoshow'] == $i){ echo ' selected="selected"'; } echo '>'.$i.'</option>';
		}
		echo '
					</select></p>
				<p><label>Exclude replies:</label>
					<input type="checkbox" name="'.$this->get_field_name( 'excludereplies' ).'" id="'.$this->get_field_id( 'excludereplies' ).'" value="true"';
		if(!empty($instance['excludereplies']) && esc_attr($instance['excludereplies']) == 'true'){
			print ' checked="checked"';
		}
		print ' /></p>';
		echo '
				<p><label>Show follow link:</label>
					<input type="checkbox" name="'.$this->get_field_name( 'showfollow' ).'" id="'.$this->get_field_id( 'showfollow' ).'" value="true"';
		if(!empty($instance['showfollow']) && esc_attr($instance['showfollow']) == 'true'){
			print ' checked="checked"';
		}
		print ' /></p>';
		echo '
		<p><label>Follow link text:</label>
			<input type="text" name="'.$this->get_field_name( 'followlinktext' ).'" id="'.$this->get_field_id( 'followlinktext' ).'" value="'.esc_attr($instance['followlinktext']).'" class="widefat" /></p>
			';
	}


}

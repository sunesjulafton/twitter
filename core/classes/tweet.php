<?php

	class Tweet extends User {
		
		function __construct($pdo) {
			$this->pdo = $pdo;
		}

		public function tweets() {
			$stmt = $this->pdo->prepare("SELECT * FROM tweets, users WHERE tweetBy = user_id");
			$stmt->execute();
			$tweets = $stmt->fetchAll(PDO::FETCH_OBJ);

			foreach ($tweets as $tweet) {
				echo '<div class="all-tweet">
						<div class="t-show-wrap">	
						 <div class="t-show-inner">
							<!-- this div is for retweet icon 
							<div class="t-show-banner">
								<div class="t-show-banner-inner">
									<span><i class="fa fa-retweet" aria-hidden="true"></i></span><span>Screen-Name Retweeted</span>
								</div>
							</div>
							-->
							<div class="t-show-popup">
								<div class="t-show-head">
									<div class="t-show-img">
										<img src="'. $tweet->profile_image .'"/>
									</div>
									<div class="t-s-head-content">
										<div class="t-h-c-name">
											<span><a href="'. $tweet->username .'">'. $tweet->screen_name .'</a></span>
											<span>@'. $tweet->username .'</span>
											<span>'. $tweet->postedOn .'</span>
										</div>
										<div class="t-h-c-dis">
											'. $tweet->status .'
										</div>
									</div>
								</div>';
								if(!empty($tweet->tweetImage)){ 
									echo'
									<!--tweet show head end-->
									<div class="t-show-body">
									  <div class="t-s-b-inner">
									   <div class="t-s-b-inner-in">
									     <img src="'. $tweet->tweetImage .'" class="imagePopup"/>
									   </div>
									  </div>
									</div>
									<!--tweet show body end-->';
								}
							echo '</div>
							<div class="t-show-footer">
								<div class="t-s-f-right">
									<ul> 
										<li><button><a href="#"><i class="fa fa-share" aria-hidden="true"></i></a></button></li>	
										<li><button><a href="#"><i class="fa fa-retweet" aria-hidden="true"></i></a></button></li>
										<li><button><a href="#"><i class="fa fa-heart-o" aria-hidden="true"></i></a></button></li>
											<li>
											<a href="#" class="more"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a>
											<ul> 
											  <li><label class="deleteTweet">Delete Tweet</label></li>
											</ul>
										</li>
									</ul>
								</div>
							</div>
						</div>
						</div>
						</div>';
			}
		}


		public function getTrendByHash($hashtag) {
			$stmt = $this->pdo->prepare("SELECT * FROM trends WHERE hashtag LIKE ? LIMIT 5");
			$stmt->bindValue(1, $hashtag.'%');
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}


		public function getMention($mention) {
			$stmt = $this->pdo->prepare('SELECT user_id, username, screen_name, profile_image FROM users WHERE username LIKE ? OR screen_name LIKE ? LIMIT 5');
			$stmt->bindValue(1, $mention. '%');
			$stmt->bindValue(2, $mention. '%');
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}

		public function addTrend($hashtag) {
			preg_match_all("/#+([a-zA-Z0-9_]+)/i", $hashtag, $matches);
			if($matches) {
				$result = array_values($matches[1]);

			}
			$sql = 'INSERT INTO trends (hashtag, createdOn) VALUES (?, CURRENT_TIMESTAMP)';
			foreach($result as $trend) {
				
				if($stmt = $this->pdo->prepare($sql)) {
					$stmt->bindValue(1, $trend. '%');
					$stmt->execute(array($trend));
				}
			}
		}

	}

?>
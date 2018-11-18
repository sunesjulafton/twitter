<?php

	class Tweet extends User {
		
		function __construct($pdo) {
			$this->pdo = $pdo;
		}

		public function tweets($user_id) {
			$stmt = $this->pdo->prepare('SELECT * FROM tweets LEFT JOIN users ON tweetBy = user_id WHERE tweetBy = ? AND retweetID = 0 OR tweetBy = user_id AND retweetBy != ?');
			$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $user_id, PDO::PARAM_INT);

			$stmt->execute();
			$tweets = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			foreach ($tweets as $tweet) {
				$likes = $this->likes($user_id, $tweet->tweetID);
				$retweet = $this->checkRetweet($tweet->tweetID, $user_id);
				$user = $this->userData($tweet->retweetBy);
				echo '<div class="all-tweet">
						<div class="t-show-wrap">	
						 <div class="t-show-inner">
							'.(($retweet['retweetID'] === $tweet->retweetID OR $tweet->retweetID > 0) ? '
							<div class="t-show-banner">
								<div class="t-show-banner-inner">
									<span><i class="fa fa-retweet" aria-hidden="true"></i></span><span>'.$user->screen_name.' Retweeted</span>
								</div>
							</div>' : '').'
							

							'.((!empty($tweet->retweetMsg) && $tweet->tweetID === $retweet['tweetID'] or $tweet->retweetID > 0) ? '<div class="t-show-head">
								<div class="t-show-img">
									<img src="'.BASE_URL.$user->profile_image.'"/>
								</div>
								<div class="t-s-head-content">
									<div class="t-h-c-name">
										<span><a href="'.BASE_URL.$user->screen_name.'">'.$user->screen_name.'</a></span>
										<span>@'.$user->username.'</span>
										<span>'.$retweet['postedOn'].'</span>
									</div>
									<div class="t-h-c-dis">
										'.$this->getTweetLinks($tweet->retweetMsg).'
									</div>
								</div>
							</div>
							<div class="t-s-b-inner">
								<div class="t-s-b-inner-in">
									<div class="retweet-t-s-b-inner">
										'.((!empty($tweet->tweetImage)) ? '
										<div class="retweet-t-s-b-inner-left">
											<img src="'.BASE_URL.$tweet->tweetImage.'"/>	
										</div>
										' : '').'
										<div class="retweet-t-s-b-inner-right">
											<div class="t-h-c-name">
												<span><a href="'.BASE_URL.$tweet->username.'">'.$tweet->screen_name.'</a></span>
												<span>@'.$tweet->username.'</span>
												<span>'.$tweet->postedOn.'</span>
											</div>
											<div class="retweet-t-s-b-inner-right-text">		
												'.$tweet->status.'
											</div>
										</div>
									</div>
								</div>
							</div>


							' : '


							<div class="t-show-popup" data-tweet="'.$tweet->tweetID.'">
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
											'. $this->getTweetLinks($tweet->status) .'
										</div>
									</div>
								</div>'.
								((!empty($tweet->tweetImage)) ? 
									'
									<!--tweet show head end-->
									<div class="t-show-body">
									  <div class="t-s-b-inner">
									   <div class="t-s-b-inner-in">
									     <img src="'. $tweet->tweetImage .'" class="imagePopup"/>
									   </div>
									  </div>
									</div>
									<!--tweet show body end-->
								' : '') . ' 
							</div> ' ).'
							<div class="t-show-footer">
								<div class="t-s-f-right">
									<ul> 
										<li><button><i class="fa fa-share" aria-hidden="true"></i></button></li>	
										<li>'.(($tweet->tweetID === $retweet['retweetID']) ? '<button class="retweeted" data-tweet="'. $tweet->tweetID.'" data-user="'.$tweet->tweetBy.'"><i class="fa fa-retweet" aria-hidden="true"></i><span class="retweetCount">'.$tweet->retweetCount.'</span></button>' : '<button class="retweet" data-tweet="'. $tweet->tweetID.'" data-user="'.$tweet->tweetBy.'"><i class="fa fa-retweet" aria-hidden="true"></i><span class="retweetCount">'.(($tweet->retweetCount > 0) ? $tweet->retweetCount : '').'</span></button>').'</li>
										<li>'.(($likes['likeOn'] === $tweet->tweetID) ? '<button class="unlike-btn" data-tweet="' . $tweet->tweetID.'" data-user="'. $tweet->tweetBy.'"><i class="fa fa-heart" aria-hidden="true"></i><span class="likesCounter">'. $tweet->likesCount .'</span></button>' : '<button class="like-btn" data-tweet="' . $tweet->tweetID.'" data-user="'. $tweet->tweetBy.'"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="likesCounter">'. (($tweet->likesCount > 0) ? $tweet->likesCount : '') . '</span></button>').'</li>
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

		public function getTweetLinks($tweet) {
			$tweet = preg_replace("/(https?:\/\/)([\w]+.)([\w\w.]+)/", "<a href='$0' target='_balnk'>$0</a>", $tweet);
			$tweet = preg_replace("/#([\w]+)/", "<a href='".BASE_URL."hashtag/$1'>$0</a>", $tweet);
			$tweet = preg_replace("/@([\w]+)/", "<a href='".BASE_URL."$1'>$0</a>", $tweet);
			return $tweet;
		}

		public function getPopupTweet($tweet_id){
			$stmt = $this->pdo->prepare('SELECT * FROM tweets, users WHERE tweetID = ? AND tweetBy = user_id');
			$stmt->bindParam(1, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ);
		}

		public function retweet($tweet_id, $user_id, $get_id, $comment) {

			$stmt = $this->pdo->prepare('UPDATE tweets SET retweetCount = retweetCount + 1 WHERE tweetID = ?');
			$stmt->bindParam(1, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();

			$stmt = $this->pdo->prepare('INSERT INTO tweets (status, tweetBy, tweetImage, retweetID, retweetBy, postedOn, likesCount, retweetCount, retweetMsg) SELECT status, tweetBy, tweetImage, tweetID, ?, CURRENT_TIMESTAMP, likesCount, retweetCount, ? FROM tweets WHERE tweetID = ?');
			$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $comment, PDO::PARAM_STR);
			$stmt->bindParam(3, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();

		}

		public function checkRetweet($tweet_id, $user_id) {
			$stmt = $this->pdo->prepare('SELECT * FROM tweets WHERE retweetID = ? AND retweetBy = ?');
			$stmt->bindParam(1, $tweet_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $user_id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}

		public function comments($tweet_id) {
			$stmt = $this->pdo->prepare('SELECT * FROM comments LEFT JOIN users ON commentBy = user_id WHERE commentOn = ?');
			$stmt->bindParam(1, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}

		public function addLike($user_id, $tweet_id, $get_id) {
			$stmt = $this->pdo->prepare('UPDATE tweets SET likesCount = likesCount +1 WHERE tweetID = ?');
			$stmt->bindParam(1, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();

			$this->create('likes', array('likeBy' => $user_id, 'likeOn' => $tweet_id));

		}

		public function unlike($user_id, $tweet_id, $get_id) {
			$stmt = $this->pdo->prepare('UPDATE tweets SET likesCount = likesCount -1 WHERE tweetID = ?');
			$stmt->bindParam(1, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();

			$stmt = $this->pdo->prepare("DELETE FROM likes WHERE likeBy = ? AND likeOn = ?");
			$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();

		}



		public function likes($user_id, $tweet_id) {
			$stmt = $this->pdo->prepare('SELECT * FROM likes WHERE likeBy = ? AND likeOn = ?');
			$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $tweet_id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}


	}

?>
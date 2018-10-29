<?php

	
	if(isset($_POST['signup'])) {
		
		$screen_name = $_POST['screen_name'];
		$password = $_POST['password'];
		$email = $_POST['email'];
		$error = "";

		if(empty($screen_name) or empty($password) or empty($email)) {
			$error = "All fields are required!";
		}
		else {
			$screen_name = $getFromU->checkInput($screen_name);
			$password = $getFromU->checkInput($password);
			$email = $getFromU->checkInput($email);
			
			if(!filter_var($email)) {
				$error = "Invalid email format!";
			}
			else if(strlen($screen_name) > 20) {
				$error = "Name must be in between 6-20 characters!";
			}
			else if(strlen($password) < 5) {
				$error = "Password must be at least 5 characters long!";
			}
			else {
				if($getFromU->checkEmail($email) === true) {
					$error = "Email is already in use!";
				}
				else {
					
					$password = password_hash($password, PASSWORD_DEFAULT);
					$user_id = $getFromU->create('users', array('email' => $email, 'password' => $password, 'screen_name' => $screen_name));
					$_SESSION['user_id'] = $user_id;
					header('Location: includes/signup.php?step=1');
				}
			}
		}
	}
?>

<form method="post">
<div class="signup-div"> 
	<h3>Sign up </h3>
	<ul>
		<li>
		    <input type="text" name="screen_name" placeholder="Full Name"/>
		</li>
		<li>
		    <input type="email" name="email" placeholder="Email"/>
		</li>
		<li>
			<input type="password" name="password" placeholder="Password"/>
		</li>
		<li>
			<input type="submit" name="signup" Value="Signup for Twitter">
		</li>
		<?php 
			if(isset($error)) {
				echo '<li class="error-li">
			  <div class="span-fp-error">'. $error .'</div>
			 </li> ';
		}

	?>
	</ul>
	
	 
	
</div>
</form>
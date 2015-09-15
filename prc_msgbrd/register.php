<!DOCTYPE html>

<html >

  <head>

    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

    <title>Registration</title>

</head> 

  <body>

    <div id="main">

      <?php
      require_once("configmsgbrd.php");

      if (isset($_POST['submitted'])) { // Handle the form.

	// Check for an email address.

	if (preg_match ('%^[A-Za-z0-9._\%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$%', stripslashes(trim($_POST['email'])))) {

		$e = escape_data($_POST['email']);

	} else {

		$e = FALSE;

		echo '<p><font color="red" size="+1">Please enter a valid email address!</font></p>';

	}
	
	// Check for a valid username

	if (preg_match ('%\A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])\S{8,}\z%', stripslashes(trim($_POST['userid'])))) {

	 $ui = escape_data($_POST['userid']);
	

	} else {

		$ui = FALSE;

		echo '<p><font color="red" size="+1">Please enter a valid userid!</font></p>';

	}

	// Check for a password and match against the confirmed password.

	if (preg_match ('%\A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])\S{8,}\z%', stripslashes(trim($_POST['password1'])))) {

		if (($_POST['password1'] == $_POST['password2']) && ($_POST['password1'] != $_POST['userid'])) {

			$p = escape_data($_POST['password1']);

		} elseif ($_POST['password1'] == $_POST['userid']) {
			$p = FALSE;

			echo '<p><font color="red" size="+1">Your password cannot be the same as the userid!</font></p>';
		} else {
			$p = FALSE;

			echo '<p><font color="red" size="+1">Your password did not match the confirmed password!</font></p>';

		}

	} else {

		$p = FALSE;

		echo '<p><font color="red" size="+1">Please enter a valid password!</font></p>';

	}
	
	// PHP Code for the CAPTCHA System

		$captchchk = 1;
  		require_once('./includes/recaptchalib.php');
  		$privatekey = "6LfXR8ASAAAAAKpztg_bZb27P7KwUwFZYPi0pvOA";
  		$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

  	if (!$resp->is_valid) {
    	// What happens when the CAPTCHA was entered incorrectly
   	 	echo '<p><font color="red" size="+1">The CAPTCHA Code wasn\'t entered correctly!</font></p>';
  		$captchchk = 0;
  	}

	if ($e && $p && $ui && $captchchk) { // If everything's OK.

		// Make sure the userid is available.

		$query = "SELECT username FROM users WHERE username='$ui'";		

		$result = mysql_query ($query) or trigger_error("Sorry there is an account assigned to that userid");

		if (mysql_num_rows($result) == 0) { // Available.

			// Create the activation code.
			// Create a random number with rand. 
			// Use it as a seed for uniqid, which when set to true generates a random number 23 digits in length
			// Use it to seed md5 that creates a random string 32 characters in length

			$a = md5(uniqid(rand(), true));

			// Add the user. By entering values in a different order from the form sql injection can be limited

			$query = "INSERT INTO users (email, passwd, active, username) VALUES ('$e', SHA('$p'), '$a', '$ui')";		

			// By using mysql_query I can make sure only one query is submitted blocking sql injection
			// Never use the php multi_query function
			$result = mysql_query ($query) or trigger_error("Sorry an error occurred and the account could not be created");

			// Check that the effected rows was equal to 1 in the last query. Should log if greater than
			if (mysql_affected_rows() == 1) { // If it ran OK.

				// Send the email.

				$body = "Thank you for registering. To activate your account, please click on this link:<br />";
				
				// mysql_insert_id() retrieves the value of the last auto_incremented id
				// Attach the random activation code in the link sent to the email
				$body .= "http://localhost/activate.php?x=" . mysql_insert_id() . "&y=$a";

				mail($_POST['email'], 'Registration Confirmation', $body, 'From: derekbanas@verizon.net');


				// Finish the page.

				echo '<br /><br /><h3>Thank you for registering! A confirmation email has been sent to your address. Please click on the link in that email in order to activate your account.</h3>';

				exit();				

			} else { // If it did not run OK.

				echo '<p><font color="red" size="+1">You could not be registered due to a system error. We apologize for any inconvenience.</font></p>'; 

                exit();				

			}		

		} else { // The email address is not available.

			echo '<p><font color="red" size="+1">That email address has already been registered. If you have forgotten your password, use the link to have your password sent to you.</font></p>'; 

		}


	} else { // If one of the data tests failed.

		echo '<p><font color="red" size="+1">Please try again.</font></p>';		

	}

	// mysql_close(); // Close the database connection.

} // End of the main Submit conditional.

?>

<h1>Register</h1>

<form action="register.php" method="post">

	<fieldset>

	<p><b>Email Address:</b> <input type="text" name="email" size="40" maxlength="40" value="<?php if (isset($_POST['email'])) echo $_POST['email']; ?>" /> </p>
	
	<p><b>Username:</b> <input type="password" name="userid" size="20" maxlength="20" /> <small>Must contain a letter of both cases, a number and a minimum length of 8 characters.</small></p>

	<p><b>Password:</b> <input type="password" name="password1" size="20" maxlength="20" /> <small>Must contain a letter of both cases, a number and a minimum length of 8 characters.</small></p>

	<p><b>Confirm Password:</b> <input type="password" name="password2" size="20" maxlength="20" /></p>
	
	<?php
          require_once('./includes/recaptchalib.php');
          $publickey = "6LfXR8ASAAAAAAaDH3VUIOuMbqAHQEfmSr0_W-Oq"; // you got this from the signup page
          echo recaptcha_get_html($publickey);
    ?>


	</fieldset>

	<div align="center"><input type="submit" name="submit" value="Register" /></div>

	<input type="hidden" name="submitted" value="TRUE" />

</form>

    </div>

  </body>

</html>




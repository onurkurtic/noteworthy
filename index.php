<?php

  session_start();

  define("ONE_HOUR", 60*60);
  define("ONE_YEAR", 60*60*24*365);

  define("FORM_ERROR", "<p>There were error(s) in your form:</p>");
  define("AUTHENTICATION_ERROR", "That email/password combination could not be found.");
  define("SIGN_UP_ERROR", "<p>Could not sign you up - please try again later.</p>");
  define("EMAIL_TAKEN_ERROR", "That email address is taken.");
  define("EMAIL_REQUIRED_ERROR", "An email address is required.");
  define("PASSWORD_REQUIRED_ERROR", "A password is required.");

  $error = "";  

  function logOut() {
    unset($_SESSION);
    setcookie("id", "", time() - ONE_HOUR);
    $_COOKIE["id"] = "";  

    session_destroy();		
  }

	function userLoggedIn() {
		(array_key_exists("id", $_SESSION) AND $_SESSION['id']) OR (array_key_exists("id", $_COOKIE) AND $_COOKIE['id'])
	}

  function validateFormParams() {
    if (!$_POST['email']) {
      $error .= EMAIL_REQUIRED_ERROR;
    } 

    if (!$_POST['password']) {
      $error .= PASSWORD_REQUIRED_ERROR;
    } 

    return $error
  }

  function handleSignUp() {
    $query = "SELECT id FROM `users` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."' LIMIT 1";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {
      $error = EMAIL_VALIDATION_ERROR;
    } else {
      $query = "INSERT INTO `users` (`email`, `password`) VALUES ('".mysqli_real_escape_string($link, $_POST['email'])."', '".mysqli_real_escape_string($link, $_POST['password'])."')";

      if (!mysqli_query($link, $query)) {
        $error = SIGN_UP_ERROR;
      } else {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE `users` SET password = '".mysqli_real_escape_string($link, $hash)."' WHERE id = ".mysqli_insert_id($link)." LIMIT 1";
        $id = mysqli_insert_id($link);

        mysqli_query($link, $query);

        $_SESSION['id'] = $id;

        if ($_POST['stayLoggedIn'] == '1') {
          setcookie("id", $id, time() + ONE_YEAR);
        } 

        header("Location: loggedinpage.php");
      }
    }
  }

  function handleLogin() {
    $query = "SELECT * FROM `users` WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);

    if (isset($row)) {
      $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

      if (password_verify($_POST["password"], $hashedPassword)) {
        $_SESSION['id'] = $row['id'];
        
        if (isset($_POST['stayLoggedIn']) AND $_POST['stayLoggedIn'] == '1') {
          
          setcookie("id", $row['id'], time() + ONE_YEAR);
        } 

        header("Location: loggedinpage.php");
      } else {
        $error = AUTHENTICATION_ERROR;
      }

    } else {
      $error = AUTHENTICATION_ERROR;
    }
  }

  if (array_key_exists("logout", $_GET)) {
    logOut()
  } else if (userLoggedIn()) {
    header("Location: loggedinpage.php");
  }

  if (array_key_exists("submit", $_POST)) {
    include("connection.php");
    $error = validateFormParams()

    if (!$error.empty) {
      $error = FORM_ERROR.$error;
    } else {
      if ($_POST['signUp'] == '1') {
        handleSignUp() 
      } else {
        handleLogin()    
      }
     }
  }


?>


<?php include("header.php"); ?>
 
  
   
    <div class="container" id="homePageContainer">
    
		<h1>Noteworthy</h1>
			
		<p class="lead">Store your thoughts permanently and securely.</p>
			
		<div id="error"><?php if ($error!="") { echo '<div class="alert alert-danger" role="alert">'.$error.'</div>';	} ?></div>
			
			<div id="card">
			
				<div class="formContainer front">

					<form method="post" id="signUpForm">

						<h3>Sign up now for free!</h3>

						<fieldset class="form-group">
							<input class="form-control" type="email" name="email" placeholder="Your Email">
						</fieldset>

						<fieldset class="form-group">
							<input class="form-control" type="password" name="password" placeholder="password">
						</fieldset>

						<div class="checkbox">
							<label>
								<input type="checkbox" name="stayLoggedIn" value=1>
								Stay logged in
							</label>	
						</div>

						<fieldset class="form-group">
							<input class="form-control" type="hidden" name="signUp" value="1">
						</fieldset>

						<fieldset class="form-group">
							<button class="btn btn-success" type="submit" name="submit">Sign Up!</button>
						</fieldset>

						<p class="footnote">Already have an account?<br><a class="showHide">Log In</a></p>


					</form>

				</div>

				<div class="formContainer back">

					<form method="post" id="logInForm" >
						
					<h3>Log into your account</h3>

						<fieldset class="form-group">
							<input class="form-control" type="email" name="email" placeholder="Your Email">
						</fieldset>

						<fieldset class="form-group">
							<input class="form-control" type="password" name="password" placeholder="password">
						</fieldset>

						<div class="checkbox">
							<label>
								<input type="checkbox" name="stayLoggedIn" value=1>
								Stay logged in
							</label>	
						</div>


						<fieldset class="form-group">
							<input class="form-control" type="hidden" name="signUp" value="0">
						</fieldset>

						<fieldset class="form-group">
							<button class="btn btn-primary" type="submit" name="submit">Log In!</button>
						</fieldset>

						<p class="footnote">Don't have an account yet?<br><a class="showHide">Sign Up</a></p>

					</form>	
				</div>
			</div>	
	  </div>

<?php include("footer.php"); ?>







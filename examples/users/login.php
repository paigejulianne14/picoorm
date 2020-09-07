<?php
/**
 * Package PicoORM
 *
 * @author  Paige Julianne Sullivan <paige@paigejulianne.com>
 * @license https://creativecommons.org/licenses/by-sa/4.0/
 * This is an example file
 */

if ($_REQUEST['submit']) {
	require_once '../src/PicoORM.php';
	$user = new User($_REQUEST['email'], 'email');
	var_export($_REQUEST);
	var_export($user);
	if ($user->id) {
		if (password_verify($_REQUEST['password'], $user->password)) {
			$_SESSION['user'] = $user->id;
			header('Location: ../index.php');
		} else {
			$err = 'Password invalid.';
		}
	} else {
		$err = 'Email not found.';
	}
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
</head>

<body>
<h1>Login
</h1>
<h3><?php
	echo $err; ?></h3>
<form id="form1" name="form1" method="post">
    <p>
        <label for="email"><br>
            Email Address:</label>
        <input name="email" type="text" required="required" id="email" autocomplete="on">
    </p>
    <p>
        <label for="password">Password:</label>
        <input name="password" type="password" required="required" id="password" autocomplete="on">
    </p>
    <p>
        <input type="submit" name="submit" id="submit" value="Submit">
    </p>
</form>
</body>
</html>

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
	// form posted, check for duplicate email addresses
	$userexists = User::checkForDuplicate('email', $_REQUEST['email']);
	if ($userexists) {
		$err = 'Email address already in use.';
	} else {
		User::createNew(array(
			'f_name'   => $_REQUEST['f_name'],
			'l_name'   => $_REQUEST['l_name'],
			'email'    => $_REQUEST['email'],
			'password' => password_hash($_REQUEST['password'], PASSWORD_DEFAULT),
		));
		// fetch the uid
		$user             = new User($_REQUEST['email'], 'email');
		$_SESSION['user'] = $user->id;
		header('Location: ../index.php');
	}
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="ISO-8859-1">
    <title>New User Registration</title>
</head>


<body>
<h1>Create New User </h1>
<h3><?php
	echo $err; ?></h3>
<form method="post" name="form1" id="form1" autocomplete="on">
    <p>
        <label for="f_name">First Name:</label>
        <input name="f_name" type="text" required="required" id="f_name" autocomplete="on">
    </p>
    <p>
        <label for="l_name">Last Name:</label>
        <input name="l_name" type="text" required="required" id="l_name" autocomplete="on">
    </p>
    <p>
        <label for="email">Email:</label>
        <input name="email" type="email" required="required" id="email" autocomplete="on">
    </p>
    <p>
        <label for="password">Password:</label>
        <input name="password" type="password" required="required" id="password">
    </p>
    <p>
        <input type="submit" name="submit" id="submit" value="Submit">
        <input type="reset" name="reset" id="reset" value="Reset">
    </p>
</form>


</body>
</html>
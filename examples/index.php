<?php

/**
 * Package PicoORM
 *
 * @author  Paige Julianne Sullivan <paige@paigejulianne.com>
 * @license https://creativecommons.org/licenses/by-sa/4.0/
 * This is an example file
 */

if ($_REQUEST['logout'] && $_SESSION['user']) {
	session_destroy();
	header("Location: /");
}

?>


<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Index</title>

</head>

<body>


<?php
if ($_SESSION['user']): ?>

    User logged in<br>
    <a href="?logout=true">Logout</a>


<?php
else: ?>
    <a href="users/login.php">Login</a><br>
    <a href="users/reg.php">Reg New User</a>
<?php
endif; ?>
</body>
</html>
<?php
$pageTitle = 'Home';
require_once('head.php');

if ((! isset($_SESSION['isAuthenticated'])) || (! $_SESSION['isAuthenticated'])) {
?>
<form id="frmIndex" class="" type="post" action="">
	<input type="text" id="username" name="username" value="" />
	<input type="password" id="password" name="password" value="" />
	<input type="submit" id="submit_login" name="submit_login" value="Log In" />

</form>
<?php
} else
{
?>

You are logged in as <?php echo $_SESSION['username']; ?>. <input type="button" name="logout" id="logout_btn" value="log out"/>

<?php
$_SESSION['isAuthenticated'] = false;
}
require_once('foot.php');
?>
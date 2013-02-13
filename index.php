<?php
$pageTitle = 'Home';
require_once('head.php');


?>
<form id="frmIndex" class="" type="post" action="">

	<input type="text" id="username" name="username" value="" />
	<input type="password" id="password" name="password" value="" />
	<input type="submit" id="submit_login" name="submit_login" value="Log In" />

</form>
<?php

require_once('foot.php');
?>
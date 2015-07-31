<?php
    $pageTitle = 'Manage Users';
    require_once('head_pre_output.php');
    require_once('head_output.php');

    //use a key that is always true
    $all_users = User::getAllFromDb(['flag_delete'=>0],$DB);
?>
        <legend>Manage Users</legend>
        <?php
        if ($USER->flag_is_system_admin) {
            foreach ($all_users as $user) {
                echo "<div id='" . $user->username . "'>";
                echo "<strong>" . $user->fname . ' ' . $user->lname . "</strong>";
                if ($user->username) {
                    echo "<ul><li>Username: " . $user->username . "</li>";
                }
                if ($user->email) {
                    echo "<li>Email: " . $user->email . "</li>";
                }
                if ($user->advisor) {
                    echo "<li>Advisor: " . $user->advisor . "</li></ul>";
                }
                echo "</div>";
            }
        }else{
            util_redirectToAppHome('failure', 51);
        }
        ?>
<?php
    require_once('foot.php');
?>
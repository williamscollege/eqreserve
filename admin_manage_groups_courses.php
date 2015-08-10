<?php
$pageTitle = 'Manage LDAP Groups/Courses';
require_once('head_pre_output.php');
require_once('head_output.php');

//use a key that is always true
$all_inst_groups = InstGroup::getAllFromDb(['flag_delete'=>0],$DB);

?>
    <legend>Manage LDAP Groups/Courses</legend>
        <?php
            if ($USER->flag_is_system_admin) {
                foreach ($all_inst_groups as $inst_group) {
                    echo "<div id='".$inst_group->name."'>";
                    if ($inst_group->name) {
                        echo $inst_group->toListItemLinked() . "\n";
                    }
                    $members = InstMembership::getAllFromDb(['inst_group_id'=>$inst_group->inst_group_id],$DB);
//                    util_prePrintR($members);
                    if($members){
                        echo "<ul>";
                        foreach($members as $member){
//                            util_prePrintR($member);
                            $user = User::getOneFromDb(['user_id'=>($member->user_id)],$DB);
//                            util_prePrintR($user);
                            //get rid of deleted and banned members
                            if(!($user->flag_delete == '' || $user->flag_is_banned == '')){
                                echo "<li>" . $user->fname . ' ' . $user->lname . ' (' . $user->username . ')' . "</li>";
                            }
                        }
                        echo "</ul>";
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
<?php
require_once('cl_head.php');

/*
 * this script sends emails to all users that have a reservation coming up in the next 2 days. only one email is sent to
 * a user. that email contains three sections: consumer reservations (normal), manager reservations (manager only), and
 * reservations on groups the user manages (manager only).
 *
 * NOTE: since the look-ahead is 2 days and this runs 1/day, that means that people get 2 reminders about each reservation
 */

# 1. get all the upcoming time blocks (cur time to cur time + 48 hours); for each time block, get the schedule and reservations

# 2. build up the user reservation info hash
/*
 * user_id :
 *      name
 *      email
 *      list of ids of managed eq groups
 *      consumer reservations :
 *          * time_block_id :
 *              begin_time
 *              end time
 *              eq group name
 *              eq item names (text)
 *      manager reservations :
 *          * time_block_id :
 *              begin_time
 *              end time
 *              eq group name
 *              eq item names (text)
 *      reservations on managed groups
 *          * time_block_id :
 *              user full name
 *              user username
 *              user email
 *              begin_time
 *              end time
 *              eq group name
 *              eq item names (text)
 */
#  for each time block, look at schedule; if the user doesn't already have a hash entry, build one; populate the res info hash based on that time block info

# 3. build and send the emails
/*
 * cycle through hash ids; build each email from that hash entry; sort each reservation group by begin time; make the email; send it and sleep for a moment to avoid overwhelming the mail server
 */

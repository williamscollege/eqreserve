<?php
require_once('../classes/eq_item.class.php');

require_once('/head_ajax.php');

/**
 * Created by JetBrains PhpStorm.
 * User: cwarren
 * Date: 1/23/14
 * Time: 3:43 PM
 * To change this template use File | Settings | File Templates.
 */

/*
 * TODO
 * 1. try to load the given item; make sure it exists, make sure the name of the file to upload matches that in the DB, make sure the to_be_upload flag is true
 * 2. after upload, set the to_be_upload flag to be false and update the item record
 * 3. return status: success (or other approp status), and relevant item id and/or full image tag
 */


$intItemID        = htmlentities((isset($_REQUEST["ajaxVal_ItemID"])) ? $_REQUEST["ajaxVal_ItemID"] : 0);

#------------------------------------------------#
# Set default return value
#------------------------------------------------#
$results = [
    'status' => 'failure',
    'message' => 'unknown reason'
];


$ei = EqItem::getOneFromDb(['eq_item_id' => $intItemID], $DB);
if (! $ei->matchesDb) {
    $results['message']       = 'Could not find an item with id '.$intItemID;
    echo json_encode($results);
    exit;
}

if ($ei->image_file_name != $_FILES['ajaxVal_file']['name']) {
    $results['message']       = 'The specified item has a different image associated with it';
    echo json_encode($results);
    exit;
}

if (! $ei->flag_image_to_be_uploaded) {
    $results['message']       = 'The specified item does not have an image upload queued';
    echo json_encode($results);
    exit;
}

if (! isset($_FILES['ajaxVal_file'])) {
    $results['message']       = 'no file provided for upload';
    echo json_encode($results);
    exit;
}


$base_name = $_FILES['ajaxVal_file']['name'];
$ext = pathinfo($base_name, PATHINFO_EXTENSION);

$canonical_image_file_name = 'for_item_'.$intItemID.'_'.util_genRandomAlphNumString(24).".$ext";

move_uploaded_file($_FILES['ajaxVal_file']['tmp_name'], "../item_image/$canonical_image_file_name");

$ei->image_file_name = $canonical_image_file_name;
$ei->flag_image_to_be_uploaded = false;

$ei->updateDb();

$results['status']       = 'success';
$results['message']      = 'image '.$base_name.' uploaded and set for item '.$intItemID;
$results['html_output']  = "<br/><img src=\"item_image/$canonical_image_file_name\" id=\"itemImageFor$intItemID\" class=\"item-image\" data-for-item=\"$intItemID\" />";

echo json_encode($results);
exit;
<?php

if (array_key_exists('SERVER_NAME',$_SERVER)) {
    echo 'no web access to this script';
    exit;
}
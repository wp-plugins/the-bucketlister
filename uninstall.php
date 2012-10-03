<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

include('bucketlister.php');
LI_Bucketlister::deleteData();


?>
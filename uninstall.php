<?php
# separate procedural uninstall file is the official recommended way of cleaning up after a plugin when it is deleted


if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();


# delete options
delete_option('zigtweets');


# EOF

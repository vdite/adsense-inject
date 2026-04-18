<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('adsense_post_ids');
delete_option('adsense_client_id');

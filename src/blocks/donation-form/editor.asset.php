<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

return array(
    'dependencies' => array(
        'wp-blocks',
        'wp-element',
        'wp-block-editor',
        'wp-components',
        'wp-server-side-render',
        'wp-dom-ready'
    ),
    'version' => defined('BMCF_VERSION') ? BMCF_VERSION : '0.1.0'
);

<?php
/*
Plugin Name:  Opensea NFT Plugin
Plugin URI:   https://nftu.io/opensea-wordpress-plugin/?utm_source=WordPress&utm_medium=Admin&utm_campaign=Opensea
Description:  The Opensea WordPress plugin allows you to embed any single NFT quickly and easily anywhere within your website with a simple shortcode. You can also make a collections landing page too. Simply install the plugin and follow the instructions on the Settings page.
Version:      1.0.3
Author: Alex Moss
Author URI: https://alex-moss.co.uk/
License: GPL v3

Copyright (C) 2010-2021, Alex Moss - alex@firecask.com
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Alex Moss or pleer nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/
if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
	require 'class-admin.php';
else
	require 'class-frontend.php';

// Add settings link on plugin page
function opensea_link($links) {
	$settings_link = '<a href="options-general.php?page=opensea&t=general">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'opensea_link' );

add_filter( 'page_template', 'opensea_storefront_page_template' );
function opensea_storefront_page_template( $page_template ) {
	$options = get_option('opensea');
	global $post;
	$template_name =get_post_meta($post->ID,'_wp_page_template',true);
	if($template_name == 'page-storefront.php'){ 
		
    //if (is_page_template( dirname( __FILE__ ) . '/page-storefront.php' )) {
		$page_template = dirname( __FILE__ ) . '/page-storefront.php';
		return $page_template;
	} else {
		return $page_template;
	}
}

add_filter( 'theme_page_templates', 'opensea_template', 10, 3 );
function opensea_template( $page_templates, $theme, $post ){
	$page_templates['page-storefront.php'] = 'Opensea Storefront';
	return $page_templates;

}

add_action( 'wp_enqueue_scripts', 'opensea_enqueue_scripts' ); 
function opensea_enqueue_scripts() {
	wp_register_script('opensea-nft-card', 'https://unpkg.com/embeddable-nfts/dist/nft-card.min.js', array('jquery'),'1.1', true);
}

if ( ! function_exists( 'opensea_fs' ) ) {
    // Create a helper function for easy SDK access.
    function opensea_fs() {
        global $opensea_fs;

        if ( ! isset( $opensea_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $opensea_fs = fs_dynamic_init( array(
                'id'                  => '8044',
                'slug'                => 'opensea',
                'type'                => 'plugin',
                'public_key'          => 'pk_59a9cf6937ce7ace0f584b8196126',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'opensea',
                    'override_exact' => true,
                    'first-path'     => 'options-general.php?page=opensea',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ) );
        }

        return $opensea_fs;
    }

    // Init Freemius.
    opensea_fs();
    // Signal that SDK was initiated.
    do_action( 'opensea_fs_loaded' );

    function opensea_fs_settings_url() {
        return admin_url( 'options-general.php?page=opensea' );
    }

    opensea_fs()->add_filter('connect_url', 'opensea_fs_settings_url');
    opensea_fs()->add_filter('after_skip_url', 'opensea_fs_settings_url');
    opensea_fs()->add_filter('after_connect_url', 'opensea_fs_settings_url');
    opensea_fs()->add_filter('after_pending_connect_url', 'opensea_fs_settings_url');}
?>
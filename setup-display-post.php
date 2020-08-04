<?php
/**
 * Plugin Name: Setup Display Posts
 * Description: Simply adding Bill Erickson's <a href="https://www.billerickson.net/template-parts-with-display-posts-shortcode" target="_blank">code</a> for using Display Post plugin's layout feature.
 * Version: 1.0.0
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function setup_dps_template_part( $output, $original_atts ) {
    
	// Return early if our "layout" attribute is not specified
	if( empty( $original_atts['layout'] ) )
		return $output;

	ob_start();

	// The line below is Bill's code which I can't make it to work
	//get_template_part( get_stylesheet_directory_uri().'/partials/dps/', 'dps-'.$original_atts[ "layout" ].'.php' );

    // THEME DIRECTORY | check if target template is found in VIEW folder
    $theme_view = get_stylesheet_directory().'/partials/views/'.$original_atts[ "layout" ].'.php';
    if( is_file( $theme_view ) ) {

        include $theme_view;

    } else {

        // PLUGIN DIRECTORY | template not found in theme's, use plugin location
        $plug_temp = plugin_dir_path( __FILE__ ).'templates/'.$original_atts[ "layout" ].'.php';
        if( is_file( $plug_temp ) ) {

            include $plug_temp;

        } else {

            // no specified template found, return default
            include plugin_dir_path( __FILE__ ).'templates/setup-display-post-default.php';

        }

    }

	$new_output = ob_get_clean();
	
	if( !empty( $new_output ) )
		$output = $new_output;
	
	return $output;

}
add_action( 'display_posts_shortcode_output', 'setup_dps_template_part', 10, 2 );


// HANDLE IMAGES TO BE DISPLAYED
if( !function_exists( 'setup_show_images' ) ) {
    
    function setup_show_images( $images, $pid, $img_atts = FALSE ) {
        
        $out = ''; // declare a blank variable for AWS

        foreach( $images as $key => $value ) {
            
            /**
             * $key    --> this is the field name
             * $value  --> this is the image size
             */

            // catch the image size before the break
            $target_size = $value;
            
            // Check what image to display
            if( $key == 'featured' ) {
                
                // featured image
                $out .= get_the_post_thumbnail( $pid, $target_size );
                
            } else {
                
                // custom image
                $out .= wp_get_attachment_image( get_post_meta( $pid, $key, TRUE ), $target_size );
                
            }

            // break if $out has content
            if( $out ) {

                // validate link
                if( $img_atts[ 'permalink' ] ) {

                    // determine where to open the link
                    if( $img_atts[ 'target' ] )

                    $out = '<a href="'.get_the_permalink( $pid ).'">'.$out.'</a>';

                }                

                // exit loop if an image is confirmed
                break;
            }
            
        }
        
        // validate if there's an image, else, show placeholder image
        if( $out ) {

            return $out;

        } else {

            //return '<img src="'.get_stylesheet_directory_uri().'/assets/images/mock-featured.png" />';
            $spit_image = setup_pull_default_image( 'mock-featured', $target_size );

            if( $spit_image ) {

                return $spit_image;

            } else {

                // show how to upload the required image
                return '<p>Please download the default image from <a href="https://setup-be.basestructure.com/wp-content/themes/setup-be/assets/images/mock-featured.png" target="_blank">here</a> and follow the steps below:</p>
                        <p><ol>
                            <li>Login to WP-Admin</li>
                            <li>Click MEDIA</li>
                            <li>Click ADD NEW</li>
                            <li>Select or drag and drop the image(s) to the specified location</li>
                        </ol></p>
                        <p><img src="https://jakealmeda.com/images/upload-images-wordpres-media-library.gif" /></p>';

            }

        }
        
    }
    
}


// PULL DEFAULT IMAGE
if( !function_exists( 'setup_pull_default_image' ) ) {

    function setup_pull_default_image( $filename, $size = FALSE ) {

        if( !$size )
            $size = 'thumbnail';

        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => - 1,
        );

        $query_images = new WP_Query( $args );

        $images = array();
        foreach ( $query_images->posts as $image ) {
            
            if( $image->post_title == $filename ) {
                
                return wp_get_attachment_image( $image->ID, $size );
                
                break;
                
            }
            
        }

        setup_starter_reset_query();

    }

}


// RESET QUERIES
if( !function_exists( 'setup_starter_reset_query' ) ) {
    
    function setup_starter_reset_query() {

        wp_reset_query();
        wp_reset_postdata();

    }
    
}

/*
// Enqueue Style
function setup_display_post_enqueue() {

    // last arg is true - will be placed before </body>
    //wp_enqueue_script( 'spk_screensizer_js', plugins_url( 'js/asset.js', __FILE__ ), NULL, NULL, true );
    
    // enqueue styles
    wp_enqueue_style( 'setup_display_posts_style', plugins_url( 'css/setup-display-posts-style.css', __FILE__ ) );

}

if ( !is_admin() ) {

    // ENQUEUE SCRIPTS
    add_action( 'wp_enqueue_scripts', 'setup_display_post_enqueue' );

}
*/


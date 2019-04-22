<?php 
/*
Plugin Name: Jolly Brancher 
Plugin URI:  https://github.com/
Description: Let people fork your content into their own sites on a multisite blog
Version:     1.0
Author:      Tom Woodward
Author URI:  http://bionicteaching.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('wp_enqueue_scripts', 'jolly_rancher_load_scripts');

function jolly_rancher_load_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('jolly-brancher-main-js', plugin_dir_url( __FILE__) . 'js/jolly-brancher-main.js', $deps, $version, $in_footer); 
    wp_enqueue_style( 'jolly-brancher-main-css', plugin_dir_url( __FILE__) . 'css/jolly-brancher-main.css');
}


function make_brancher_html($forkword,$submitword,$showattribute){
	global $post;
 if (is_user_logged_in()) {  
                $blog_select = "
                <form id='jollbrancher-fork-form' action='" . get_the_permalink() . "' method='post'>
                <p>
                    <label>Which of your blogs would you like to $forkword this content to?</label><br/>
                    <select id='blog-select' name='blog-select'>
                        <option value=''>Select your blog</option>" . create_blogs_dropdown( get_blogs_of_current_user_by_role() ) . "</select>
                </p>
                  <fieldset id='submit'>
                    <input type='hidden' name='submit' value='1'/>
                    <input type='submit' value='$submitword' />
                </fieldset></form>";
                $blog_select_login_prompt = "";
            } 
            else {
                $blog_select = "";
                $blog_select = "<p>To fork this you'll have to <a href='" . wp_login_url(get_the_permalink()) . "'>login</a>.</p>";
            }
           if ($_POST) {
                $form_response = "";
              
               if (is_user_logged_in() && $_POST['blog-select'] ) {               	              
               	    //go elsewhere
               	    $base_content = $post->post_content;//$_POST['blog-post-content'];
               	    $home_url = $post->guid;
               	    $base_title = $post->post_title;
                    $remote_blog = get_remote_blog_info( $_POST['blog-select'] );
                    switch_to_blog($_POST['blog-select']);
					if ($showattribute==1){
						$base_content=$base_content . '<div style="width: 100%; display:block; margin: 20px 0; border-left:3px solid #000; padding-left:3px;">Forked from <a href="'.$home_url.'">'.$base_title.'</a></p>';
					}else {
						
						$forkpattern= '/\[(fork).*?\] ?/';
 					   $base_content=preg_replace($forkpattern,'',$base_content);
				}	
                    $forked_post = array(
						  'post_title'    => 'Fork of ' . $base_title,
						  'post_content'  => $base_content ,
						  'post_status'   => 'draft',						 
						);
						 
						// Insert the post into the database
						$destination_id = wp_insert_post( $forked_post );                   
						$dest_url = get_post_permalink($destination_id);
                    if ( $remote_blog ){
                       $form_response .= '<h2>SUCCESS!</h2>';  
					    $form_response .='<script type="text/javascript">';
					    $form_response .= 'window.location = "' . $dest_url . '"';
					    $form_response .= '</script>';
                    } else {
                        $form_response .= '<h2>SUCCESS2!</h2>';
					                   
                    }
                    return $form_response;
                } 
                
                else {
                    $form_response .= "<h2>CRUSHING DEFEAT!</h2>";                   
                    return $form_response;
                }
            }
                        
        return $blog_select;
  
}
  
 function create_blogs_dropdown($blogs){
                $choices = '';

                foreach ($blogs as $blog) {
                    $choices.= "<option value='" . $blog->userblog_id . "'>" . $blog->blogname . "</option>";
                }

                return $choices;
            }


function get_blogs_of_current_user_by_role() {

                $user_id = get_current_user_id();
                $role = 'administrator';

                $blogs = get_blogs_of_user( $user_id );

                foreach ( $blogs as $blog_id => $blog ) {

                    // Get the user object for the user for this blog.
                    $user = new WP_User( $user_id, '', $blog_id );

                    // Remove this blog from the list if the user doesn't have the role for it.
                    if ( ! in_array( $role, $user->roles ) ) {
                        unset( $blogs[ $blog_id ] );
                    }
                }

                return $blogs;
            }


 function get_network_name() {
                $network_id = get_blog_details()->site_id;
                $network_name = get_blog_details($network_id)->blogname;
                
                return $network_name;
            }

            function get_network_signup_url() {
                $network_signup_url = get_option('altlab_motherblog_options');

                if ( !$network_signup_url && ( $_SERVER['HTTP_HOST'] === 'rampages.us' ) ){
                    $network_signup_url['network-signup-url'] = "http://rampages.us/vcu-wp-signup.php";
                }

                return $network_signup_url['network-signup-url'];
            }

function get_remote_blog_info( $blogID ) {
                $remote_blog = new stdClass;
                
                switch_to_blog($blogID);
                    
                $remote_blog->url = get_site_url();
                $remote_blog->name = get_bloginfo('name');                  
                        
                // switch back to motherblog
                restore_current_blog();
                
                return $remote_blog;
            }            
			
			
			/**
			 * Generated by the WordPress Meta Box Generator at http://goo.gl/8nwllb
			 */
			class Rational_Meta_Box {
				private $screens = array(
					'post',
				);
				private $fields = array(
					array(
						'id' => 'add-fork-this-button',
						'label' => 'Add Fork This Button',
						'type' => 'checkbox',
					),
					array(
						'id' => 'fork-history-attribution-refork',
						'label' => 'Fork History, Attribution & Refork',
						'type' => 'checkbox',
					),
					array(
						'id' => 'text-for-fork-fork-duplicate',
						'label' => 'Text for Fork (fork, duplicate...)',
						'type' => 'text',
					),
				);

				/**
				 * Class construct method. Adds actions to their respective WordPress hooks.
				 */
				public function __construct() {
					add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
					add_action( 'save_post', array( $this, 'save_post' ) );
				}

				/**
				 * Hooks into WordPress' add_meta_boxes function.
				 * Goes through screens (post types) and adds the meta box.
				 */
				public function add_meta_boxes() {
					foreach ( $this->screens as $screen ) {
						add_meta_box(
							'fork-metabox',
							__( 'Fork Metabox', 'fork-metabox' ),
							array( $this, 'add_meta_box_callback' ),
							$screen,
							'side',
							'high'
						);
					}
				}

				/**
				 * Generates the HTML for the meta box
				 * 
				 * @param object $post WordPress post object
				 */
				public function add_meta_box_callback( $post ) {
					wp_nonce_field( 'fork_metabox_data', 'fork_metabox_nonce' );
					$this->generate_fields( $post );
				}

				/**
				 * Generates the field's HTML for the meta box.
				 */
				public function generate_fields( $post ) {
					$output = '';
					foreach ( $this->fields as $field ) {
						$label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
						$db_value = get_post_meta( $post->ID, 'fork_metabox_' . $field['id'], true );
						switch ( $field['type'] ) {
							case 'checkbox':
								$input = sprintf(
									'<input %s id="%s" name="%s" type="checkbox" value="1">',
									$db_value === '1' ? 'checked' : '',
									$field['id'],
									$field['id']
								);
								break;
							default:
								$input = sprintf(
									'<input id="%s" name="%s" type="%s" value="%s">',
									$field['id'],
									$field['id'],
									$field['type'],
									$db_value
								);
						}
						$output .= '<p>' . $label . '<br>' . $input . '</p>';
					}
					echo $output;
				}

				/**
				 * Hooks into WordPress' save_post function
				 */
				public function save_post( $post_id ) {
					if ( ! isset( $_POST['fork_metabox_nonce'] ) )
						return $post_id;

					$nonce = $_POST['fork_metabox_nonce'];
					if ( !wp_verify_nonce( $nonce, 'fork_metabox_data' ) )
						return $post_id;

					if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
						return $post_id;

					foreach ( $this->fields as $field ) {
						if ( isset( $_POST[ $field['id'] ] ) ) {
							switch ( $field['type'] ) {
								case 'email':
									$_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
									break;
								case 'text':
									$_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
									break;
							}
							update_post_meta( $post_id, 'fork_metabox_' . $field['id'], $_POST[ $field['id'] ] );
						} else if ( $field['type'] === 'checkbox' ) {
							update_post_meta( $post_id, 'fork_metabox_' . $field['id'], '0' );
						}
					}
				}
			}
			new Rational_Meta_Box;
			
			
add_filter( 'the_content', 'fork_the_content_in_the_main_loop' );

function fork_the_content_in_the_main_loop( $content ) {

    // Check if we're inside the main loop.
    if (   in_the_loop() && is_main_query() ) {
		//get metabox values
		$addFork= get_post_meta( get_the_ID(), 'fork_metabox_add-fork-this-button', true );
		if ($addFork){
			$attrFork= get_post_meta( get_the_ID(), 'fork-history-attribution-refork', true );
			
				$wordFork= get_post_meta( get_the_ID(), 'text-for-fork-fork-duplicate', true );
				$submitword="Go";
				 return $content . make_brancher_html($wordFork,$submitword,$attrFork) ;
				
		}
       
    }

    return $content;
}

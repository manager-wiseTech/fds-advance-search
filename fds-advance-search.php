<?php
/**
 * @package  FDS Advance Search
Plugin Name: FDS Advance Search
Plugin URI: http://www.finaldatasolutions.com/
Description: This is advance search plugin.
Version: 1.0.3
Author: Ibrar Ayoub
Author URI: http://www.finaldatasolutions.com/
License: GPLv2 or later
*/

require 'plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/manager-wiseTech/fds-advance-search/',
	__FILE__,
	'fds-advance-search'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('your-token-here');


defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

//adding styles and sript of select2
add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'selectcss', 'https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css' );
    wp_register_style( 'bootstrapcss', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css' );
    wp_enqueue_style( 'bootstrapcss' );
    wp_enqueue_style( 'selectcss' );
    wp_enqueue_script( 'bootstrapjs', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'selectjs', 'https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js', array( 'jquery' ) );
    
}
add_action("admin_menu","fds_advance_search");
function fds_advance_search()
{
	add_menu_page("FDS Advance Search","FDS Advance Search","manage_options","fds-advance-search","fds_advance_search_menu_fn","dashicons-search");
}
$plugin_dir_path = dirname(__FILE__);
function fds_advance_search_menu_fn()
{
	echo "<h1>Place this shortcode in footer widget.</h1>";
	echo"[fds-search-form]";
	echo "Add a new page and copy its url in fds-settings page and place this short code in newly created page.";
	echo "[fds-search-result]";
}

function fds_set_first_post_image($post) {
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = $matches [1][0];

  if(empty($first_img)){
    $first_img = "";
  }
  return $first_img;
}
function fds_form_creation($atts){
	$content = NULL;
$content .='<style type="text/css">

	</style>';	
	$default_key = NULL;
	if (isset($_GET['s'])) {
		$default_key = $_GET['s'];
	}
$content .= "<div>";
$content .= '<form class="advsrch_form" method="get" target="_blank" action="'. get_option('fds_search_option') .'">
	<div>
		<div><label class="advsrch_lbl" style="font-size:20px;">Enter Keyword:</label></div>
		<div><input style="width:100%;border-radius:4px;box-sizing: border-box;" type="text" name="srchbox" value="'.$default_key.'"></div>
	</div>
	<div style="margin-top:10px">		    
		      <select class="form-control fds-select" name="categories[]" multiple data-actions-box="true"title="Select Categories" data-live-search="true">';
		    $args = array(
			    'orderby' => 'name',
			    'hierarchical' => 1,
			    'taxonomy' => 'category',
			    'hide_empty' => 0,
			    'parent' => 0,
			    );
		     $categories = get_categories($args);  
	$content .=	'';
		    	foreach($categories as $category) {
		    	if ($category->name == "Uncategorized") {
		    		continue;
		    	}
		$content .=   '<option data-tokens="'.$category->name.'" value="'.$category->name.'">'.$category->name.'</option>';
		      		    $child_cat = get_categories(
										    array( 'parent' => $category->cat_ID )
										);
		      		    if ($child_cat) {
		      		    	
		      		    	foreach($child_cat as $cat)
		$content.=		'<option data-tokens="'.$cat->name.'" value="'.$cat->name.'">'.$cat->name.'></option>'; 
		      		    }
		     		    

		      		    } 
	$content .=' </select>
			
	</div>
	<div style="float:left;width:100%;text-align:center;">
		<input type="submit" style="font-size:18px;border-radius:5px; padding: 0px 50px;" name="search" value="Search">
	</div>
</form></div>';
$content .= "<div style='clear:both'></div>";
$content .= '<script type="text/javascript">
(function($) {
					$(".fds-select").selectpicker();
			})( jQuery );
			
</script>';
		return $content;
}
add_shortcode('fds-search-form','fds_form_creation');
function fds_result_generator(){
	if (isset($_GET['search']))
	{
	$keyword = $_GET['srchbox'];
	if (isset($_GET['categories'])) {
	$categories = $_GET['categories'];
	$cat = null;
		if (!empty($categories)) {
		foreach ($categories as $category) {
			$cat .= $category.','; 
			}
			 $cat = ltrim($cat);
			}	
	}
	
	$args = array(
		's'=>$keyword,
		'category_name' => $cat,
		'posts_per_page' => -1,
		'post_type' => 'post'
	);
	$data="";
	$filter_form = '';
	$filter_form .= '<style type="text/css">
	</style>
	<div>
	<form method="get" target="_blank" class="advsrch_form" action="'. get_option('fds_search_option') .'">
	<div>
		<div><label class="advsrch_lbl" style="font-size:20px;">Enter Keyword: </label></div>
		<div><input style="width:100%;border-radius:4px;box-sizing: border-box;" type="text" name="srchbox" value="'.$keyword.'"></div>
	</div>
	<div style="margin-top:10px">
		<select class="form-control fds-select" name="categories[]" multiple data-actions-box="true"title="Select Categories" data-live-search="true">';
	$args = array(
			    'orderby' => 'name',
			    'hierarchical' => 1,
			    'taxonomy' => 'category',
			    'hide_empty' => 0,
			    'parent' => 0,
			    );
	$categories = get_categories($args);  
	$filter_form .=	'';
		    	foreach($categories as $category) {
		    	if ($category->name == "Uncategorized") {
		    		continue;
		    	}
		    	$selected_cat = explode(',', $cat);
		    	if (in_array($category->name, $selected_cat))
				  {
				  	$checked = "selected";
				  }
				else
				  {
				  	$checked = "";
				  }
		$filter_form .=   '
		      		       <option '.$checked.' value="'.$category->name.'">'.$category->name.'</option>';
		      		       $child_cat = $categories=get_categories(
										    array( 'parent' => $category->cat_ID )
										);
		      		    if ($child_cat) {
		      		    	
		      		    	foreach($child_cat as $cc){
		$filter_form.=		'<option '.$checked.' value="'.$cc->name.'">'.$cc->name.'</option>'; 
		      		    	
		      		    	    
		      		    	}
		      		    	    
		      		    	}
		      		    } 
	$filter_form .=' </select>
		  
			
	</div>
	<div style="float:left;width:100%;text-align:center;">
		<input type="submit" style="font-size:18px;border-radius:5px; padding: 0px 50px; " name="search" value="Search">
	</div>
</form></div>';
$filter_form .= "<div style='clear:both'></div>";
$filter_form .= '<script type="text/javascript">
			(function($) {
					$(".fds-select").selectpicker();
			})( jQuery );
</script>';
	$query = new WP_Query($args);






		$posts = $query->posts;
		foreach($posts as $post) {
		  $data .= '<div class="bp-vertical-share" style="width:100%">
								<div class="bp-entry">
								<div class="bp-head">
								<h2><a href="'.get_permalink($post->ID).'" data-wpel-link="internal">'.get_the_title($post->ID).'</a></h2>
								  <div class="mom-post-meta bp-meta">
								  <span>In:'.get_the_category( $post->ID )[0]->name .'</span>';
						$post_tags = get_the_tags($post->ID);
 						$tags =NULL;
						if ( $post_tags ) {
						    foreach( $post_tags as $tag ) {
						    $tags .= $tag->name . ', ';
						    }
						}
						$data .=  '<span> Tags: '.$tags.'</span>
								  </div>
								</div> 
								<div class="bp-details">
								<div class="post-img">
								<a href="'.get_permalink($post->ID).'">';
								if(get_the_post_thumbnail($post->ID)){
									$data .= get_the_post_thumbnail($post->ID);
								}
								else{
									 $data .= '<img width="750" height="560" src="'.fds_set_first_post_image($post).'" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="">';
								}
						 $data .='  </a>
								</div> 
								'.get_the_excerpt( $post->ID ).'
								<a href="'.get_permalink($post->ID).'" class="read-more-link" data-wpel-link="internal">Read more <i class="fa-icon-double-angle-right"></i></a>
								</div> 
								</div> 
								<div class="clear"></div>
								</div>';	
		}

		if (empty($data)) {
			$data ="<h3>No Data Found.</h3>";
		}
		
		wp_reset_postdata();
	
		return $filter_form.$data;
	}
}

add_shortcode('fds-search-result','fds_result_generator');
	 function fds_add_admin_pages() {
			add_options_page('FDS Search Settings', 'FDS Search settings', 'manage_options', 'fds-search-settings', 'fds_admin_index' );
		}
	function fds_admin_index() {
			require plugin_dir_path( __FILE__ ) . 'templates/fds-setting.php';
		}
add_action( 'admin_menu', 'fds_add_admin_pages' );
?>

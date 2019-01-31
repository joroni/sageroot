<?php
// Only declare the function if it doesn't exist (prevents PHP fatal error)
	if (!function_exists('product_post_type'))
	{
		function product_post_type() // Function to register new custom post type
		{
		  // Labels used inside the WordPress CMS
			$labels = array(
				'name' => _x('Products', 'Post Type General Name', 'text_domain'),
				'singular_name' => _x('Product', 'Post Type Singular Name', 'text_domain'),
				'menu_name' => __('Products', 'text_domain'),
				'name_admin_bar' => __('Products', 'text_domain'),
				'archives' => __('Products Catalog', 'text_domain'),
				'parent_item_colon' => __('Parent Product:', 'text_domain'),
				'all_items' => __('All Products', 'text_domain'),
				'add_new_item' => __('Add New Product', 'text_domain'),
				'add_new' => __('Add New', 'text_domain'),
				'new_item' => __('New Product', 'text_domain'),
				'edit_item' => __('Edit Product', 'text_domain'),
				'update_item' => __('Update Product', 'text_domain'),
				'view_item' => __('View Product', 'text_domain'),
				'search_items' => __('Search Products', 'text_domain'),
				'not_found' => __('Not found', 'text_domain'),
				'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
				'featured_image' => __('Featured Image', 'text_domain'),
				'set_featured_image' => __('Set featured image', 'text_domain'),
				'remove_featured_image' => __('Remove featured image', 'text_domain'),
				'use_featured_image' => __('Use as featured image', 'text_domain'),
				'insert_into_item' => __('Insert into product', 'text_domain'),
				'uploaded_to_this_item' => __('Uploaded to this product', 'text_domain'),
				'items_list' => __('Products list', 'text_domain'),
				'items_list_navigation' => __('Products list navigation', 'text_domain'),
				'filter_items_list' => __('Filter products list', 'text_domain'),
				);
				
      // Custom type configuration
			$args = array(
				'label' => __('Product', 'text_domain'),
				'description' => __('Aquation Products', 'text_domain'),
				'labels' => $labels,
				'supports' => array(
					'title',
					'editor',
					'excerpt',
					'author',
					'thumbnail',
					'comments',
					'trackbacks',
					'revisions'),
				'taxonomies' => array(
				  'category', 
				  'post_tag'),
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 5,
				'menu_icon' => 'dashicons-cart',
				'show_in_admin_bar' => true,
				'show_in_nav_menus' => true,
				'can_export' => true,
				'has_archive' => 'products',
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'page',
				);

			register_post_type('product', $args);
		}
		add_action('init', 'product_post_type', 0);
}
?>





<?php
  	// Create posts in WordPress from data in a MySQL database. Full article at: https://imelgrat.me/wordpress/bulk-upload-custom-posts-wordpress/
        //Load WordPress functions and plug-ins. Put correct path for this file. This example assumes you're using it from a sub-folder of  WordPress
    //require_once ('../wp-load.php'); 
    require( dirname(__FILE__) . '/wp-load.php' );
    //echo ( dirname(__FILE__) . '/wp-load.php' );

	$database['hostname'] = 'localhost';
	$database['username'] = 'root';
	$database['password'] = '';
	$database['database'] = 'wpmovies';

	$mysql_link = mysqli_connect($database['hostname'], $database['username'], $database['password']);
	mysqli_select_db($mysql_link, $database['database']);
	mysqli_query($mysql_link, "SET NAMES UTF8");
	mysqli_query($mysql_link, "SET NAMES 'UTF8'");
	mysqli_query($mysql_link, "SET CHARACTER SET UTF8");

/*
--
-- Table structure for table `products`
--
  CREATE TABLE IF NOT EXISTS `products` (
    `product_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `price` float NOT NULL,
    `ingredients` text NOT NULL,
    PRIMARY KEY (`product_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
	$query = "SELECT * FROM `products` WHERE `proccessed`= 0 ORDER BY `products`.`id` ASC;"; // products 
	$result = mysqli_query($mysql_link, $query);

	while ($row = mysqli_fetch_assoc($result))
	{
		// Insert the post and set the category. See https://gist.github.com/imelgrat/46da054bc27d10dbdff5408502623b2d for custom post type declaration
		$post_id = wp_insert_post(array(
			'post_type' => 'product',
			'post_title' => $row['name'],
			'post_content' => $row['description'],
			'post_status' => 'publish', // Can be draft, pending or any other post status
			'comment_status' => 'closed', // if you prefer
			'ping_status' => 'closed', // if you prefer
			));

		if ($post_id)
		{
			// Insert post meta (ACF Custom Fields)
			add_post_meta($post_id, 'price', $row['price']);
			add_post_meta($post_id, 'ingredients', $row['ingredients']);
		}

    echo $row['name'].' posted<br>';
	}
?>
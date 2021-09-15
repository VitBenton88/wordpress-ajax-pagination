<?php

function paginate_posts() {
    $paged = $_GET['paged'];
    $category_id = $_GET['category_id'];
    $search_term = $_GET['search_term'];
    $posts_per_page = 6; // change as needed
	$response = array();

	if ($paged && $posts_per_page && $category_id) {
		// the query
		$args = array(
			'posts_per_page' => $posts_per_page, 
			'paged' => $paged, 
			'category__in' => $category_id,
			'post_status' => 'publish',
			's' => $search_term,
		);
		$query = new WP_Query( $args );
	
		// collect data
		$posts = $query->posts;
		$max_num_pages = $query->max_num_pages;
		
		// manually collect post thumbnails in associative array, key is post's ID
		$thumbnails = array();
		// set a default image url for posts without a featured image
		$default_thumbnail = '';

		if ( $query->have_posts() ) {
			foreach ($posts as $post) {
				// change thumbnail size if needed, source: https://developer.wordpress.org/reference/functions/get_the_post_thumbnail_url/
				$post_thumbnail = get_the_post_thumbnail_url( $post, 'full' ); 
				// add to thumbnail array
				$thumbnails[$post->ID] = $post_thumbnail || $default_thumbnail;
			}
		}
	
		// prep response object
		$response['posts'] = $posts;
		$response['thumbnails'] = $thumbnails;
		$response['max_num_pages'] = max_num_pages;
	}
	
    echo json_encode($response);
    wp_die();
};

add_action( 'wp_ajax_nopriv_paginate_posts', 'paginate_posts' );
add_action( 'wp_ajax_paginate_posts', 'paginate_posts' );
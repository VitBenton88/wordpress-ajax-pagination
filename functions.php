<?php

function paginate_posts() {
    $paged = $_GET['paged'];
    $posts_per_page = $_GET['posts_per_page'];
    $category_id = $_GET['category_id'];
	$thumbnails = array();
	$args = array('posts_per_page'	=> $posts_per_page, 'paged' => $paged ); // by default, query all posts

	if ($category_id !== 'all') { // apply a category filter to query when needed 
		$args['category__in'] = $category_id;
	}

	$query = new WP_Query( $args );
	$posts = $query->posts;
	$max_num_pages = $query->max_num_pages;
	
	// manually collect post thumbnails in associative array, key is post's ID
	if ( $query->have_posts() ) {
		foreach ($posts as $post) {
			$thumbnails[$post->ID] = get_the_post_thumbnail_url( $post, 'full' ); // change thumbnail size if needed
		}
	}

	// prep response object
	$response = array('posts' => $posts, 'thumbnails' => $thumbnails, 'max_num_pages' => $max_num_pages);
	
    echo json_encode($response);
    wp_die();
};

add_action( 'wp_ajax_nopriv_paginate_posts', 'paginate_posts' );
add_action( 'wp_ajax_paginate_posts', 'paginate_posts' );
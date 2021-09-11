<?php

function paginate_posts() {
    $paged = $_GET['paged'];
    $posts_per_page = $_GET['posts_per_page'];
    $category_id = $_GET['category_id'];
	$thumbnails = array();
	$args = array('posts_per_page'	=> $posts_per_page, 'paged' => $paged ); // essentially the 'all' filter

	if ($category_id !== 'all') {
		$args['category__in'] = $category_id;
	}

	$query = new WP_Query( $args );
	$posts = $query->posts;
	$max_num_pages = $query->max_num_pages;
	
	if ( $query->have_posts() ) {
		foreach ($posts as $post) {
			$thumbnails[$post->ID] = get_the_post_thumbnail_url( $post, 'full' );
		}
	}

	$response = array('posts' => $posts, 'thumbnails' => $thumbnails, 'max_num_pages' => $max_num_pages);
	
    echo json_encode($response);
    wp_die();
};

add_action( 'wp_ajax_nopriv_paginate_posts', 'paginate_posts' );
add_action( 'wp_ajax_paginate_posts', 'paginate_posts' );
<?php

/* Template Name: AJAX Pagination Template */ 

$cat_args = array(
    'hide_empty'    => 1,
    'orderby'       => 'name',
    'order'         => 'ASC',
    'parent'        => 0 // Ensures only top-level categories are used
);
$categories = get_categories($cat_args);
?>

<!-- jquery if needed -->
<?php if ( !wp_script_is('jquery') ) : ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php endif; ?>

<!-- Bootstrap Start -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
<!-- Bootstrap End -->

<section class="container mt-5">
    <?php if ( $categories ) : ?>
        <div class="row">
            <div class="col-12 btn-group">
                <button type="button" class="btn btn-outline-primary cat-btn active" data-id="all">All</button>
                <?php foreach($categories as $category): ?>
                    <button type="button" class="btn btn-outline-primary cat-btn" data-id="<?= $category->term_id ?>"><?= $category->name ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger" role="alert">No Categories Found!</div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- The feed -->
    <div id="posts-feed" class="row mt-4 d-flex"></div>

    <div id="spinner" class="d-flex justify-content-center">
        <div id="spinner" class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <nav id="pagination" aria-label="Page navigation" class="mt-4 d-none">
        <ul class="pagination justify-content-center">
        <li class="page-item arrow prev">
            <a class="page-link" href="#">Previous</a>
        </li>

        <!-- Pagination -->
        <li id="page-items" class="d-flex"></li>

        <li class="page-item arrow next">
            <a class="page-link" href="#">Next</a>
        </li>
        </ul>
    </nav>
    
</section>

<script type="text/javascript">
    $(document).ready(function () {
        const $pagination = $('#pagination');
        const $spinner = $('#spinner');
        const excerpt_char_limit = 50;
        const btn_label = 'Read More';
        let category_id = 'all';
        let paged = 1;
        let posts_per_page = 6;

        function queryPosts() {
            // make ajax call to server
            $.ajax({
                type : "GET",
                dataType : "json",
                url : "<?= admin_url('admin-ajax.php'); ?>",
                data : {
                    action: "paginate_posts",
                    paged,
                    posts_per_page,
                    category_id
                },
                beforeSend: function() {
                    // clear feed & pagination
                    $('#posts-feed, #page-items').empty();
                    // show spinner
                    $spinner.removeClass('d-none');
                    // hide pagination
                    $pagination.addClass('d-none');
                    // enable next/prev
                    $('.page-item').removeClass('disabled')
                },
                success: function(response) {
                    console.log('Response: ', response); // see the raw response
                    // destructure response
                    const { max_num_pages, posts, thumbnails } = response;
                    let posts_append_value = '';
                    let page_append_value = '';

                    if (posts) {
                        // collect post data for DOM elements
                        for (let index = 0; index < posts.length; index++) {
                            // destructure post
                            const { guid, ID, post_excerpt, post_title, } = posts[index];
                            const thumbnail_url = thumbnails[ID];

                            posts_append_value +=
                            `<div id="${ID}" class="col-6 col-md-4 mt-3 d-flex">
                                <div class="card w-100">
                                    <img src="${thumbnail_url}" class="card-img-top" alt="${post_title}">
                                    <div class="card-body">
                                        <h5 class="card-title">${post_title}</h5>
                                        <p class="card-text">${post_excerpt ? `${post_excerpt.substring(0, excerpt_char_limit)}&nbsp;...` : ''}</p>
                                        <a href="${guid}" class="btn btn-primary">${btn_label}</a>
                                    </div>
                                </div>
                            </div>`;
                        }

                        // collect pagination DOM elements
                        if (max_num_pages && max_num_pages > 1) {
                            for (let index = 0; index < max_num_pages; index++) {
                                const page_num = index + 1;
                                page_append_value += `<li class="page-item${page_num === paged ? ' active' : ''}"><a class="page-link" href="#">${page_num}</a></li>`
                            }
                            // show pagination
                            $pagination.removeClass('d-none');
                            // disable next/prev if needed
                            if (paged === max_num_pages) {
                                $('.page-item.next').addClass('disabled')
                            } else if (paged === 1) {
                                $('.page-item.prev').addClass('disabled')
                            }
                        }
                    } else {
                        // show alert if no posts
                        posts_append_value = '<div class="alert alert-warning" role="alert">No Posts Found!</div>';
                    }

                    // append to DOM
                    $('#posts-feed').append(posts_append_value);
                    $('#page-items').append(page_append_value);
                },
                complete: function() {
                    // hide spinner
                    $spinner.addClass('d-none');
                }
            });
        }

        // query on load
        queryPosts();

        // category clicks
        $('.cat-btn').click(function() {
            const $this = $(this);
            paged = 1;
            $('.cat-btn.active').removeClass('active');
            $this.addClass('active');
            category_id = $this.data('id');
            queryPosts();
        });

        // pagination number clicks
        $('body').on('click', '.page-item a', function(e) {
            e.preventDefault();
            const $this = $(this);

            if ($this.parent().hasClass('active')) return false;

            paged = parseInt($this.text());
            queryPosts();
        });

        // pagination arrow clicks
        $('.page-item.arrow a').click(function(e) {
            e.preventDefault();

            if ( $(this).parent().hasClass('prev') ) {
                paged--;
            } else {
                paged++;
            }

            queryPosts();
            return false;
        });

    });
</script>
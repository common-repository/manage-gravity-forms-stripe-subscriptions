<?php

add_action( 'wp_ajax_gss_get_pages', 'gss_ajax_fn_get_pages' );
function gss_ajax_fn_get_pages()
{
    $query_args["post_type"] = "page";
    $query_args["posts_per_page"] = -1;
    $query_args = apply_filters( 'gstripe_query_args', $query_args );
    $query = new WP_Query( $query_args );
    $return_array = array();
    
    if ( $query->have_posts() ) {
        $posts = $query->posts;
        foreach ( $posts as $post ) {
            $return_array[] = array(
                "id"    => $post->ID,
                "text"  => $post->post_title,
                "value" => $post->ID,
                "label" => $post->post_title,
            );
        }
    }
    
    return $return_array;
}

//add_action('wp_ajax_gss_cancel_subscription_pro', 'gss_ajax_fn_cancel_subscription_pro');
<?php
/*
Plugin Name: WPS Continue shopping
Plugin URI: https://www.wpstud.io/plugins
Description: Change the cart 'continue shopping' link to a link to the product category instead of the product itself.
Version: 0.1
Author: Frank Schrijvers, WPStudio
Author URI: https://www.wpstud.io
*/

add_filter( 'woocommerce_continue_shopping_redirect', 'wps_change_continue_shopping_link', 20 );
/**
 * Change the 'continue shopping' link. Return, if possible, to the category page.
 *
 * @param [type] $url
 * @return $url
 */
function wps_change_continue_shopping_link( $url ) {

	if ( class_exists( 'WooCommerce' ) ) {

		if ( isset( $_COOKIE['wps_product_category'] ) ) {

			$url = $_COOKIE['wps_product_category'];
			$url = preg_replace( '/\/page\/[0-9]+\/?$/', '/', $url );

		} else {

			preg_match( '/\/product\/(.+)\/$/', $url, $matches );
			$productslug = ( $matches && isset( $matches[1] ) ) ? $matches[1] : false;

			if ( $productslug ) {

				// By default, if a product is found, we return the visitor to the general shop page.
				// We try to find a category to redirect to.
				$url = get_permalink( woocommerce_get_page_id( 'shop' ) );

				$args = array(
					'name'        => $productslug,
					'post_type'   => 'product',
					'post_status' => 'publish',
					'showposts'   => 1,
				);

				$products = get_posts( $args );

				if ( $products ) {

					$terms = get_the_terms( $products[0]->ID, 'product_cat' );

					if ( $terms ) {

						$categoryfound = false;

						foreach ( $terms as $category ) {

							if ( true  === $categoryfound ) continue;

								$categoryurl = get_term_link( intval( $category->term_id ), 'product_cat' );

							if ( $categoryurl && ! is_wp_error( $categoryurl ) ) {

								$categoryfound = true;
								$url = $categoryurl;

							}
						}
					}
				}
			}
		}
	}

	return $url;

}

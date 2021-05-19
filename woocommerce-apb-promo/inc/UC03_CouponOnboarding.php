<?php

use \BouletAP\CouponModel;

class UC03_CouponOnboarding {


	
    function init() {        


		add_action( 'woocommerce_check_cart_items', array($this, 'setup_product_by_coupon'), 10 );

		add_action( 'woocommerce_before_cart', array($this, 'new_coupon_onboarding'), 50 );

    }


	function setup_product_by_coupon() {

		if( !empty($_GET['promo']) ) {
			$coupon_code =  sanitize_text_field( $_GET['promo'] );
			$sub_id = CouponModel::getSubForCoupon($coupon_code);

			if( $sub_id ) {

				// clear cart, add only this product
				WC()->cart->empty_cart();
				WC()->cart->add_to_cart( $sub_id );
			}
		}


	}

	// http://gaboteur.bouletap.ca/panier/?promo=jb07d98gsvus0v
	function new_coupon_onboarding() {

		if( !empty($_GET['promo']) ) {
			$coupon_code =  sanitize_text_field( $_GET['promo'] );


			//$coupon = CouponModel::getCoupon($coupon_code);
			//echo '<pre>'; print_r($coupon); echo '</pre>'; die();


			if ( WC()->cart->has_discount( $coupon_code ) ) return;


			// if the specific product isnt in the cart, we add it.

			WC()->cart->apply_coupon( $coupon_code );
		}
	}

	
}
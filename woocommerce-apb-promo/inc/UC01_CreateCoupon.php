<?php


class UC01_CreateCoupon {


    function init() {        

		// EF01 - Add new admin coupon type
		add_filter( 'woocommerce_coupon_discount_types', array($this, 'add_discount_types') );



		// // Add our recurring product coupon types to the list of coupon types that apply to individual products
		add_filter( 'woocommerce_product_coupon_types', __CLASS__ . '::filter_product_coupon_types', 10, 1 );

		if ( ! is_admin() ) {
			// WC 3.0 only sets a coupon type if it is a pre-defined supported type, so we need to temporarily add our pseudo types. We don't want to add these on admin pages.
			add_filter( 'woocommerce_coupon_discount_types',  __CLASS__ . '::add_pseudo_coupon_types' );
		}
    }


	// EF01 - Add new admin coupon type
	public function add_discount_types( $discount_types ) {

		return array_merge(
			$discount_types,
			array(
				'free_days_by_subs'         => __( 'Free days for subscription', 'woocommerce-apb-promo' ),
			)
		);
	}

	public static function filter_product_coupon_types( $product_coupon_types ) {

		if ( is_array( $product_coupon_types ) ) {
			$product_coupon_types = array_merge( $product_coupon_types, array( 'free_days_by_subs' ) );
		}

		return $product_coupon_types;
	}

	public static function add_pseudo_coupon_types( $coupon_types ) {
		return array_merge(
			$coupon_types,
			array(
				'free_days_by_subs' => __( 'Free days for subs', 'woocommerce-apb-promo' ),
			)
		);
	}
}
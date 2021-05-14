<?php


class UC01_CreateCoupon {


    function init() {        

		add_filter( 'woocommerce_coupon_discount_types', array($this, 'add_discount_types') );

    }

	public static function add_discount_types( $discount_types ) {

		return array_merge(
			$discount_types,
			array(
				'free_days_by_sub'         => __( 'Free days for subscription', 'woocommerce-apb-promo' ),
			)
		);
	}
}
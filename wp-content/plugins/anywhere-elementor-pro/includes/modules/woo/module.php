<?php

namespace Aepro\Modules\Woo;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-woo-add-to-cart',
			'ae-woo-category',
			'ae-woo-description',
            'ae-woo-notices',
            'ae-woo-price',
            'ae-woo-product-image-gallery',
            'ae-woo-products',
            'ae-woo-rating',
            'ae-woo-readmore',
            'ae-woo-sku',
            'ae-woo-stock-status',
            'ae-woo-tabs',
            'ae-woo-tags',
            'ae-woo-title',
		];
	}

}
<?php

namespace Aepro\Modules\AcfGallery;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-acf-gallery',
		];
	}

}
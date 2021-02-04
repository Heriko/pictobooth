<?php

namespace Aepro\Modules\PostImage;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-image',
		];
	}

}
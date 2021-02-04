<?php

namespace Aepro\Modules\PostReadmore;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-readmore',
		];
	}

}
<?php

namespace Aepro\Modules\PostTitle;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-title',
		];
	}

}
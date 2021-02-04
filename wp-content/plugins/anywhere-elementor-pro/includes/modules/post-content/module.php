<?php

namespace Aepro\Modules\PostContent;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-content',
		];
	}

}
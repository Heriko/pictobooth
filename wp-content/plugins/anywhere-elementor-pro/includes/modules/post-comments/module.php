<?php

namespace Aepro\Modules\PostComments;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-comments',
		];
	}

}
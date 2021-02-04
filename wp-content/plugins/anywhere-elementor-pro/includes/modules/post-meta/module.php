<?php

namespace Aepro\Modules\PostMeta;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-post-meta',
		];
	}

}
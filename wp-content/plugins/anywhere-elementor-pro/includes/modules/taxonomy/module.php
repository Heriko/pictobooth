<?php

namespace Aepro\Modules\Taxonomy;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-taxonomy',
		];
	}

}
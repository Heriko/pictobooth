<?php

namespace Aepro\Modules\Breadcrumb;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-breadcrumb',
		];
	}

}
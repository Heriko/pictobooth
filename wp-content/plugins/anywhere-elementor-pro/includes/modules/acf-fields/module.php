<?php

namespace Aepro\Modules\AcfFields;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-acf',
		];
	}

}
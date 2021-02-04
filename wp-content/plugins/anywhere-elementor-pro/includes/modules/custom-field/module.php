<?php

namespace Aepro\Modules\CustomField;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-custom-field',
		];
	}

}
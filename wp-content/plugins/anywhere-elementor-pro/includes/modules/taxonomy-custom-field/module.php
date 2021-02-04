<?php

namespace Aepro\Modules\TaxCustomField;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-tax-custom-Field',
		];
	}

}
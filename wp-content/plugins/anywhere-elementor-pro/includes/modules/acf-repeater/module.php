<?php

namespace Aepro\Modules\AcfRepeater;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-acf-repeater',
		];
	}

}
<?php

namespace Aepro\Modules\TaxonomyBlocks;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-taxonomy-blocks',
		];
	}

}
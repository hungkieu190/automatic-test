<?php
namespace PhysCode\Customizer\Control;

use PhysCode\Customizer\Modules\Base;

defined( 'ABSPATH' ) || exit;

class Custom extends Base {

	public $type = 'phys-custom';

	protected function content_template() {
		?>
		<label>
			<# if ( data.label ) { #><span class="customize-control-title">{{{ data.label }}}</span><# } #>
			<# if ( data.description ) { #><span class="description customize-control-description">{{{ data.description }}}</span><# } #>
			{{{ data.value }}}
		</label>
		<?php
	}
}

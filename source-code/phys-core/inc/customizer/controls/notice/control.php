<?php
namespace PhysCode\Customizer\Control;

use PhysCode\Customizer\Modules\Base;

defined( 'ABSPATH' ) || exit;

class Notice extends Base {

	public $type = 'phys-notice';

	protected function content_template() {
		?>
		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>
		<?php
	}
}

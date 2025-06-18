<?php
/**
 * Tax Meta Class
 *
 * The Tax Meta Class is used by including it in your plugin r theme files and using its methods to
 * Add meta fields for WordPress Taxonomies (categories,tags and custom taxonomies). It is meant to be very simple and
 * straightforward.
 *
 * This class is derived from My-Meta-Box (https://github.com/bainternet/My-Meta-Box script) which is
 * a class for creating custom meta boxes for WordPress.
 *
 * @version    2.1.0
 * @copyright  2012 Ohad Raz
 * @author     Ohad Raz (email: admin@bainternet.info)
 * @link       http://en.bainternet.info
 *
 * @license    GNU General Public LIcense v3.0 - license.txt
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    Tax Meta Class
 * @deprecated replace_insert_to_post_text() @since 1.8.3
 *
 */

if ( ! class_exists( 'Tax_Meta_Class' ) ) :

	/**
	 * All Types Meta Box class.
	 *
	 * @package All Types Meta Box
	 * @since   1.0
	 *
	 */
	class Tax_Meta_Class {

		/**
		 * Holds meta box object
		 *
		 * @var object
		 * @access protected
		 */
		protected $_meta_box;

		/**
		 * Holds meta box fields.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_prefix;

		/**
		 * Holds Prefix for meta box fields.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_fields;

		/**
		 * Use local images.
		 *
		 * @var bool
		 * @access protected
		 */
		protected $_Local_images;

		/**
		 * What form is this? edit or new term.
		 *
		 * @var string
		 * @access protected
		 * $since 1.0
		 */
		protected $_form_type;
		/**
		 * SelfPath to allow themes as well as plugins.
		 *
		 * @var string
		 * @access protected
		 * $since 1.0
		 */
		protected $SelfPath;

		/**
		 * Constructor
		 *
		 * @param array $meta_box
		 *
		 * @since  1.0
		 * @access public
		 *
		 */
		public function __construct( $meta_box ) {

			// If we are not in admin area exit.
			if ( ! is_admin() ) {
				return;
			}

			// Assign meta box values to local variables and add it's missed values.
			$this->_meta_box     = $meta_box;
			$this->_prefix       = ( isset( $meta_box['prefix'] ) ) ? $meta_box['prefix'] : '';
			$this->_fields       = $this->_meta_box['fields'];
			$this->_Local_images = ( isset( $meta_box['local_images'] ) ) ? true : false;
			$this->add_missed_values();

			// Add Actions
			add_action( 'admin_init', array( $this, 'add' ) );

			// Load common js, css files
			// Must enqueue for all pages as we need js for the media upload, too.
			add_action( 'admin_print_styles', array( $this, 'load_scripts_styles' ) );

			//delete term meta on term deletion
			add_action( 'delete_term', array( $this, 'delete_taxonomy_metadata' ), 10, 2 );
		}

		/**
		 * Load all Javascript and CSS
		 *
		 * @since  1.0
		 * @access public
		 */
		public function load_scripts_styles() {

			//only load styles and js when needed
			/*
			 * since 1.0
			 */
			$taxnow = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
			if ( in_array( $taxnow, $this->_meta_box['pages'] ) ) {
				// Check for special fields and add needed actions for them.
				$this->check_field_color();
				$this->check_field_date();
				// Enqueue Meta Box Style
				wp_enqueue_style( 'tax-meta-class', PHYS_CORE_ADMIN_URI . '/assets/css/tax-meta-class.css' );
				// Enqueue Meta Box Scripts
				wp_enqueue_script( 'tax-meta-class', PHYS_CORE_ADMIN_URI . '/assets/js/tax-meta-class.js', array( 'jquery' ), null, true );
				add_thickbox();
			}

		}


		/**
		 * Check Field Color
		 *
		 * @since  1.0
		 * @access public
		 */
		public function check_field_color() {
			if ( $this->has_field( 'color' ) || $this->is_edit_page() ) {
				// Enqueu built-in script and style for color picker.
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
			}
		}

		/**
		 * Check Field Date
		 *
		 * @since  1.0
		 * @access public
		 */
		public function check_field_date() {
			if ( $this->has_field( 'date' ) && $this->is_edit_page() ) {
				// Enqueu JQuery UI, use proper version.
				$this->enqueue_jqueryui();
			}
		}

		/**
		 * Add Meta Box for multiple post types.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function add() {
			// Loop through array
			foreach ( $this->_meta_box['pages'] as $page ) {
				//add fields to edit form
				add_action( $page . '_edit_form_fields', array( $this, 'show_edit_form' ) );
				//add fields to add new form
				add_action( $page . '_add_form_fields', array( $this, 'show_new_form' ) );
				// this saves the edit fields
				add_action( 'edited_' . $page, array( $this, 'save' ), 10, 2 );
				// this saves the add fields
				add_action( 'created_' . $page, array( $this, 'save' ), 10, 2 );
			}
			// Delete all attachments when delete custom post type.
			add_action( 'wp_ajax_at_delete_file', array( $this, 'delete_file' ) );
			add_action( 'wp_ajax_at_reorder_images', array( $this, 'reorder_images' ) );
			// Delete file via Ajax
			add_action( 'wp_ajax_at_delete_mupload', array( $this, 'wp_ajax_delete_image' ) );

		}

		/**
		 * Callback function to show fields on add new taxonomy term form.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_new_form( $term_id ) {
			$this->_form_type = 'new';
			//add_action( 'admin_footer', array( $this, 'footer_js' ) );
			$this->show( $term_id );
		}

		/**
		 * Callback function to show fields on term edit form.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_edit_form( $term_id ) {
			$this->_form_type = 'edit';
			$this->show( $term_id );
		}


		/**
		 * Callback function to show fields in meta box.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show( $term_id ) {
			wp_nonce_field( basename( __FILE__ ), 'tax_meta_class_nonce' );

			wp_nonce_field( basename( __FILE__ ), 'tax_meta_class_nonce' );

			foreach ( $this->_fields as $field ) {
				$multiple = isset( $field['multiple'] ) ? $field['multiple'] : false;
				$class    = isset( $field['class'] ) ? ' ' . $field['class'] : '';
				$meta     = $this->get_tax_meta( $term_id, $field['id'], !$multiple );
				$meta     = ( $meta !== '' ) ? $meta : ( isset( $field['std'] ) ? $field['std'] : '' );
				if ( 'image' != $field['type'] && $field['type'] != 'repeater' ) {
					$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );
				}

				echo '<tr class="form-field' . $class . '">';
				// Call Separated methods for displaying each type of field.
				call_user_func( array(
					$this,
					'show_field_' . $field['type']
				), $field, is_array( $meta ) ? $meta : stripslashes( $meta ) );
				echo '</tr>';
			}
			echo '</table>';
		}

		/**
		 * Begin Field.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_begin( $field, $meta ) {
			if ( isset( $field['group'] ) ) {
				if ( $field['group'] == "start" ) {
					echo "<td class='at-field'>";
				}
			} else {
				if ( $this->_form_type == 'edit' ) {
					echo '<th valign="top" scope="row">';
				} else {
					$class = isset( $field['class'] ) ? ' ' . $field['class'] : '';
					echo '<td><div class="form-field' . $class . '">';
				}
			}
			if ( $field['name'] != '' || $field['name'] != false ) {
				echo "<label for='{$field['id']}'>{$field['name']}</label>";
			}
			if ( $this->_form_type == 'edit' ) {
				echo '</th><td>';
			}
		}

		/**
		 * End Field.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_end( $field, $meta = null, $group = false ) {
			if ( isset( $field['group'] ) ) {
				if ( $group == 'end' ) {
					if ( isset( $field['desc'] ) && $field['desc'] != '' ) {
						echo "<p class='description'>{$field['desc']}</p></td>";
					} else {
						echo "</td>";
					}
				} else {
					if ( isset( $field['desc'] ) && $field['desc'] != '' ) {
						echo "<p class='description'>{$field['desc']}</p><br/>";
					} else {
						echo '<br/>';
					}
				}
			} else {
				if ( isset( $field['desc'] ) && $field['desc'] != '' ) {
					echo "<p class='description'>{$field['desc']}</p>";
				}
				if ( $this->_form_type == 'edit' ) {
					echo '</td>';
				} else {
					echo '</td></div>';
				}
			}
		}

		/**
		 * Show Field Text.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_text( $field, $meta ) {
			$this->show_field_begin( $field, $meta );
			echo "<input type='text' class='at-text' name='{$field['id']}' id='{$field['id']}' value='{$meta}' style='{$field['style']}' size='30' />";
			$this->show_field_end( $field, $meta );
		}

		/**
		 * Show Field Text.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_icon($field, $meta) {
			$this->show_field_begin($field, $meta);

			$icons     = apply_filters(
				'phys_core_data_field_icon_tax_meta',
				array(
					'none',
					'flaticon-airplane-1',
					'flaticon-airplane-2',
					'flaticon-airplane-3',
					'flaticon-airplane-4',
					'flaticon-balloon',
					'flaticon-business',
					'flaticon-cart',
					'flaticon-cart-1',
					'flaticon-globe',
					'flaticon-hotel',
					'flaticon-island',
					'flaticon-map',
					'flaticon-map-1',
					'flaticon-money',
					'flaticon-people',
					'flaticon-plans',
					'flaticon-road',
					'flaticon-road-1',
					'flaticon-road-2',
					'flaticon-sand',
					'flaticon-ship',
					'flaticon-shipping',
					'flaticon-skyscraper',
					'flaticon-suitcase',
					'flaticon-ticket',
					'flaticon-transport',
					'flaticon-transport-1',
					'flaticon-transport-2',
					'flaticon-transport-3',
					'flaticon-transport-4',
					'flaticon-transport-5',
					'flaticon-transport-6',
					'flaticon-travel',
					'flaticon-travel-1',
					'flaticon-travel-2',
					'flaticon-travelling',
					'flaticon-yacht'
				)
			);
			$icons_str = implode(' ', $icons);
			echo "<input type='hidden' class='at-text' name='{$field['id']}' id='edit-{$field['id']}' value='{$meta}' style='{$field['style']}' size='30' />";
?>

			<input alt="#TB_inline?height=400&width=500&inlineId=iconbox-popup" title="<?php esc_attr_e('Click to browse icon', 'travelwp') ?>" class="thickbox button-secondary submit-add-to-menu" type="button" value="<?php esc_attr_e('Browse Icon', 'travelwp') ?>" onclick="on_open_icon_browser('edit-<?php echo esc_attr($field['id']); ?>');" />
			<span class="icon-preview"><i class="<?php echo esc_html($meta); ?>"></i></span>
			<script type="text/javascript">
				function on_open_icon_browser(target_id) {
					jQuery('#iconbox_target_element').val(target_id);
				}

				function onSearchIconBoxKeyUp(filter) {
					var icons_str = "<?php echo ent2ncr($icons_str); ?>";
					if (!filter) {
						jQuery("#icon-dropdown .icon-list li").show();
					}
					var regex_str = "[^\\s]?" + filter + "[^\\s]*";
					var re = new RegExp(regex_str, "gi");
					var match = icons_str.match(re);
					if (filter.length) {
						jQuery("#icon-dropdown .icon-list li").hide();
					}
					if (match && match.length) {
						jQuery.each(match, function (index, value) {
							jQuery("#icon-dropdown .icon-list li#icon-list-" + value).show();
						});
					}
					return;
				}
				jQuery(document).ready(function () {
					jQuery("#icon-dropdown li").click(function () {
						jQuery(this).attr("class", "selected").siblings().removeAttr("class");
						var icon = jQuery(this).attr("data-icon");
						var target_id = jQuery('#iconbox_target_element').val();
						var match = target_id.match(/\d+/gi);
						jQuery("#" + target_id).val(icon);
						jQuery(".icon-preview").html("<i class=\'icon " + icon + "\'></i>");
					});
				});
			</script>
			<div id="iconbox-popup" style="display:none;">
				<?php
				$html = '<div id="iconbox-search-bar">';
				$html .= '<input type="hidden" name="" class="wpb_vc_param_value" value="" id="trace"/> ';
				$html .= '<input id="icon_box_search_box" onkeyup="onSearchIconBoxKeyUp(this.value)" class="search icon-search" type="text" placeholder="Search" /><div class="icon-preview"><i class=""></i></div>';
				$html .= '<input id="iconbox_target_element" type="hidden" />';
				$html .= '</div>';

				$html .= '<div id="icon-dropdown" >';
				$html .= '<ul class="icon-list">';
				$n = 1;
				foreach ( $icons as $icon ) {
					$html .= '<li id="icon-list-' . $icon . '" data-icon="' . $icon . '"><i class="icon ' . $icon . '"></i></li>';
					$n ++;
				}
				$html .= '</ul>';
				$html .= '</div>';
				echo ent2ncr( $html );
				?>
			</div>
			<?php
			$this->show_field_end( $field, $meta );
		}
		/**
		 * Show Field Textarea.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_textarea($field, $meta) {
			$this->show_field_begin($field, $meta);
			echo "<textarea class='at-textarea large-text' style='{$field['style']}' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>{$meta}</textarea>";
			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Field Select.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_select($field, $meta) {

			if (!is_array($meta)) {
				$meta = (array) $meta;
			}
			$this->show_field_begin($field, $meta);
			echo "<select class='at-select' style='{$field['style']}' name='{$field['id']}" . ($field['multiple'] ? "[]' id='{$field['id']}' multiple='multiple'" : "'") . ">";
			foreach ($field['options'] as $key => $value) {
				echo "<option value='{$key}'" . selected(in_array($key, $meta), true, false) . ">{$value}</option>";
			}
			echo "</select>";
			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Radio Field.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_radio($field, $meta) {

			if (!is_array($meta)) {
				$meta = (array) $meta;
			}

			$this->show_field_begin($field, $meta);
			foreach ($field['options'] as $key => $value) {
				echo "<input style='{$field['style']}' type='radio' class='at-radio' name='{$field['id']}' value='{$key}'" . checked(in_array($key, $meta), true, false) . " /> <span class='at-radio-label'>{$value}</span>";
			}
			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Checkbox Field.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_checkbox($field, $meta) {

			$this->show_field_begin($field, $meta);
			echo "<input type='checkbox' style='{$field['style']}' class='rw-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked(!empty($meta), true, false) . " />";
			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Image Field.
		 *
		 * @param array $field
		 * @param array $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_image($field, $meta) {
			$this->show_field_begin($field, $meta);
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_media();
			$std   = isset($field['std']) ? $field['std'] : array('id' => '', 'url' => '');
			$name  = esc_attr($field['id']);
			$value = isset($meta['id']) ? $meta : $std;
			if ($value == '') {
				$value = array();
			}
			if (!isset($value['url'])) {
				$value['url'] = '';
			}
			if (!isset($value['id'])) {
				$value['id'] = '';
			}
			$value['url'] = isset($value['src']) ? $value['src'] : $value['url'];
			$has_image    = empty($value['url']) ? false : true;
			$w            = isset($field['width']) ? $field['width'] : 'auto';
			$h            = isset($field['height']) ? $field['height'] : 'auto';
			$PreviewStyle = "style='width: $w; height: $h;" . ((!$has_image) ? "display: none;'" : "'");
			$id           = $field['id'];
			$multiple     = isset($field['multiple']) ? $field['multiple'] : false;
			$multiple     = ($multiple) ? "multiFile " : "";
			echo '<div class="wrapper-simplePanelImagePreview">';
			echo "<span class='simplePanelImagePreview'><img {$PreviewStyle} src='{$value['url']}'><br/></span>";
			echo "<input type='hidden' name='{$name}[id]' value='{$value['id']}'/>";
			echo "<input type='hidden' name='{$name}[url]' value='{$value['url']}'/>";
			if ($has_image) {
				echo "<input class='{$multiple} button  simplePanelimageUploadclear' id='{$id}' value='" . esc_html__('Remove Image', 'travelwp') . "' type='button'/>";
			} else {
				echo "<input class='{$multiple} button simplePanelimageUpload' id='{$id}' value='" . esc_html__('Upload Image', 'travelwp') . "' type='button'/>";
			}
			echo '</div>';
			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Color Field.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_color($field, $meta) {

			if (empty($meta)) {
				$meta = '#';
			}

			$this->show_field_begin($field, $meta);
			echo '<input class="at-color" type="text" name="' . $field['id'] . '" value="' . esc_attr($meta) . '"/>';
			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Checkbox List Field
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_checkbox_list($field, $meta) {

			if (!is_array($meta)) {
				$meta = (array) $meta;
			}

			$this->show_field_begin($field, $meta);

			$html = array();

			foreach ($field['options'] as $key => $value) {
				$html[] = "<input style='{$field['style']}' type='checkbox' class='at-checkbox_list' name='{$field['id']}[]' value='{$key}'" . checked(in_array($key, $meta), true, false) . " /> {$value}";
			}

			echo implode('<br />', $html);

			$this->show_field_end($field, $meta);
		}

		/**
		 * Show Date Field.
		 *
		 * @param string $field
		 * @param string $meta
		 *
		 * @since  1.0
		 * @access public
		 */
		public function show_field_date($field, $meta) {
			$this->show_field_begin($field, $meta);
			echo "<input style='{$field['style']}' type='text' class='at-date' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='{$meta}' size='30' />";
			$this->show_field_end($field, $meta);
		}

		/**
		 * Save Data from Metabox
		 *
		 * @param string $term_id
		 *
		 * @since  1.0
		 * @access public
		 */

		public function save($term_id) {

			// check if the we are coming from quick edit issue #38 props to Nicola Peluchetti.
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save-tax') {
				return $term_id;
			}

			if (
				!isset($term_id) // Check Revision
				|| (!isset($_POST['taxonomy'])) // Check if current taxonomy type is set.
				|| (!in_array($_POST['taxonomy'], $this->_meta_box['pages'])) // Check if current taxonomy type is supported.
				|| (!check_admin_referer(basename(__FILE__), 'tax_meta_class_nonce')) // Check nonce - Security
				|| (!current_user_can('manage_categories'))
			) // Check permission
			{
				return $term_id;
			}

			foreach ($this->_fields as $field) {

				$name = $field['id'];
				$type = $field['type'];
				$old  = $this->get_tax_meta($term_id, $name, !$field['multiple']);
				$new  = (isset($_POST[$name])) ? $_POST[$name] : (($field['multiple']) ? array() : '');


				// Validate meta value
				if (class_exists('Tax_Meta_Validate') && method_exists('Tax_Meta_Validate', $field['validate_func'])) {
					$new = call_user_func(array('Tax_Meta_Validate', $field['validate_func']), $new);
				}

				//skip on Paragraph field
				if ($type != "paragraph") {
					// Call defined method to save meta value, if there's no methods, call common one.
					$save_func = 'save_field_' . $type;
					if (method_exists($this, $save_func)) {
						call_user_func(array($this, 'save_field_' . $type), $term_id, $field, $old, $new);
					} else {
						$this->save_field($term_id, $field, $old, $new);
					}
				}
			} // End foreach
		}

		/**
		 * Common function for saving fields.
		 *
		 * @param string       $term_id
		 * @param string       $field
		 * @param string       $old
		 * @param string|mixed $new
		 *
		 * @since  1.0
		 * @access public
		 */
		public function save_field($term_id, $field, $old, $new) {
			$name = $field['id'];
			$this->delete_tax_meta($term_id, $name);
			if ($new === '' || $new === array()) {
				return;
			}

			$this->update_tax_meta($term_id, $name, $new);
		}

		/**
		 * function for saving image field.
		 *
		 * @param string       $term_id
		 * @param string       $field
		 * @param string       $old
		 * @param string|mixed $new
		 *
		 * @since  1.0
		 * @access public
		 */
		public function save_field_image($term_id, $field, $old, $new) {
			$name = $field['id'];
			$this->delete_tax_meta($term_id, $name);
			if ($new === '' || $new === array() || $new['id'] == '' || $new['url'] == '') {
				return;
			}

			$this->update_tax_meta($term_id, $name, $new);
		}


		/**
		 * Add missed values for meta box.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function add_missed_values() {

			// Default values for meta box
			$this->_meta_box = array_merge(array(
				'context'  => 'normal',
				'priority' => 'high',
				'pages'    => array('post')
			), (array) $this->_meta_box);

			// Default values for fields
			foreach ((array) $this->_fields as $field) {

				$multiple = in_array($field['type'], array('image'));
				$std      = $multiple ? array() : '';
				$format   = 'date' == $field['type'] ? 'yy-mm-dd' : ('time' == $field['type'] ? 'hh:mm' : '');
				$field    = array_merge(array(
					'multiple'      => $multiple,
					'std'           => $std,
					'desc'          => '',
					'format'        => $format,
					'validate_func' => ''
				), $field);
			} // End foreach

		}

		/**
		 * Check if field with $type exists.
		 *
		 * @param string $type
		 *
		 * @since  1.0
		 * @access public
		 */
		public function has_field($type) {
			foreach ($this->_fields as $field) {
				if ($type == $field['type']) {
					return true;
				} elseif ('repeater' == $field['type']) {
					foreach ((array) $field["fields"] as $repeater_field) {
						if ($type == $repeater_field["type"]) {
							return true;
						}
					}
				}
			}

			return false;
		}

		/**
		 * Check if current page is edit page.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function is_edit_page() {
			global $pagenow;

			return ($pagenow == 'edit-tags.php');
		}

		/**
		 * Fixes the odd indexing of multiple file uploads.
		 *
		 * Goes from the format:
		 * $_FILES['field']['key']['index']
		 * to
		 * The More standard and appropriate:
		 * $_FILES['field']['index']['key']
		 *
		 * @param string $files
		 *
		 * @since  1.0
		 * @access public
		 */
		public function fix_file_array(&$files) {

			$output = array();

			foreach ($files as $key => $list) {
				foreach ($list as $index => $value) {
					$output[$index][$key] = $value;
				}
			}

			return $files = $output;
		}

		/**
		 * Get proper JQuery UI version.
		 *
		 * Used in order to not conflict with WP Admin Scripts.
		 *
		 * @since  1.0
		 * @access public
		 */
		public function get_jqueryui_ver() {

			global $wp_version;

			if (version_compare($wp_version, '4.0', '>=')) {
				return '1.11.2';
			}
			if (version_compare($wp_version, '3.9', '>=')) {
				return '1.10.4';
			}
			if (version_compare($wp_version, '3.8', '>=')) {
				return '1.10.3';
			}
			if (version_compare($wp_version, '3.5', '>=')) {
				return '1.9.2';
			}
			if (version_compare($wp_version, '3.1', '>=')) {
				return '1.8.10';
			}

			return '1.7.3';
		}

		/**
		 * Enqueue JQuery UI version.
		 *
		 * @since  2.0.2
		 * @access public
		 */
		public function enqueue_jqueryui() {
			wp_enqueue_style('tmc-jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $this->get_jqueryui_ver() . '/themes/' . apply_filters('tmc_jquery_ui_theme', 'smoothness') . '/jquery-ui.css');
			wp_enqueue_script('tmc-jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/' . $this->get_jqueryui_ver() . '/jquery-ui.min.js', array('jquery'));
		}

		/**
		 *  Add Text Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'style' =>   // custom style for field, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addText($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'text',
				'id'       => $id,
				'std'      => '',
				'desc'     => '',
				'class'    => '',
				'style'    => '',
				'name'     => 'Text Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}

		//add icon
		public function addIcon($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'icon',
				'id'       => $id,
				'std'      => '',
				'desc'     => '',
				'class'    => '',
				'style'    => '',
				'name'     => 'Text Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}

			return $new_field;
		}

		/**
		 *  Add Hidden Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'style' =>   // custom style for field, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  0.1.3
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addHidden($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'hidden',
				'id'       => $id,
				'std'      => '',
				'desc'     => '',
				'style'    => '',
				'name'     => 'Text Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}


		/**
		 *  Add Checkbox Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addCheckbox($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'checkbox',
				'id'       => $id,
				'std'      => '',
				'desc'     => '',
				'class'    => '',
				'style'    => '',
				'name'     => 'Checkbox Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}


		/**
		 *  Add Textarea Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'style' =>   // custom style for field, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addTextarea($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'textarea',
				'id'       => $id,
				'std'      => '',
				'desc'     => '',
				'class'    => '',
				'style'    => '',
				'name'     => 'Textarea Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}

		/**
		 *  Add Select Field to meta box
		 *
		 * @param $id       string field id, i.e. the meta key
		 * @param $options  (array)  array of key => value pairs for select options
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, (array) optional
		 *                  'multiple' => // select multiple values, optional. Default is false.
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @author Ohad Raz
		 * @since  1.0
		 * @access public
		 *
		 */
		public function addSelect($id, $options, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'select',
				'id'       => $id,
				'std'      => array(),
				'desc'     => '',
				'style'    => '',
				'class'    => '',
				'name'     => 'Select Field',
				'multiple' => false,
				'options'  => $options
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}


		/**
		 *  Add Radio Field to meta box
		 *
		 * @param $id       string field id, i.e. the meta key
		 * @param $options  (array)  array of key => value pairs for radio options
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @author Ohad Raz
		 * @since  1.0
		 * @access public
		 *
		 */
		public function addRadio($id, $options, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'radio',
				'id'       => $id,
				'std'      => array(),
				'desc'     => '',
				'style'    => '',
				'name'     => 'Radio Field',
				'options'  => $options,
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}

		/**
		 *  Add Date Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'validate_func' => // validate function, string optional
		 *                  'format' => // date format, default yy-mm-dd. Optional. Default "'d MM, yy'"  See more formats here: http://goo.gl/Wcwxn
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addDate($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'date',
				'id'       => $id,
				'style'    => '',
				'std'      => '',
				'desc'     => '',
				'format'   => 'd MM, yy',
				'name'     => 'Date Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}


		/**
		 *  Add Color Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'std' => // default value, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addColor($id, $args, $repeater = false) {
			$new_field = array(
				'type'     => 'color',
				'id'       => $id,
				'std'      => '',
				'style'    => '',
				'desc'     => '',
				'class'    => '',
				'name'     => 'ColorPicker Field',
				'multiple' => false
			);
			$new_field = array_merge($new_field, $args);
			if (false === $repeater) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}

		/**
		 *  Add Image Field to meta box
		 *
		 * @param $id       string  field id, i.e. the meta key
		 * @param $args     mixed|array
		 *                  'name' => // field name/label string optional
		 *                  'desc' => // field description, string optional
		 *                  'validate_func' => // validate function, string optional
		 * @param $repeater bool  is this a field inside a repeatr? true|false(default)
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function addImage( $id, $args, $repeater = false ) {
			$new_field = array(
				'type'     => 'image',
				'id'       => $id,
				'desc'     => '',
				'style'    => '',
				'name'     => 'Image Field',
				'std'      => '',
				'class'    => '',
				'multiple' => false
			);
			$new_field = array_merge( $new_field, $args );
			if ( false === $repeater ) {
				$this->_fields[] = $new_field;
			} else {
				return $new_field;
			}
		}


		/**
		 * Finish Declaration of Meta Box
		 * @author Ohad Raz
		 * @since  1.0
		 * @access public
		 */
		public function Finish() {
			$this->add_missed_values();
		}

		/**
		 * Helper function to check for empty arrays
		 *
		 * @param $args mixed|array
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @author Ohad Raz
		 */
		public function is_array_empty( $array ) {
			if ( ! is_array( $array ) ) {
				return true;
			}

			foreach ( $array as $a ) {
				if ( is_array( $a ) ) {
					foreach ( $a as $sub_a ) {
						if ( ! empty( $sub_a ) && $sub_a != '' ) {
							return false;
						}
					}
				} else {
					if ( ! empty( $a ) && $a != '' ) {
						return false;
					}
				}
			}

			return true;
		}


		//get term meta field
		public function get_tax_meta( $term_id, $key, $multi = false ) {
			$t_id = ( is_object( $term_id ) ) ? $term_id->term_id : $term_id;
			$m    = get_option( 'tax_meta_' . $t_id );
			if ( isset( $m[$key] ) ) {
				return $m[$key];
			} else {
				return '';
			}
		}

		//delete meta
		public function delete_tax_meta( $term_id, $key ) {
			$m = get_option( 'tax_meta_' . $term_id );
			if ( isset( $m[$key] ) ) {
				unset( $m[$key] );
			}
			update_option( 'tax_meta_' . $term_id, $m );
		}

		//update meta
		public function update_tax_meta( $term_id, $key, $value ) {
			$m       = get_option( 'tax_meta_' . $term_id );
			$m[$key] = $value;
			update_option( 'tax_meta_' . $term_id, $m );
		}


		/**
		 * deletetaxonomy_metadata
		 *
		 * delete meta on term deletion
		 *
		 *  answers issue #16
		 * @return Void
		 * @since  1.8.1
		 * @access public
		 * @author Ohad Raz
		 */
		public function delete_taxonomy_metadata( $term, $term_id ) {
			delete_option( 'tax_meta_' . $term_id );
		}

	} // End Class

endif; // End Check Class Exists

/*
 * meta functions for easy access:
 */


if ( ! function_exists( 'get_tax_meta' ) ) {
	function get_tax_meta( $term_id, $key, $multi = false ) {
		$t_id = ( is_object( $term_id ) ) ? $term_id->term_id : $term_id;
		$m    = get_option( 'tax_meta_' . $t_id );
		if ( isset( $m[$key] ) ) {
			return $m[$key];
		} else {
			return '';
		}
	}
}
//delete meta
if ( ! function_exists( 'delete_tax_meta' ) ) {
	function delete_tax_meta( $term_id, $key ) {
		$m = get_option( 'tax_meta_' . $term_id );
		if ( isset( $m[$key] ) ) {
			unset( $m[$key] );
		}
		update_option( 'tax_meta_' . $term_id, $m );
	}
}

//update meta
if ( ! function_exists( 'update_tax_meta' ) ) {
	function update_tax_meta( $term_id, $key, $value ) {
		$m       = get_option( 'tax_meta_' . $term_id );
		$m[$key] = $value;
		update_option( 'tax_meta_' . $term_id, $m );
	}
}

//get term meta field and strip slashes
if ( ! function_exists( 'get_tax_meta_strip' ) ) {
	function get_tax_meta_strip( $term_id, $key, $multi = false ) {
		$t_id = ( is_object( $term_id ) ) ? $term_id->term_id : $term_id;
		$m    = get_option( 'tax_meta_' . $t_id );
		if ( isset( $m[$key] ) ) {
			return is_array( $m[$key] ) ? $m[$key] : stripslashes( $m[$key] );
		} else {
			return '';
		}
	}
}

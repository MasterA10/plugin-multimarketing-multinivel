<?php
/**
 * Handles Drag & Drop reordering for Custom Post Types in the Admin.
 */
class Expressive_Admin_Sortable {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_lms_update_post_order', array( $this, 'ajax_update_post_order' ) );
		add_action( 'pre_get_posts', array( $this, 'set_admin_order' ) );
	}

	/**
	 * Force alphabetical or manual order in admin list.
	 */
	public function set_admin_order( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->get( 'post_type' ) === 'academy_member' ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}
	}

	/**
	 * Enqueue Sortable scripts on the specific post type list.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'edit.php' ) {
			return;
		}

		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		
		if ( $post_type !== 'academy_member' ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-sortable' );
		add_action( 'admin_footer', array( $this, 'render_js' ) );
	}

	/**
	 * Render the Sortable JS logic in the footer.
	 */
	public function render_js() {
		?>
		<script>
		jQuery(document).ready(function($) {
			var $postList = $('table.wp-list-table tbody');
			
			$postList.sortable({
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				placeholder: 'sortable-placeholder',
				start: function(e, ui) {
					ui.placeholder.height(ui.item.height());
				},
				update: function(event, ui) {
					var postOrder = $(this).sortable('toArray', { attribute: 'id' });
					var ids = postOrder.map(function(id) {
						return id.replace('post-', '');
					});

					// Visual feedback
					$postList.css('opacity', '0.5');

					$.post(ajaxurl, {
						action: 'lms_update_post_order',
						order: ids,
						nonce: '<?php echo wp_create_nonce("lms_sortable_nonce"); ?>'
					}, function(response) {
						$postList.css('opacity', '1');
						if (response.success) {
							// Notification could be added here
							console.log('Ordem da equipe atualizada com sucesso.');
						} else {
							alert('Erro ao salvar nova ordem: ' + (response.data || 'Erro desconhecido'));
							location.reload();
						}
					});
				}
			});

			// Add styling and move cursor to handles or rows
			$postList.find('tr').css('cursor', 'move');
		});
		</script>
		<style>
			.sortable-placeholder { background: rgba(212, 175, 55, 0.1) !important; border: 2px dashed #D4AF37 !important; visibility: visible !important; }
			.ui-sortable-helper { background: #fff !important; box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; display: table !important; width: 100% !important; }
			.ui-sortable-helper td { border-bottom: 1px solid #eee !important; }
		</style>
		<?php
	}

	/**
	 * AJAX Handler to save the new order.
	 */
	public function ajax_update_post_order() {
		check_ajax_referer( 'lms_sortable_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permissão negada.' );
		}

		$order = isset( $_POST['order'] ) ? array_map( 'intval', (array) $_POST['order'] ) : array();
		
		if ( empty( $order ) ) {
			wp_send_json_error( 'Nenhum dado recebido.' );
		}

		foreach ( $order as $index => $post_id ) {
			wp_update_post( array(
				'ID'         => $post_id,
				'menu_order' => $index,
			) );
		}

		wp_send_json_success();
	}
}

<?php
/**
 * CartFlows- Onboarding Wizard
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CartFlows_Wizard' ) ) :

	/**
	 * CartFlows_Wizard class.
	 */
	class CartFlows_Wizard {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {

			if ( apply_filters( 'cartflows_enable_setup_wizard', true ) && current_user_can( 'cartflows_manage_settings' ) ) {
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_init', array( $this, 'setup_wizard' ) );
				add_action( 'admin_notices', array( $this, 'show_setup_wizard' ) );
				add_action( 'wp_ajax_page_builder_save_option', array( $this, 'save_page_builder_option' ) );

				// For onboarding user in Sendinblue.
				add_action( 'wp_ajax_wcf_user_onboarding', array( $this, 'add_user_to_mailing_list' ) );

				add_action( 'wp_ajax_usage_tracking_option', array( $this, 'save_usage_tracking_option' ) );

				add_action( 'woocommerce_installed', array( $this, 'disable_woo_setup_redirect' ) );
				add_action( 'admin_init', array( $this, 'hide_notices' ) );
				add_action( 'wp_ajax_wcf_activate_plugin', array( $this, 'activate_plugin' ) );
			}
		}


		/**
		 * Hide a notice if the GET variable is set.
		 */
		public function hide_notices() {

			if ( ! isset( $_GET['wcf-hide-notice'] ) ) {
				return;
			}

			$wcf_hide_notice   = filter_input( INPUT_GET, 'wcf-hide-notice', FILTER_SANITIZE_STRING );
			$_wcf_notice_nonce = filter_input( INPUT_GET, '_wcf_notice_nonce', FILTER_SANITIZE_STRING );

			if ( $wcf_hide_notice && $_wcf_notice_nonce && wp_verify_nonce( sanitize_text_field( wp_unslash( $_wcf_notice_nonce ) ), 'wcf_hide_notices_nonce' ) ) {
				update_option( 'wcf_setup_skipped', true );
			}
		}

		/**
		 *  Disable the woo redirect for new setup.
		 */
		public function disable_woo_setup_redirect() {

			delete_transient( '_wc_activation_redirect' );
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function show_setup_wizard() {

			$screen          = get_current_screen();
			$screen_id       = $screen ? $screen->id : '';
			$allowed_screens = array(
				'cartflows_page_cartflows_settings',
				'edit-cartflows_flow',
				'dashboard',
				'plugins',
			);

			if ( ! in_array( $screen_id, $allowed_screens, true ) ) {
				return;
			}

			$status     = get_option( 'wcf_setup_complete', false );
			$skip_setup = get_option( 'wcf_setup_skipped', false );

			if ( false === $status && ! $skip_setup ) { ?>
				<div class="notice notice-info wcf-notice">
					<p><b><?php esc_html_e( 'Thanks for installing and using CartFlows!', 'cartflows' ); ?></b></p>
					<p><?php esc_html_e( 'It is easy to use the CartFlows. Please use the setup wizard to quick start setup.', 'cartflows' ); ?></p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'index.php?page=cartflow-setup' ) ); ?>" class="button button-primary"> <?php esc_html_e( 'Start Wizard', 'cartflows' ); ?></a>
						<a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcf-hide-notice', 'install' ), 'wcf_hide_notices_nonce', '_wcf_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip Setup', 'cartflows' ); ?></a>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			if ( empty( $_GET['page'] ) || 'cartflow-setup' !== $_GET['page'] ) { //phpcs:ignore
				return;
			}
			$capability = 'cartflows_manage_settings';
			add_dashboard_page( '', '', $capability, 'cartflow-setup', '' );
		}

		/**
		 * Show the setup wizard.
		 */
		public function setup_wizard() {

			if ( empty( $_GET['page'] ) || 'cartflow-setup' !== $_GET['page'] ) { //phpcs:ignore
				return;
			}

			$this->steps = array(
				'basic-config' => array(
					'name'    => __( 'Welcome', 'cartflows' ),
					'view'    => array( $this, 'welcome_step' ),
					'handler' => array( $this, 'welcome_step_save' ),
				),
				'page-builder' => array(
					'name' => __( 'Page Builder', 'cartflows' ),
					'view' => array( $this, 'page_builder_step' ),
				),
				'checkout'     => array(
					'name' => __( 'Checkout', 'cartflows' ),
					'view' => array( $this, 'checkout_step' ),
				),
				'training'     => array(
					'name' => __( 'Training', 'cartflows' ),
					'view' => array( $this, 'training_step' ),
				),
				'setup-ready'  => array(
					'name'    => __( 'Ready!', 'cartflows' ),
					'view'    => array( $this, 'ready_step' ),
					'handler' => '',
				),
			);

			$this->step = isset( $_GET['step'] ) ? sanitize_text_field( $_GET['step'] ) : current( array_keys( $this->steps ) ); //phpcs:ignore

			wp_enqueue_style( 'cartflows-setup', CARTFLOWS_URL . 'admin/assets/css/setup-wizard.css', array( 'dashicons' ), CARTFLOWS_VER );
			wp_style_add_data( 'cartflows-setup', 'rtl', 'replace' );

			wp_enqueue_script( 'cartflows-setup', CARTFLOWS_URL . 'admin/assets/js/setup-wizard.js', array( 'jquery', 'wp-util', 'updates' ), CARTFLOWS_VER, false );

			wp_localize_script( 'cartflows-setup', 'cartflows_setup_vars', self::localize_vars() );

			wp_enqueue_media();

			if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) { //phpcs:ignore
				call_user_func( $this->steps[ $this->step ]['handler'] );
			}

			ob_start();
			$this->setup_wizard_header();
			$this->setup_wizard_steps();
			$this->setup_wizard_content();
			$this->setup_wizard_footer();
			exit;
		}

		/**
		 * Get current step slug
		 */
		public function get_current_step_slug() {
			$keys = array_keys( $this->steps );
			return $keys[ array_search( $this->step, array_keys( $this->steps ), true ) ];
		}

		/**
		 * Get previous step link
		 */
		public function get_prev_step_link() {
			$keys = array_keys( $this->steps );
			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ), true ) - 1 ] );
		}

		/**
		 * Get next step link
		 */
		public function get_next_step_link() {
			$keys = array_keys( $this->steps );
			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ), true ) + 1 ] );
		}

		/**
		 * Get next step link
		 */
		public function get_next_step_plain_link() {
			$keys       = array_keys( $this->steps );
			$step_index = array_search( $this->step, $keys, true );
			$step_index = ( count( $keys ) == $step_index + 1 ) ? $step_index : $step_index + 1;
			$step       = $keys[ $step_index ];
			return admin_url( 'index.php?page=cartflow-setup&step=' . $step );
		}

		/**
		 * Setup Wizard Header.
		 */
		public function setup_wizard_header() {
			set_current_screen();
			?>
			<html <?php language_attributes(); ?>>
			<html>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'CartFlows Setup', 'cartflows' ); ?></title>

				<script type="text/javascript">
					addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
					var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
					var pagenow = '';
				</script>
				<?php wp_print_scripts( array( 'cartflows-setup' ) ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="cartflows-setup wp-core-ui cartflows-step-<?php echo esc_attr( $this->get_current_step_slug() ); ?>">
				<div id="cartflows-logo">
					<img height="40" class="wcf-logo" src="<?php echo CARTFLOWS_URL . 'assets/images/cartflows-logo.svg'; ?>" />
				</div>
			<?php
		}

		/**
		 * Setup Wizard Footer.
		 */
		public function setup_wizard_footer() {

			$admin_url = add_query_arg(
				array(
					'page' => CARTFLOWS_SLUG,
					'path' => 'settings',
				),
				admin_url()
			);

			?>
					<div class="close-button-wrapper">
						<a href="<?php echo esc_url( $admin_url ); ?>" class="wizard-close-link" ><?php esc_html_e( 'Exit Setup Wizard', 'cartflows' ); ?></a>
					</div>
				</body>
			</html>
			<?php
		}

		/**
		 * Output the steps.
		 */
		public function setup_wizard_steps() {

			$ouput_steps = $this->steps;
			?>
			<ol class="cartflows-setup-steps">
				<?php
				foreach ( $ouput_steps as $step_key => $step ) :
					$classes   = '';
					$activated = false;
					if ( $step_key === $this->step ) {
						$classes   = 'active';
						$activated = true;
					} elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true ) ) {
						$classes   = 'done';
						$activated = true;
					}
					?>
					<li class="<?php echo esc_attr( $classes ); ?>">
						<span><?php echo esc_html( $step['name'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step.
		 */
		public function setup_wizard_content() {
			?>
			<input type="hidden" class="wcf-redirect-link" data-redirect-link="<?php echo esc_url_raw( $this->get_next_step_plain_link() ); ?>" >
			<?php

			echo '<div class="cartflows-setup-content">';
			call_user_func( $this->steps[ $this->step ]['view'] );
			echo '</div>';
		}

		/**
		 * Introduction step.
		 */
		public function welcome_step() {
			?>
			<h1><?php esc_html_e( 'Welcome to CartFlows!', 'cartflows' ); ?></h1>
			<p><?php esc_html_e( 'Thank you for choosing CartFlows to get more leads, increase conversions, & maximize profits. This short setup wizard will guide you though configuring CartFlows and creating your first funnel.', 'cartflows' ); ?></p>
			<form method="post">
				<div class="cartflows-usage-tracking cartflows-setup-message">
					<div class="usage-tracking-wrap">
						<h4><?php esc_html_e( 'Let&#x27;s Build it better!', 'cartflows' ); ?> </h4>
						<p><?php esc_html_e( 'Get improved features and faster fixes by sharing non-sensitive data via usage tracking that shows us how CartFlows is used. No personal data is tracked or stored.', 'cartflows' ); ?>
						<a href="https://my.cartflows.com/usage-tracking/"> <?php esc_html_e( 'Learn More', 'cartflows' ); ?></a></p>
						<input type="hidden" name="cartflows-usage-tracking-option" value="no">
						<input type="checkbox" name="cartflows-usage-tracking-option" id="cartflows-usage-tracking-option" value="Yes" checked>
						<label><?php esc_html_e( 'Yes, I am in.', 'cartflows' ); ?></label>
					</div>
				</div>
				<div class="cartflows-setup-actions step">
					<div class="button-prev-wrap">
					</div>
					<div class="wcf-usage-tracking" >
						<input type="submit" class="uct-activate button-primary button button-large " value="<?php esc_html_e( 'Lets Go »', 'cartflows' ); ?>" name="save_step" />
					</div>
					<?php wp_nonce_field( 'cartflow-setup' ); ?>
				</div>

			</form>
			<?php
		}

		/**
		 * Save Locale Settings.
		 */
		public function welcome_step_save() {
			check_admin_referer( 'cartflow-setup' );

			// Update site title & tagline.
			$redirect_url = $this->get_next_step_link();

			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Locale settings
		 */
		public function page_builder_step() {
			?>

			<h1><?php esc_html_e( 'Page Builder Setup', 'cartflows' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Please select a page builder you would like to use with CartFlows.', 'cartflows' ); ?></p>
			<form method="post">
				<table class="cartflows-table widefat">
					<tr class="cartflows-row">
						<td class="cartflows-row-heading">
							<label><?php esc_html_e( 'Select Page Builder', 'cartflows' ); ?></label>
						</td>
						<td class="cartflows-row-content">
							<?php
							$installed_plugins = get_plugins();
							$plugins           = array(
								array(
									'title' => __( 'Elementor', 'cartflows' ),
									'value' => 'elementor',
									'data'  => array(
										'slug'    => 'elementor',
										'init'    => 'elementor/elementor.php',
										'active'  => is_plugin_active( 'elementor/elementor.php' ) ? 'yes' : 'no',
										'install' => isset( $installed_plugins['elementor/elementor.php'] ) ? 'yes' : 'no',
									),
								),
								array(
									'title' => __( 'Beaver Builder Plugin (Lite Version)', 'cartflows' ),
									'value' => 'beaver-builder',
									'data'  => array(
										'slug'    => 'beaver-builder-lite-version',
										'init'    => 'beaver-builder-lite-version/fl-builder.php',
										'active'  => is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ? 'yes' : 'no',
										'install' => isset( $installed_plugins['beaver-builder-lite-version/fl-builder.php'] ) ? 'yes' : 'no',
									),
								),
								array(
									'title' => __( 'Divi', 'cartflows' ),
									'value' => 'divi',
									'data'  => array(
										'slug'    => 'divi',
										'init'    => 'divi',
										'active'  => 'yes',
										'install' => 'NA',
									),
								),
								array(
									'title' => __( 'Gutenberg', 'cartflows' ),
									'value' => 'gutenberg',
									'data'  => array(
										'slug'    => 'ultimate-addons-for-gutenberg',
										'init'    => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
										'active'  => is_plugin_active( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ) ? 'yes' : 'no',
										'install' => isset( $installed_plugins['ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php'] ) ? 'yes' : 'no',
									),
								),
								array(
									'title' => __( 'Other', 'cartflows' ),
									'value' => 'other',
									'data'  => array(
										'slug'    => 'other',
										'init'    => false,
										'active'  => 'yes',
										'install' => 'NA',
									),
								),
							);
							?>
							<input type="hidden" name="save-pb-input" id="save-pb-option" value="1" />
							<select name="page-builder" class="page-builder-list" data-redirect-link="<?php echo esc_url_raw( $this->get_next_step_plain_link() ); ?>">
								<?php
								foreach ( $plugins as $key => $plugin ) {
									echo '<option value="' . esc_attr( $plugin['value'] ) . '" data-install="' . esc_attr( $plugin['data']['install'] ) . '" data-active="' . esc_attr( $plugin['data']['active'] ) . '" data-slug="' . esc_attr( $plugin['data']['slug'] ) . '" data-init="' . esc_attr( $plugin['data']['init'] ) . '">' . esc_html( $plugin['title'] ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
				</table>
				<p><?php esc_html_e( 'While CartFlows Should work with most page builders, we offer templates for the above page builders.', 'cartflows' ); ?></p>
				<div class="cartflows-setup-extra-notice">
					<span>
						<?php esc_html_e( 'The following plugin will be installed and activated for you:', 'cartflows' ); ?>
						<a target="_blank" id="wcf-page-builder" href="https://wordpress.org/plugins/elementor/"> <?php esc_html_e( 'Elementor', 'cartflows' ); ?></a>
					</span>

				</div>
				<div class="cartflows-setup-actions step">
					<div class="button-prev-wrap">
						<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" ><?php esc_html_e( '« Previous', 'cartflows' ); ?></a>
					</div>
					<div class="button-next-wrap">
						<a href="<?php echo esc_url_raw( $this->get_next_step_plain_link() ); ?>" class="button button-large button-next" ><?php esc_html_e( 'Skip this step', 'cartflows' ); ?></a>
						<a href="#" class="button button-primary wcf-install-plugins"><?php esc_html_e( 'Next »', 'cartflows' ); ?></a>
					</div>
					<?php wp_nonce_field( 'cartflow-setup' ); ?>
				</div>
			</form>
			<?php
		}

		/**
		 * Render checkout step.
		 */
		public function checkout_step() {

			$installed_plugins   = get_plugins();
			$is_wc_installed     = isset( $installed_plugins['woocommerce/woocommerce.php'] ) ? 'yes' : 'no';
			$is_wcf_ca_installed = isset( $installed_plugins['woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php'] ) ? 'yes' : 'no';
			$is_active           = class_exists( 'WooCommerce' ) ? 'yes' : 'no';
			$is_wcf_ca_active    = class_exists( 'CARTFLOWS_CA_Loader' ) ? 'yes' : 'no';
			?>
			<h1><?php esc_html_e( 'Choose a checkout', 'cartflows' ); ?></h1>
			<div class="cartflows-setup-message">
				<p>
					<?php esc_html_e( 'While CartFlows is designed to use WooCommerce sell digital and physical products, not all funnels need a checkout system.', 'cartflows' ); ?>
				</p>
				<h4 class="cartflows-setup-message-title"><?php esc_html_e( 'Would you like to install WooCommerce to sell digital and physical products in your funnels?', 'cartflows' ); ?></h4>
				<span><input data-wcf-ca-active="<?php echo esc_attr( $is_wcf_ca_active ); ?>" data-wcf-ca-install="<?php echo esc_attr( $is_wcf_ca_installed ); ?>"  data-woo-active="<?php echo esc_attr( $is_active ); ?>" data-woo-install="<?php echo esc_attr( $is_wc_installed ); ?>" type="hidden" class="wcf-install-wc-input" name="installl-woocommerce" value="" checked></span>
			</div>

			<div class="cartflows-setup-extra-notice">

				<span>
					<?php esc_html_e( 'The following plugin will be installed and activated for you:', 'cartflows' ); ?>
					<a target="_blank" href="https://wordpress.org/plugins/woocommerce/"> <?php esc_html_e( 'WooCommerce', 'cartflows' ); ?></a>,
					<a target="_blank" href="https://wordpress.org/plugins/woo-cart-abandonment-recovery/"> <?php esc_html_e( 'WooCommerce Cart Abandonment Recovery', 'cartflows' ); ?></a>,
					<a target="_blank" href="https://wordpress.org/plugins/checkout-plugins-stripe-woo/"> <?php esc_html_e( 'Checkout Plugins Stripe For WooCommerce', 'cartflows' ); ?></a>
				</span>

			</div>

			<div class="cartflows-setup-actions step">
				<div class="button-prev-wrap">
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" ><?php esc_html_e( '« Previous', 'cartflows' ); ?></a>
				</div>
				<div class="button-next-wrap">
					<a href="<?php echo esc_url_raw( $this->get_next_step_plain_link() ); ?>"  class="button button-large button-next"><?php esc_html_e( 'No thanks', 'cartflows' ); ?></a>
					<a class="wcf-install-wc button-primary button button-large" name="save_step" > <?php esc_html_e( 'Yes', 'cartflows' ); ?> </a>
				</div>
			</div>

			<?php
		}


		/**
		 * Save usage tracking Settings.
		 */
		public function save_usage_tracking_option() {

			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
				return;
			}

			check_ajax_referer( 'wcf-usage-tracking-option', 'security' );

			$allow_usage_tracking = isset( $_POST['allow_usage_tracking'] ) && 'true' == $_POST['allow_usage_tracking'] ? 'yes' : 'no';

			$usage_tracking = get_site_option( 'cf_analytics_optin' );

			if ( ( false === $usage_tracking ) || $allow_usage_tracking !== $usage_tracking ) {
				update_site_option( 'cf_analytics_optin', $allow_usage_tracking );
			}

			wp_send_json_success( get_site_option( 'cf_analytics_optin' ) );
		}

		/**
		 * Save selected page builder in options database.
		 */
		public function save_page_builder_option() {

			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
				return;
			}

			$page_builder = isset( $_POST['page_builder'] ) ? sanitize_text_field( wp_unslash( $_POST['page_builder'] ) ) : ''; //phpcs:ignore

			$wcf_settings = get_option( '_cartflows_common', array() );

			if ( false !== strpos( $page_builder, 'beaver-builder' ) ) {
				$page_builder = 'beaver-builder';
			}

			if ( false !== strpos( $page_builder, 'gutenberg' ) ) {
				$page_builder = 'gutenberg';
			}

			$wcf_settings['default_page_builder'] = $page_builder;

			update_option( '_cartflows_common', $wcf_settings );

			wp_send_json_success(
				array( 'plugin' => $page_builder )
			);

		}

		/**
		 * Redirect the user to create his first flow depending on which UI he is using.
		 */
		public function get_final_page_link() {

			$default_url = add_query_arg(
				array(
					'page' => CARTFLOWS_SLUG,
					'path' => 'flows',
				),
				admin_url()
			);

			return $default_url;

		}

		/**
		 * Add user to the sendingblue mailing list.
		 *
		 * @hooked wp_ajax_wcf_user_onboarding
		 */
		public function add_user_to_mailing_list() {

			$target_url    = CARTFLOWS_TEMPLATES_URL . 'wp-json/cartflows-server/v1/add-subscriber';
			$response_body = array();

			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {

				wp_send_json_error(
					array(
						'success' => false,
						'message' => __( 'Sorry, you are not allowed to do this operation.', 'cartflows' ),
					)
				);
			}

			/**
			 * Nonce verification
			 */
			if ( ! check_ajax_referer( 'wcf-user-onboarding', 'security', false ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => __( 'Nonce validation failed', 'cartflows' ),
					)
				);
			}

			$email     = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
			$user_name = isset( $_POST['user_fname'] ) ? sanitize_text_field( wp_unslash( $_POST['user_fname'] ) ) : '';

			if ( empty( $email ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => __( 'Please enter your email ID.', 'cartflows' ),
					)
				);
			}

			$api_args = array(
				'timeout' => 90,
				'body'    => array(
					'user_email'     => $email,
					'user_fname'     => $user_name,
					'source'         => 'cartflows',
					'add-subscriber' => true,
				),

			);

			$response = wp_remote_post( $target_url, $api_args );

			$has_errors = $this->is_api_error( $response );

			if ( $has_errors['error'] ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $has_errors['error_message'],
					)
				);
			}

			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			wp_send_json_success(
				array(
					'success' => $response_body['success'],
					'message' => $response_body['message'],
				)
			);

		}

		/**
		 * Check is error in the received response.
		 *
		 * @param array $response Received API Response.
		 * @return array $result Error result.
		 */
		public function is_api_error( $response ) {

			$result = array(
				'error'         => false,
				'error_message' => __( 'Oops! Something went wrong. Please refresh the page and try again.', 'cartflows' ),
				'error_code'    => 0,
			);

			if ( is_wp_error( $response ) ) {
				$result['error']         = true;
				$result['error_message'] = $response->get_error_message();
				$result['error_code']    = $response->get_error_code();
			} elseif ( ! empty( wp_remote_retrieve_response_code( $response ) ) && ! in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201, 204 ), true ) ) {
				$result['error']         = true;
				$result['error_message'] = wp_remote_retrieve_response_message( $response );
				$result['error_code']    = wp_remote_retrieve_response_code( $response );
			}

			return $result;
		}

		/**
		 * Final step.
		 */
		public function ready_step() {

			// Set setup wizard status to complete.
			update_option( 'wcf_setup_complete', true );
			?>
			<h1><?php esc_html_e( 'Congratulations, You Did It!', 'cartflows' ); ?></h1>

			<div class="cartflows-setup-next-steps">
				<div class="cartflows-setup-next-steps-last">

					<p class="success">
						<?php
						esc_html_e( 'CartFlows is ready to use on your website. You\'ve successfully completed the setup process and all that is left for you to do is create your first flow.', 'cartflows' )
						?>
					</p>


					<ul class="wcf-wizard-next-steps">
						<li class="wcf-wizard-next-step-item">
							<div class="wcf-wizard-next-step-description">
								<p class="next-step-heading">Next step</p>
								<h3 class="next-step-description">Create First Flow</h3>
								<p class="next-step-extra-info">You're ready to add flows to your website.</p>
							</div>
							<div class="wcf-wizard-next-step-action">
								<p class="wc-setup-actions step">
									<a href="<?php echo $this->get_final_page_link(); ?>" type="button" class="button button-primary button-hero" ><?php esc_html_e( 'Create a flow', 'cartflows' ); ?></a>
								</p>
							</div>
						</li>
					</ul>

				</div>
			</div>
			<?php
		}

		/**
		 * Training course step.
		 */
		public function training_step() {
			$current_user = wp_get_current_user();
			?>
			<h1><?php esc_html_e( 'Exclusive CartFlows Training Course Offer', 'cartflows' ); ?></h1>

			<div id="cartflows_onboarding_wrapper" class="cartflows_onboarding_wrapper">
				<form autocomplete="false" role="form" method="post" action="#" id="cartflows_onboarding_subscriber" enctype="multipart/form-data">
					<div class="cartflows-setup-message">
						<p>
							<?php esc_html_e( 'We want you to get off to a great start using CartFlows, so we would like to give access to our exclusive training course.', 'cartflows' ); ?>
							<?php esc_html_e( 'Get access to this course, for free, by entering your email below.', 'cartflows' ); ?>
						</p>
						<div class="cartflows-onboarding--fields">
							<input id="cartflows_onboarding_name" name="cartflows_onboarding_name" placeholder="<?php esc_html_e( 'Enter Your Name', 'cartflows' ); ?>" value="<?php echo $current_user->display_name; ?>" class="input-text" type="text">
							<input id="cartflows_onboarding_email" name="cartflows_onboarding_email" placeholder="<?php esc_html_e( 'Enter Email address', 'cartflows' ); ?>" value="<?php echo $current_user->user_email; ?>" class="input-text" type="email">
						</div>
						<p class="cartflows_onboarding_terms"><?php esc_html_e( 'By clicking "Allow", you agree to receive our newsletters as part of this course.', 'cartflows' ); ?></p>
					</div>

					<div class="onboarding-error"></div>
					<div class="onboarding-message"></div>

					<div class="cartflows-setup-actions step">
						<div class="button-prev-wrap">
							<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" ><?php esc_html_e( '« Previous', 'cartflows' ); ?></a>
						</div>
						<div class="button-next-wrap">
							<a href="<?php echo esc_url_raw( $this->get_next_step_plain_link() ); ?>" class="button button-large button-next"><?php esc_html_e( 'No thanks', 'cartflows' ); ?></a>
							<button type="submit" name="submit_subscriber" id="submit_subscriber" value="<?php esc_html_e( 'Allow', 'cartflows' ); ?>" class="sendinblue-form-submit btn btn-default button-primary button button-large button-next" name="save_step"><?php esc_html_e( 'Allow', 'cartflows' ); ?></button>
						</div>
					</div>

					<?php wp_nonce_field( 'wcf-user-onboarding', 'wcf_user_onboarding_nonce' ); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Localize variables in admin
		 */
		public function localize_vars() {

			$vars = array();

			$ajax_actions = array(
				'wcf_wc_plugins_activate',
				'wcf_usage_tracking_option',
				'wcf_activate_plugin',
			);

			foreach ( $ajax_actions as $action ) {

				$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
			}

			$plugins = array(
				'woocommerce'                   => $this->get_plugin_status( 'woocommerce/woocommerce.php' ),
				'woo-cart-abandonment-recovery' => $this->get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' ),
				'checkout-plugins-stripe-woo'   => $this->get_plugin_status( 'checkout-plugins-stripe-woo/checkout-plugins-stripe-woo.php' ),
			);

			$vars['plugins'] = $plugins;

			return $vars;
		}

		/**
		 * Get plugin status
		 *
		 * @since 1.1.4
		 *
		 * @param  string $plugin_init_file Plguin init file.
		 * @return mixed
		 */
		public function get_plugin_status( $plugin_init_file ) {

			$installed_plugins = get_plugins();

			if ( ! isset( $installed_plugins[ $plugin_init_file ] ) ) {
				return 'not-installed';
			} elseif ( is_plugin_active( $plugin_init_file ) ) {
				return 'active';
			} else {
				return 'inactive';
			}
		}

		/**
		 * Save usage tracking Settings.
		 */
		public function activate_plugin() {

			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
				return;
			}

			check_ajax_referer( 'wcf-activate-plugin', 'security' );

			$plugin_init = isset( $_POST['plugin_init'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_init'] ) ) : '';
			$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';

			$do_sliently = true;

			$exclude_do_silently = array(
				'woo-cart-abandonment-recovery',
			);

			if ( in_array( $plugin_slug, $exclude_do_silently, true ) ) {
				$do_sliently = false;
			}

			$activate = activate_plugin( $plugin_init, '', false, $do_sliently );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $activate->get_error_message(),
					)
				);
			}

			wp_send_json_success(
				array(
					'success' => true,
					'message' => $plugin_slug,
				)
			);
		}
	}

	new CartFlows_Wizard();

endif;

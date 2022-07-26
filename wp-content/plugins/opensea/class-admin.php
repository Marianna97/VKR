<?php
define("OPENSEA_NAME","Opensea NFT Embed and Storefront");
define("OPENSEA_TAGLINE","Lets you embed your Opensea NFTs quickly and easily");
define("OPENSEA_URL","https://firecask.com/opensea-nft-wordpress-plugin/");
define("OPENSEA_EXTEND_URL","https://wordpress.org/plugins/opensea/");
define("OPENSEA_AUTHOR_TWITTER","alexmoss");
define("OPENSEA_DONATE_LINK","https://www.paypal.me/alexmoss");

	/**
	 * Init plugin
	 * @return [type] [description]
	 */
	function opensea_init(){
		register_setting( 'opensea_options', 'opensea' );
		$new_options = array(
			'osjs' => 'on',
			'header' => 'off',
			'frameheight' => '1000px',
			'storefronturl' => 'https://opensea.io/alexm',
			'refaddress' => '0xbd6359c710dbc6342b0d41208dbb328be21ed9be',
			'attr' => 'off'
		);
	}
	add_action('admin_init', 'opensea_init');

	/**
	 * Add menu item
	 * @return [type] [description]
	 */
	function show_opensea_options() {
		add_options_page('Opensea Options', 'Opensea', 'manage_options', 'opensea', 'opensea_options');
	}
	add_action('admin_menu', 'show_opensea_options');

	/**
	 * [opensea_admin_notice description]
	 * @return [type] [description]
	 */
	function opensea_admin_notice(){
		$options = get_option('opensea');
		if (empty($options['storefronturl'])||$options['storefronturl']==""||$options['storefronturl']=="https://opensea.io/assets") {
			$osadminurl = get_admin_url()."options-general.php?page=opensea";
			echo '<div id="setting-error-settings_updated" class="notice notice-warning"><p>Please <a href="'.$fbadminurl.'">enter your Opensea Storefront URL</a> for your storefront page to work properly.</p></div>';
		}
	}
	add_action('admin_notices', 'opensea_admin_notice');


function opensea_admin_script( $hook ) {
    if ( 'settings_page_opensea' != $hook ) {
        return;
    }
    wp_register_script('opensea-nft-card', 'https://unpkg.com/embeddable-nfts/dist/nft-card.min.js', array('jquery'),'1.1', true);
	wp_enqueue_script( 'opensea-nft-card' );
	wp_register_style( 'fontawesome-css', 'https://pro.fontawesome.com/releases/v5.10.0/css/all.css' );
	wp_enqueue_style('fontawesome-css');
}
add_action( 'admin_enqueue_scripts', 'opensea_admin_script' );


	/**
	 * [opensea_options description]
	 * @return [type] [description]
	 */
	function opensea_options() {
		// Opensea bits
		$default = "http://reviews.evanscycles.com/static/0924-en_gb/noAvatar.gif";
		$size = 70;
		$alex_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( "alex@firecask.com" ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
		?>
		<link href="<?php echo plugins_url( 'admin.css' , __FILE__ ); ?>" rel="stylesheet" type="text/css">
		<div class="opensea_admin_wrap">
			<div class="opensea_admin_top">
				<h1><?php echo OPENSEA_NAME?> <small> - <?php echo OPENSEA_TAGLINE?></small></h1>
				<hr>
			</div>
			<div class="opensea_admin_main_wrap">
				<div class="opensea_admin_main_left">
					<br>
					<div class="opensea_admin_signup">
						Want to know about updates to this plugin without having to log into your site every time? Want to know about other cool plugins we've made? Add your email and we'll add you to our very rare mail outs.

						<!-- Begin MailChimp Signup Form -->
						<div id="mc_embed_signup">
							<form action="https://nftu.us1.list-manage.com/subscribe/post?u=2b7bef25ab6abd15c128119d3&amp;id=de8405002a" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
								<div class="mc-field-group">
									<label for="mce-EMAIL">Email Address
									</label>
									<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL"><button type="submit" name="subscribe" id="mc-embedded-subscribe" class="opensea_admin_green" style="font-size: 16px;">Sign Up!</button>
								</div>
								<div id="mce-responses" class="clear">
									<div class="response" id="mce-error-response" style="display:none"></div>
									<div class="response" id="mce-success-response" style="display:none"></div>
								</div>	<div class="clear"></div>
							</form>
						</div>

						<!--End mc_embed_signup-->
					</div>
					<form method="post" action="options.php" id="options">
						<?php
						settings_fields('opensea_options');
						$options = get_option('opensea');
						?>

						<h3 class="title">Storefront Page Template Settings</h3>
						<table class="form-table">
							
							<?php
							if (empty($options['storefronturl'])||$options['storefronturl']==""||$options['storefronturl']=="https://opensea.io/alexm") { echo '<tr valign="top" id="setting-error-settings_updated" class="notice notice-error"><th scope="row" style="padding-left: 5px!important;">';
						} else {
							echo '<tr valign="top"><th scope="row">';
						}
						?>


						<label for="storefronturl">Default Storefront URL</label></th>
						<td><input id="storefronturl" type="url" name="opensea[storefronturl]" value="<?php echo esc_attr($options['storefronturl']); ?>" style="width: 350px" /><br>
							<?php
							if (empty($options['storefronturl'])||$options['storefronturl']==""||$options['storefronturl']=="https://opensea.io/alexm") {
								echo '<strong>Please enter your Opensea Storefront URL for your storefront page to work properly.</strong>';
							} else {
								echo 'Set your default Opensea storefront URL here.';
							}
							?>	
						</td>
					</tr>
					
					<tr valign="top"><th scope="row"><label for="order">Frame placement</label></th>
						<td>
							<select name="opensea[placement]">
								<option value="relative"<?php if ($options['placement'] == 'relative') { echo ' selected="selected"'; } ?>>Relative</option>
								<option value="absolute"<?php if ($options['placement'] == 'absolute') { echo ' selected="selected"'; } ?>>Absolute</option>
							</select>
						</td>
					</tr>
					<tr valign="top"><th scope="row"><label for="frameheight">Relative Frame Height</label></th>
						<td><input id="frameheight" type="text" name="opensea[frameheight]" value="<?php echo esc_attr($options['frameheight']); ?>" style="width: 75px" /> Default: 1000px. You can use both px and %</td>
					</tr>
					<tr valign="top"><th scope="row"><label for="header">Include Page Header</label></th>
						<td><input id="header" name="opensea[header]" type="checkbox" <?php checked('on', $options['header']); ?> /></td>
					</tr>
					</table>

					<h3 class="title">Single NFT Embed Overrides</h3>
					<table class="form-table">
						<tr valign="top"><th scope="row"><label for="width">Width Override</label></th>
							<td><input id="width" type="number" name="opensea[width]" value="<?php echo esc_attr($options['width']); ?>" min="250" max="2000" maxlength="4" style="width: 75px" /><span class="add-on">px</span><small><br>leave blank for default</small></td>
						</tr>
						<tr valign="top"><th scope="row"><label for="height">Height Override</label></th>
							<td><input id="height" type="number" name="opensea[height]" value="<?php echo esc_attr($options['height']); ?>" min="250" max="2000" maxlength="4" style="width: 75px" /><span class="add-on">px</span><small><br>leave blank for default</small></td>
						</tr>
						<tr valign="top"><th scope="row"><label for="posts">Manual Orientation</label></th>
							<td><input id="osorientation" name="opensea[osorientation]" type="checkbox" <?php checked('off', $options['osorientation']); ?> /> <small>Enabling this forces the same orientation on both desktop and mobile.<br><b>Default orientations - horizontal on desktop, vertical on mobile</b></small></td>
						</tr>
					</table>

					<h3 class="title">Developer Settings</h3>
					<table class="form-table">
						<tr valign="top"><th scope="row"><label for="osjs">Enable Opensea JS</label></th>
							<td><input id="osjs" name="opensea[osjs]" type="checkbox" <?php checked('on', $options['osjs']); ?> /> <small>only disable this if you already have Opensea's JS loaded elsewhere</small></td>
						</tr>
						<tr valign="top"><th scope="row"><label for="refaddress">Referrer Address</label></th>
							<td><input id="refaddress" type="text" name="opensea[refaddress]" value="<?php echo esc_attr($options['refaddress']); ?>" style="width: 350px" /><br>
							<strong>Enter your referrer wallet address to earn affiliate rewards. Leaving blank will enter the author address</strong>
						</td>
						</tr>
						<tr valign="top"><th scope="row"><label for="attr">Credit</label></th>
							<td><input id="credit" name="opensea[attr]" type="checkbox" <?php checked('on', $options['attr']); ?> /></td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
					</p>
				</form>

				<div class="opensea_admin_box">
					<table class="form-table">
						<tr valign="top">
							<td>
								<h3>Using the Shortcodes</h3><br>
								<p>The settings above are to set default behaviour for both shortcodes. You can use the shortcodes to output any Opensea single NFT or storefront.</p>
								<br><br><h3>Single NFT embed</h3><br>
								<p>You can insert the single NFT embed manually in any page or post by simply using the shortcode <code>[opensea]</code>.</p>
								<p>You can also use the following options to change the behaviour of the embed.</p>
								<ul>
									<li><strong>link</strong> - link to the Opensea asset.</li>
									<li><strong>orientation</strong> - set to automatic by default, you can also choose "manual" to show an NFT in portrait mode.</li>
									<li><strong>width</strong> -  override the default width. Must be px or % value</li>
									<li><strong>height</strong>  -  override the default width. Must be px or % value</li>
								</ul>
								<p>Here's an example of using the shortcode:<br><code>[opensea link="https://opensea.io/assets/0x60e4d786628fea6478f785a6d7e704777c86a7c6/15398"]</code></p>
								<p>You can also insert the shortcode directly into your theme with PHP:</p>
								<p><code>&lt;?php echo do_shortcode('[opensea link="https://opensea.io/assets/0x60e4d786628fea6478f785a6d7e704777c86a7c6/15398"]'); ?&gt;</code></p>
								<p>This will then show the following NFT</p>
								<p>
									<nft-card
									contractAddress="0x60e4d786628fea6478f785a6d7e704777c86a7c6"
									tokenId="15398">
								</nft-card>
								</p>
								<br><br><h3>Storefront embed</h3><br>
								<p>You can embed any Opensea storefront within any page within WordPress. To do so:</p>
								<ol>
									<li>Select <b>Opensea Storefront</b> as the page template</li>
									<li>Insert the shortcode <code>[opensea-storefront]</code> to insertyour Opensea storefront.</li>
								</ol>
								<p>You can use the following options to change the behaviour of the embed.</p>
								<ul>
									<li><strong>link</strong> - <b>required</b> URL to the Opensea storefront you want to embed.</li>
									<li><strong>placement</strong> - <b>default: relative</b>. You can also set this to <b>absolute</b>.</li>
									<li><strong>frameheight</strong> - <b>default: 1000px</b>. You can set this to any px or % value.</li>
								</ul>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="opensea_admin_main_right">
				<br>
				<div class="opensea_admin_box">
					<center>
						<!--<a href="https://firecask.com/?utm_source=<?php echo $domain; ?>&utm_medium=referral&utm_campaign=Opensea%2BAdmin" target="_blank"><img src="<?php echo plugins_url( 'images/nftu-landscape.png' , __FILE__ ); ?>" width="220"></a>-->
						<h3>Join the Community</h3>
						<p class="has-text-align-center" style="font-size: 24px;">
							<a href="https://twitter.com/firecask" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
							<a href="https://www.facebook.com/firecask" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>
							<a href="https://www.instagram.com/firecaskmcr" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
						</p>

						<br /><br />
						<a href="<?php echo OPENSEA_DONATE_LINK; ?>" target="_blank"><img class="paypal" src="<?php echo plugins_url( 'images/paypal.gif' , __FILE__ ); ?>" width="147" height="47" title="Please Donate - it helps support this plugin!"></a></center>
						<center><a href="ethereum:0xBd6359c710DbC6342B0d41208DbB328be21ed9be" target="_blank"><img src="<?php echo plugins_url( 'images/nftu-wallet.png' , __FILE__ ); ?>" width="220"></a></center>
						<p class="opensea_admin_clear"><img class="opensea_admin_fl" src="<?php echo $alex_url; ?>" alt="Alex Moss" /> <h3>About the Author</h3><br />Alex Moss is the Co-Founder and Director of <a href="https://firecask.com/?utm_source=<?php echo $domain; ?>&utm_medium=referral&utm_campaign=Opensea%2BAdmin" target="_blank">FireCask</a>, an award-winning online marketing agency and WordPress development specialising in NFT marketing, promotion and collaboration.</p>
					</div>
				</div>
			</div>

			<?php
		}
	?>
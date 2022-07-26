<?php

//ADD OPENSEA JS
function openseasetup() {
	$options = get_option('opensea');
	if (!isset($options['osjs'])) {$options['js'] = "";}
	if ($options['osjs'] == 'on') {
		?><!-- Opensea NFT Embed WordPress Plugin by NFTU: https://nftu.io/opensea-wordpress-plugin/ --><?php
		wp_enqueue_script( 'opensea-nft-card' );
	}

}
add_action('wp_head', 'openseasetup', 100);

//When our custom template has been chosen then display it for the page
add_filter( 'template_include', 'opensea_load_template', 99 );
function opensea_load_template( $template ) {

	global $post;
	$custom_template_slug   = 'storefront.php';
	$page_template_slug     = get_page_template_slug( $post->ID );

	if( $page_template_slug == $custom_template_slug ){
		return plugin_dir_path( __FILE__ ) . $custom_template_slug;
	}

	return $template;

}

function nftu_opensea_shortcode($openseaatts) {
	extract(shortcode_atts(array(
		"nftuos" => get_option('opensea'),
	), $openseaatts));

	if (!empty($openseaatts)) {
		foreach ($openseaatts as $key => $option)
			$nftuos[$key] = $option;
	}
	if (!empty($nftuos['width'])||$nftuos['width']!="") {$width = " width=\"".$nftuos['width']."\"";} else {}
	if (!empty($nftuos['height'])||$nftuos['height']!="") {$height = " height=\"".$nftuos['height']."\"";}
	if (empty($nftuos['refaddress'])||$nftuos['refaddress']=="") {$nftuos['refaddress'] = "0xbd6359c710dbc6342b0d41208dbb328be21ed9be";}
	if (isset($nftuos['orientation'])) {$osorientation = " orientationMode=\"manual\"";}
	if (empty($nftuos['link'])) {$link = "https://opensea.io/assets/0x60e4d786628fea6478f785a6d7e704777c86a7c6/15398";} else {  $link=$nftuos['link'];}
	$path = parse_url($link, PHP_URL_PATH);
	$segments = explode('/', rtrim($path, '/'));
	if (strpos($path, 'matic') !== false) {$addressortoken = 'contractAddress';} else {$addressortoken = 'tokenAddress';}


	if (!empty($nftuos['osjs'])||$nftuos['osjs']!="") {wp_enqueue_script( 'opensea-nft-card' );}
	$openseabox =	"<!-- Opensea NFT Embed WordPress Plugin by NFTU: https://nftu.io/opensea-wordpress-plugin/ --><nft-card ".$addressortoken."=\"".$segments[count($segments)-2]."\" tokenId=\"".$segments[count($segments)-1]."\" ></nft-card>";
	if (!empty($nftuos['attr'])) {
		$openseabox .= '<p><small><center>Powered by <a href="https://nftu.io/opensea-wordpress-plugin/">Opensea WordPress Plugin</a></small></center></p>';
	}
	return $openseabox;
}
add_filter('widget_text', 'do_shortcode');
add_shortcode('opensea', 'nftu_opensea_shortcode');


function nftu_opensea_storesc($openseaatts) {
	extract(shortcode_atts(array(
		"opensea" => get_option('opensea'),
		"link" => $link,
	), $openseaatts));
	if (!empty($openseaatts)) {
		foreach ($openseaatts as $key => $option)
			$nftuos[$key] = $option;
	}
	if (empty($opensea['storefronturl'])||$opensea['storefronturl']=="") {$opensea['storefronturl'] = "https://opensea.io/nftu_io/";}
	if (empty($link)||$link=="") {$link = $opensea['storefronturl']."?embed=true";} else {$link=$link."?embed=true";}
	if (empty($opensea['placement'])||$opensea['placement']=="") {$opensea['placement'] = "relative";}
	if (empty($opensea['frameheight'])||$opensea['frameheight']=="") {$opensea['frameheight'] = "1000px";}
	if (empty($nftuos['refaddress'])||$nftuos['refaddress']=="") {$nftuos['refaddress'] = "0xbd6359c710dbc6342b0d41208dbb328be21ed9be";}
	$openseabox =   "<!-- Opensea NFT Embed WordPress Plugin by NFTU: https://nftu.io/ --><iframe src='".$link."&ref=".$nftuos['refaddress']."' width='100%' height='".$opensea['frameheight']."' frameborder='0' allowfullscreen style=\"position: ".$opensea['placement'].";min-height: ".$opensea['frameheight'].";border: none\"></iframe>";
	if (!empty($nftuos['attr'])) {
		$openseabox .= '<p><small><center>Powered by <a href="https://nftu.io/opensea-wordpress-plugin/">Opensea WordPress Plugin</a></small></center></p>';
	}
	return $openseabox;
}
add_filter('widget_text', 'do_shortcode');
add_shortcode('opensea-storefront', 'nftu_opensea_storesc');


?>
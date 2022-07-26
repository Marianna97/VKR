<?php /* Template Name: Opensea Storefront */ 
get_header();
$osatts = get_option('opensea');
if (!empty($osatts)) {
	foreach ($osatts as $key => $option)
		$nftuos[$key] = $option;
}
if (isset($nftuos['header'])||$nftuos['header']!="") {
	get_template_part( 'template-parts/entry-header' );
}
the_post(); 
if (empty($nftuos['refaddress'])||$nftuos['refaddress']=="") {$nftuos['refaddress'] = "0xbd6359c710dbc6342b0d41208dbb328be21ed9be";}
?>
<iframe src='<?php if (!empty($nftuos['storefronturl'])||$nftuos['storefronturl']!="") {echo $nftuos['storefronturl'];} else {echo 'https://opensea.io/NFTU_io';} ?>?embed=true'
        width='100%'
        height='<?php if (!empty($nftuos['frameheight'])||$nftuos['frameheight']!="") {echo $nftuos['frameheight'];} else {echo '1000px';} ?>'
        frameborder='0'
        allowfullscreen></iframe>
<?php
the_content();
if (!empty($nftuos['attr'])||$nftuos['attr']!="") {
	echo '<center><small>Powered by <a href="https://nftu.io/">Opensea WordPress Plugin</a></small></center>';
}
get_footer();
?> 
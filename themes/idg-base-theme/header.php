<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	idg_base_theme_cat_meta();
	idg_base_theme_tag_meta();
	?>
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>
<?php
$body_classes = isset( $args['body_class'] ) ? $args['body_class'] : [];

// TODO: Simplified header check. Needs expanding how header type is determined.
if ( is_front_page() ) {
	$header_type    = 'large';
	$body_classes[] = 'static-header';
} else {
	$header_type    = 'article';
	$body_classes[] = 'sticky-header';
}

?>

<body <?php body_class( $body_classes ); ?>>
<?php wp_body_open(); ?>

<?php require get_template_directory() . '/template-parts/navigation/mobile.php'; ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'idg-base-theme' ); ?></a>

	<header id="masthead" class="site-header">
		<?php
		require get_template_directory() . '/template-parts/navigation/primary.php';

		if ( 'large' === $header_type ) {
			require get_template_directory() . '/template-parts/navigation/logo-bar.php';
		}

		require get_template_directory() . '/template-parts/navigation/secondary.php';

		?>
	</header><!-- #masthead -->

	<?php do_action( 'idg_after_header' ); ?>

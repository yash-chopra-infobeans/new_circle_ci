<?php
/**
 * File for handling header file.
 *
 * @package idg-base-theme
 */

$post_title = get_the_title();

?>

<!doctype html>
<html ⚡>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1">
	<meta name="amp-consent-blocking" content="amp-ad,amp-embed">
	<script async src="https://cdn.ampproject.org/v0.js"></script>

	<title><?php echo esc_html( idg_base_theme_meta_title() ); ?></title>

	<?php do_action( 'amp_post_template_head', $this ); ?>
</head>

<body>
	<?php require get_template_directory() . '/template-parts/navigation/mobile.php'; ?>

	<header class="page-head">
		<?php
			require get_template_directory() . '/template-parts/navigation/primary.php';
			require get_template_directory() . '/template-parts/navigation/secondary.php';
		?>
	</header>

	<?php do_action( 'idg_after_header' ); ?>

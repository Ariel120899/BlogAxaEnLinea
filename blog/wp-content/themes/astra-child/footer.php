<?php
/**
 * The template for displaying the footer.
 *
 * @package Astra_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php astra_content_bottom(); ?>
	</div> <!-- ast-container -->
	</div><!-- #content -->
<?php
astra_content_after();

astra_footer_before();

get_template_part( 'template-parts/footer/site-footer' );

astra_footer_after();
?>
	</div><!-- #page -->
<?php
astra_body_bottom();
wp_footer();
?>
	</body>
</html>

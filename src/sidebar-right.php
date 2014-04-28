<?php
/**
 * @package ttf-one
 */

$sidebar_id = esc_attr( apply_filters( 'ttf_one_sidebar_right', 'sidebar-right' ) );
?>
<section id="<?php echo $sidebar_id; ?>" class="widget-area <?php echo ( is_active_sidebar( $sidebar_id ) ) ? 'active' : 'inactive'; ?>" role="complementary">
	<?php if ( ! dynamic_sidebar( $sidebar_id ) ) : ?>
		&nbsp;
	<?php endif; ?>
</section>
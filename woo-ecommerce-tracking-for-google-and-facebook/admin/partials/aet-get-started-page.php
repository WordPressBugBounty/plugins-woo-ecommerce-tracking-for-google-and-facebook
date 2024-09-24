<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' );
?>
	<div class="waet-section-left">
		<div class="waet-table res-cl">
			<div class="dots-getting-started-main">
		        <div class="getting-started-content">
		            <span><?php esc_html_e( 'How to Get Started', 'advance-ecommerce-tracking' ); ?></span>
		            <h3><?php esc_html_e( 'Welcome to Ecommerce Tracking Plugin', 'advance-ecommerce-tracking' ); ?></h3>
		            <p><?php esc_html_e( 'Thank you for choosing our top-rated Ecommerce Tracking plugin. Our user-friendly interface makes it easy to set up and gain insights into customer behavior.', 'advance-ecommerce-tracking' ); ?></p>
		            <p>
		                <?php 
		                echo sprintf(
		                    esc_html__('To help you get started, watch the quick tour video on the right. For more help, explore our help documents or visit our %s for detailed video tutorials.', 'advance-ecommerce-tracking'),
		                    '<a href="' . esc_url('https://www.youtube.com/@Dotstore16') . '" target="_blank">' . esc_html__('YouTube channel', 'advance-ecommerce-tracking') . '</a>',
		                );
		                ?>
		            </p>
		            <div class="getting-started-actions">
		                <a href="<?php echo esc_url(add_query_arg(array('page' => 'aet-et-settings'), admin_url('admin.php'))); ?>" class="quick-start"><?php esc_html_e( 'Start Tracking Your Store', 'advance-ecommerce-tracking' ); ?><span class="dashicons dashicons-arrow-right-alt"></span></a>
		                <a href="https://docs.thedotstore.com/article/950-beginners-guide-for-ecommerce-tracking" target="_blank" class="setup-guide"><span class="dashicons dashicons-book-alt"></span><?php esc_html_e( 'Read the Setup Guide', 'advance-ecommerce-tracking' ); ?></a>
		            </div>
		        </div>
		        <div class="getting-started-video">
		            <iframe width="960" height="600" src="<?php echo esc_url('https://www.youtube.com/embed/YdS6bFBltzg'); ?>" title="<?php esc_attr_e( 'Plugin Tour', 'advance-ecommerce-tracking' ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		        </div>
		    </div>
		</div>
	</div>
</div>
</div>
</div>
</div>
<?php
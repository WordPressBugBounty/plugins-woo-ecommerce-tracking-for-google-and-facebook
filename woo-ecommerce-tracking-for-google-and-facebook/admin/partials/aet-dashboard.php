<?php
/**
 * GA4 Overview dashboard page (Premium).
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';

$aet_d_status  = class_exists( 'AET_GA4_Data_API' ) ? AET_GA4_Data_API::get_status() : array( 'connected' => false );
$connected     = ! empty( $aet_d_status['connected'] );
$google_linked = ! empty( $aet_d_status['google_linked'] );
$pending       = class_exists( 'AET_GA4_OAuth' ) ? AET_GA4_OAuth::get_pending_properties() : array();
$auth_url      = class_exists( 'AET_GA4_OAuth' ) ? AET_GA4_OAuth::get_auth_url( 'aet-dashboard' ) : '';

$oauth_flash = filter_input( INPUT_GET, 'aet_ga4_oauth', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$oauth_msg   = filter_input( INPUT_GET, 'aet_ga4_message', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$oauth_msg   = $oauth_msg ? rawurldecode( $oauth_msg ) : '';

$needs_setup = ! $connected;
?>
<div class="aet-dashboard-wrap">
	<div class="aet-dashboard-header">
		<div class="aet-dashboard-header__text">
			<h1><?php esc_html_e( 'Overview', 'advance-ecommerce-tracking' ); ?></h1>
			<p><?php esc_html_e( 'Get an overview of your website analytics.', 'advance-ecommerce-tracking' ); ?></p>
		</div>
		<?php if ( $connected ) : ?>
			<div class="aet-dashboard-header__controls">
				<label class="aet-dashboard-date">
					<span class="screen-reader-text"><?php esc_html_e( 'Date range', 'advance-ecommerce-tracking' ); ?></span>
					<input type="date" id="aet-dash-start" />
					<span class="aet-dashboard-date__sep">–</span>
					<input type="date" id="aet-dash-end" />
				</label>
				<label class="aet-dashboard-compare">
					<span><?php esc_html_e( 'Compare:', 'advance-ecommerce-tracking' ); ?></span>
					<input type="date" id="aet-dash-cmp-start" />
					<span class="aet-dashboard-date__sep">–</span>
					<input type="date" id="aet-dash-cmp-end" />
				</label>
				<button type="button" class="button button-primary" id="aet-dash-refresh">
					<?php esc_html_e( 'Apply', 'advance-ecommerce-tracking' ); ?>
				</button>
				<button type="button" class="button aet-dashboard-disconnect" id="aet_ga4_dash_disconnect">
					<?php esc_html_e( 'Disconnect', 'advance-ecommerce-tracking' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $needs_setup ) : ?>
		<div class="aet-dashboard-empty">
			<div class="aet-dashboard-empty__card aet-dashboard-setup">
				<span class="dashicons dashicons-chart-area" aria-hidden="true"></span>
				<h2><?php esc_html_e( 'Connect GA4 to view your Overview', 'advance-ecommerce-tracking' ); ?></h2>
				<p>
					<?php esc_html_e( 'Connect with Google to load GA4 reports in this dashboard.', 'advance-ecommerce-tracking' ); ?>
				</p>

				<?php if ( 'error' === $oauth_flash && $oauth_msg ) : ?>
					<div class="aet-ga4-dash-creds__status is-disconnected">
						<span class="dashicons dashicons-warning"></span>
						<span><?php echo esc_html( $oauth_msg ); ?></span>
					</div>
				<?php elseif ( 'connected' === $oauth_flash ) : ?>
					<div class="aet-ga4-dash-creds__status is-connected">
						<span class="dashicons dashicons-yes-alt"></span>
						<span><?php esc_html_e( 'Google account connected successfully.', 'advance-ecommerce-tracking' ); ?></span>
					</div>
				<?php elseif ( 'pick_property' === $oauth_flash || ( $google_linked && ! $connected ) ) : ?>
					<div class="aet-ga4-dash-creds__status is-connected">
						<span class="dashicons dashicons-yes-alt"></span>
						<span><?php esc_html_e( 'Google account connected. Select a GA4 property below.', 'advance-ecommerce-tracking' ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( ( $google_linked || ! empty( $pending ) ) && ! $connected ) : ?>
					<div class="aet-ga4-property-pick">
						<label for="aet_ga4_property_select"><?php esc_html_e( 'Select GA4 Property', 'advance-ecommerce-tracking' ); ?></label>
						<?php if ( ! empty( $pending ) ) : ?>
							<select id="aet_ga4_property_select">
								<option value=""><?php esc_html_e( '— Select property —', 'advance-ecommerce-tracking' ); ?></option>
								<?php foreach ( $pending as $prop ) : ?>
									<option value="<?php echo esc_attr( $prop['id'] ); ?>" data-name="<?php echo esc_attr( $prop['name'] ); ?>">
										<?php
										echo esc_html(
											sprintf(
												'%s (%s) — %s',
												$prop['name'],
												$prop['id'],
												$prop['account']
											)
										);
										?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input type="text" id="aet_ga4_property_id_manual" placeholder="123456789" value="<?php echo esc_attr( isset( $aet_d_status['property_id'] ) ? $aet_d_status['property_id'] : '' ); ?>" />
						<?php endif; ?>
						<button type="button" class="button button-primary" id="aet_ga4_dash_save_property">
							<?php esc_html_e( 'Save Property', 'advance-ecommerce-tracking' ); ?>
						</button>
					</div>
				<?php endif; ?>

				<div class="aet-ga4-dash-creds__actions">
					<?php if ( ! $google_linked && ! $connected && $auth_url ) : ?>
						<a class="button button-primary aet-ga4-connect-btn" id="aet_ga4_dash_connect" href="<?php echo esc_url( $auth_url ); ?>">
							<?php esc_html_e( 'Connect with Google', 'advance-ecommerce-tracking' ); ?>
						</a>
					<?php endif; ?>
					<span class="aet-ga4-dash-creds__result" id="aet_ga4_dash_result" aria-live="polite"></span>
				</div>
			</div>
		</div>
	<?php else : ?>
		<div id="aet-dashboard-loading" class="aet-dashboard-loading" aria-live="polite">
			<span class="spinner is-active"></span>
			<span><?php esc_html_e( 'Loading analytics…', 'advance-ecommerce-tracking' ); ?></span>
		</div>
		<div id="aet-dashboard-error" class="aet-dashboard-error" hidden></div>
		<div id="aet-dashboard-content" class="aet-dashboard-content" hidden>

			<div class="aet-kpi-grid">
				<div class="aet-kpi-card" data-kpi="sessions">
					<div class="aet-kpi-card__top">
						<span class="aet-kpi-card__icon aet-kpi-card__icon--sessions dashicons dashicons-groups"></span>
						<span class="aet-kpi-card__label"><?php esc_html_e( 'Total Sessions', 'advance-ecommerce-tracking' ); ?></span>
					</div>
					<div class="aet-kpi-card__value">—</div>
					<div class="aet-kpi-card__change">—</div>
					<canvas class="aet-kpi-sparkline" height="48"></canvas>
				</div>
				<div class="aet-kpi-card" data-kpi="page_views">
					<div class="aet-kpi-card__top">
						<span class="aet-kpi-card__icon aet-kpi-card__icon--views dashicons dashicons-visibility"></span>
						<span class="aet-kpi-card__label"><?php esc_html_e( 'Total Page Views', 'advance-ecommerce-tracking' ); ?></span>
					</div>
					<div class="aet-kpi-card__value">—</div>
					<div class="aet-kpi-card__change">—</div>
					<canvas class="aet-kpi-sparkline" height="48"></canvas>
				</div>
				<div class="aet-kpi-card" data-kpi="avg_duration">
					<div class="aet-kpi-card__top">
						<span class="aet-kpi-card__icon aet-kpi-card__icon--duration dashicons dashicons-clock"></span>
						<span class="aet-kpi-card__label"><?php esc_html_e( 'Avg. Session Duration', 'advance-ecommerce-tracking' ); ?></span>
					</div>
					<div class="aet-kpi-card__value">—</div>
					<div class="aet-kpi-card__change">—</div>
					<canvas class="aet-kpi-sparkline" height="48"></canvas>
				</div>
				<div class="aet-kpi-card" data-kpi="bounce_rate">
					<div class="aet-kpi-card__top">
						<span class="aet-kpi-card__icon aet-kpi-card__icon--bounce dashicons dashicons-undo"></span>
						<span class="aet-kpi-card__label"><?php esc_html_e( 'Bounce Rate', 'advance-ecommerce-tracking' ); ?></span>
					</div>
					<div class="aet-kpi-card__value">—</div>
					<div class="aet-kpi-card__change">—</div>
					<canvas class="aet-kpi-sparkline" height="48"></canvas>
				</div>
			</div>

			<div class="aet-mid-grid">
				<div class="aet-panel aet-panel--donut">
					<h3><?php esc_html_e( 'New vs Returning Users', 'advance-ecommerce-tracking' ); ?></h3>
					<div class="aet-donut-wrap">
						<div class="aet-donut-chart">
							<canvas id="aet-donut-chart"></canvas>
						</div>
						<div class="aet-donut-legend" id="aet-donut-legend"></div>
					</div>
				</div>

				<div class="aet-panel aet-panel--devices">
					<div class="aet-panel__head">
						<h3><?php esc_html_e( 'Devices', 'advance-ecommerce-tracking' ); ?></h3>
						<div class="aet-tabs" data-tabs="devices">
							<button type="button" class="is-active" data-tab="os"><?php esc_html_e( 'OS', 'advance-ecommerce-tracking' ); ?></button>
							<button type="button" data-tab="browsers"><?php esc_html_e( 'Browsers', 'advance-ecommerce-tracking' ); ?></button>
							<button type="button" data-tab="platforms"><?php esc_html_e( 'Platforms', 'advance-ecommerce-tracking' ); ?></button>
							<button type="button" data-tab="screens"><?php esc_html_e( 'Screens', 'advance-ecommerce-tracking' ); ?></button>
						</div>
					</div>
					<div class="aet-hbar-list" id="aet-devices-list"></div>
				</div>

				<div class="aet-panel aet-panel--heatmap">
					<div class="aet-panel__head">
						<h3><?php esc_html_e( 'Weekdays', 'advance-ecommerce-tracking' ); ?></h3>
						<div class="aet-tabs" data-tabs="heatmap">
							<button type="button" class="is-active" data-tab="sessions"><?php esc_html_e( 'Sessions', 'advance-ecommerce-tracking' ); ?></button>
							<button type="button" data-tab="page_views"><?php esc_html_e( 'Page Views', 'advance-ecommerce-tracking' ); ?></button>
						</div>
					</div>
					<div class="aet-heatmap" id="aet-heatmap"></div>
					<div class="aet-heatmap-legend">
						<span><?php esc_html_e( 'Low', 'advance-ecommerce-tracking' ); ?></span>
						<span class="aet-heatmap-legend__bar"></span>
						<span><?php esc_html_e( 'High', 'advance-ecommerce-tracking' ); ?></span>
					</div>
				</div>
			</div>

			<div class="aet-bottom-grid">
				<div class="aet-panel aet-panel--pages">
					<div class="aet-panel__head">
						<div class="aet-tabs" data-tabs="pages">
							<button type="button" class="is-active" data-tab="top"><?php esc_html_e( 'Top Pages', 'advance-ecommerce-tracking' ); ?></button>
							<button type="button" data-tab="entry"><?php esc_html_e( 'Entry Pages', 'advance-ecommerce-tracking' ); ?></button>
							<button type="button" data-tab="exit"><?php esc_html_e( 'Exit Pages', 'advance-ecommerce-tracking' ); ?></button>
						</div>
					</div>
					<table class="aet-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Page', 'advance-ecommerce-tracking' ); ?></th>
								<th><?php esc_html_e( 'Views', 'advance-ecommerce-tracking' ); ?></th>
							</tr>
						</thead>
						<tbody id="aet-pages-body"></tbody>
					</table>
				</div>

				<div class="aet-panel aet-panel--channels">
					<div class="aet-panel__head">
						<h3><?php esc_html_e( 'Top Channels', 'advance-ecommerce-tracking' ); ?></h3>
					</div>
					<table class="aet-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Channel', 'advance-ecommerce-tracking' ); ?></th>
								<th><?php esc_html_e( 'Sessions', 'advance-ecommerce-tracking' ); ?></th>
								<th>%</th>
							</tr>
						</thead>
						<tbody id="aet-channels-body"></tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
</div>
</div>
</div>
</div>

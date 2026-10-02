<?php
$legal_name     = halveron_option( 'legal_name' );
$company_number = halveron_option( 'company_number' );
?>
<footer class="site-footer">
	<div class="site-footer__inner container">
		<div class="site-footer__main">
			<div class="site-footer__brand">
				<?php get_template_part( 'template-parts/logo', null, array( 'class' => 'logo logo--light' ) ); ?>
			</div>
			<nav class="site-footer__nav" aria-label="Footer">
				<?php foreach ( HALVERON_FOOTER_MENUS as $location ) : ?>
					<div>
						<h2 class="visually-hidden"><?php echo esc_html( halveron_menu_name( $location ) ); ?></h2>
						<?php halveron_menu( $location, 'site-footer__list' ); ?>
					</div>
				<?php endforeach; ?>
			</nav>
			<ul class="icon-list">
				<?php foreach ( halveron_option_rows( 'social' ) as $social ) : ?>
					<li>
						<a class="icon-link" href="<?php echo esc_url( (string) ( $social['url'] ?? '' ) ); ?>" rel="noopener">
							<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( (string) ( $social['icon'] ?? '' ) ); ?>" /></svg>
							<span class="visually-hidden"><?php echo esc_html( (string) ( $social['label'] ?? '' ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="site-footer__contact">
				<a href="<?php echo esc_url( halveron_page_url( 'contact' ) ); ?>">Contact</a><span class="site-footer__contact-separator" aria-hidden="true">|</span><?php echo halveron_phone( halveron_option( 'phone' ), halveron_option( 'phone_href' ) ); ?>
			</p>
			<div class="site-footer__legal">
				<p class="site-footer__notice"><?php echo esc_html( halveron_option( 'footer_notice' ) ); ?></p>
				<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $legal_name ); ?>. Registered in England and Wales, no. <?php echo esc_html( $company_number ); ?>.</p>
				<nav aria-label="Legal">
					<?php halveron_menu( 'legal', 'site-footer__legal-list' ); ?>
				</nav>
				<p><?php echo esc_html( halveron_option( 'footer_note' ) ); ?></p>
			</div>
		</div>
		<div class="site-footer__forum">
			<h2 class="site-footer__forum-title"><?php echo esc_html( halveron_option( 'forum_panel_heading' ) ); ?></h2>
			<p class="site-footer__forum-text"><?php echo esc_html( halveron_option( 'forum_panel_text' ) ); ?></p>
			<a class="button button--light" href="<?php echo esc_url( halveron_page_url( 'forum' ) ); ?>"><?php echo esc_html( halveron_option( 'forum_panel_button_label' ) ); ?></a>
		</div>
	</div>
</footer>

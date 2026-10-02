<?php
get_header();

$front_id     = (int) get_queried_object_id();
$training_id  = halveron_page_id( 'training' );
$capabilities = halveron_capabilities();
$cards        = halveron_rows( 'home_feature_cards', $front_id );

halveron_prime_attachments( array_merge(
	array_column( $cards, 'image' ),
	array_map( fn ( WP_Post $capability ): int => (int) halveron_meta( 'image', $capability->ID ), $capabilities )
) );
?>
<section class="home-hero" aria-labelledby="hero-title">
	<picture class="home-hero__picture">
		<?php
		echo halveron_image( (int) get_post_thumbnail_id( $front_id ), 'home-hero__image', array(
			'alt'           => '',
			'fetchpriority' => 'high',
			'loading'       => false,
			'sizes'         => '100vw',
		), 'hero' );
		?>
	</picture>
	<div class="home-hero__inner container">
		<h1 class="home-hero__title" id="hero-title">
			<span class="home-hero__line"><?php echo esc_html( halveron_text( 'home_hero_line_1', $front_id ) ); ?></span>
			<span class="home-hero__line"><?php echo esc_html( halveron_text( 'home_hero_line_2', $front_id ) ); ?></span>
		</h1>
		<div class="home-hero__panel">
			<p class="home-hero__text"><?php echo esc_html( halveron_text( 'home_hero_text', $front_id ) ); ?></p>
			<a class="button button--light" href="<?php echo esc_url( halveron_url( halveron_text( 'home_hero_button_url', $front_id ) ) ); ?>"><?php echo esc_html( halveron_text( 'home_hero_button_label', $front_id ) ); ?></a>
		</div>
	</div>
	<nav class="page-index" aria-label="On this page">
		<ul class="page-index__list">
			<?php foreach ( array_keys( HALVERON_HOME_INDEX ) as $index => $anchor ) : ?>
				<li><a class="<?php echo 0 === $index ? 'page-index__link page-index__link--current' : 'page-index__link'; ?>" href="#<?php echo esc_attr( $anchor ); ?>"><span class="page-index__label visually-hidden"><?php echo esc_html( HALVERON_HOME_INDEX[ $anchor ] ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
</section>

<section class="section intro" id="introduction" aria-labelledby="introduction-title">
	<div class="container">
		<svg class="icon intro__chevron" aria-hidden="true" focusable="false"><use href="#icon-chevron-down" /></svg>
		<h2 class="statement" id="introduction-title"><?php echo esc_html( halveron_text( 'home_intro_heading', $front_id ) ); ?></h2>
		<p class="intro__text"><?php echo esc_html( halveron_text( 'home_intro_text', $front_id ) ); ?></p>
		<ul class="grid-3 intro__cards">
			<?php foreach ( $cards as $card ) : ?>
				<li class="feature-card">
					<h3 class="feature-card__title"><?php echo esc_html( (string) ( $card['title'] ?? '' ) ); ?></h3>
					<?php echo halveron_image( (int) ( $card['image'] ?? 0 ), 'feature-card__image', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
					<p class="feature-card__text"><?php echo esc_html( (string) ( $card['text'] ?? '' ) ); ?></p>
					<a class="button" href="<?php echo esc_url( halveron_url( (string) ( $card['button_url'] ?? '' ) ) ); ?>"><?php echo esc_html( (string) ( $card['button_label'] ?? '' ) ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section band--lavender cap-tabs" id="capabilities" aria-labelledby="capabilities-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="capabilities-title"><?php echo esc_html( halveron_text( 'home_capabilities_heading', $front_id ) ); ?></h2>
		</div>
		<ul class="cap-tabs__list">
			<?php foreach ( $capabilities as $capability ) : ?>
				<li>
					<a class="cap-tabs__tab" href="#capability-<?php echo esc_attr( $capability->post_name ); ?>">
						<svg class="icon icon--capability" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( $capability->post_name ); ?>" /></svg>
						<?php echo esc_html( get_the_title( $capability ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( $capabilities as $capability ) : ?>
			<div class="cap-tabs__panel" id="capability-<?php echo esc_attr( $capability->post_name ); ?>" tabindex="-1">
				<h3 class="visually-hidden"><?php echo esc_html( get_the_title( $capability ) ); ?></h3>
				<?php
				get_template_part( 'template-parts/media-block', null, array(
					'image' => halveron_image( (int) halveron_meta( 'image', $capability->ID ), 'media-block__image', array( 'alt' => '', 'loading' => 'lazy' ) ),
					'text'  => halveron_text( 'intro', $capability->ID ),
				) );
				?>
				<div class="cap-tabs__action">
					<a class="button" href="<?php echo esc_url( get_permalink( $capability ) ); ?>"><?php echo esc_html( halveron_text( 'tab_button', $capability->ID ) ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="section band--purple training" id="training" aria-labelledby="training-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="training-title"><?php echo esc_html( halveron_text( 'home_training_heading', $front_id ) ); ?></h2>
			<a class="button button--cyan" href="<?php echo esc_url( (string) get_post_type_archive_link( 'course' ) ); ?>">View all<span class="visually-hidden"> training courses</span></a>
		</div>
		<p class="training__intro"><?php echo esc_html( halveron_text( 'home_training_intro', $front_id ) ); ?></p>
		<div class="training__grid">
			<div>
				<h3 class="rule-heading">Open training</h3>
				<?php get_template_part( 'template-parts/course-list', null, array( 'courses' => halveron_ordered( halveron_meta( 'home_training_courses', $front_id ), 'course' ) ) ); ?>
			</div>
			<div class="training__custom">
				<h3 class="rule-heading">Custom training</h3>
				<p><?php echo esc_html( halveron_text( 'home_custom_text', $front_id ) ); ?></p>
				<?php get_template_part( 'template-parts/training-list', null, array( 'rows' => halveron_rows( 'training_custom_list', $training_id ) ) ); ?>
				<a class="button" href="<?php echo esc_url( halveron_url( halveron_text( 'home_custom_button_url', $front_id ) ) ); ?>"><?php echo esc_html( halveron_text( 'home_custom_button_label', $front_id ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="section" id="clients" aria-labelledby="clients-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="clients-title"><?php echo esc_html( halveron_text( 'home_clients_heading', $front_id ) ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/client-carousel', null, array( 'organisations' => halveron_organisations( 'client' ) ) ); ?>
		<div class="section-foot">
			<a class="button" href="<?php echo esc_url( halveron_url( halveron_text( 'home_clients_button_url', $front_id ) ) ); ?>"><?php echo esc_html( halveron_text( 'home_clients_button_label', $front_id ) ); ?></a>
		</div>
	</div>
</section>

<section class="section band--lavender" id="thinking" aria-labelledby="thinking-title">
	<div class="container">
		<?php $thinking_heading = halveron_text( 'home_thinking_heading', $front_id ); ?>
		<div class="section-head section-head--centred">
			<h2 class="display" id="thinking-title"><?php echo esc_html( $thinking_heading ); ?></h2>
			<a class="button button--outline" href="<?php echo esc_url( halveron_page_url( 'thinking' ) ); ?>">View all<span class="visually-hidden"> <?php echo esc_html( $thinking_heading ); ?></span></a>
		</div>
		<?php get_template_part( 'template-parts/insight-grid', null, array( 'posts' => halveron_ordered( halveron_meta( 'home_thinking_posts', $front_id ), 'post' ) ) ); ?>
	</div>
</section>
<?php
get_footer();

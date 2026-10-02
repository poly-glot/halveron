<?php
get_header();

$training_page = get_queried_object();
$open = halveron_by_menu_order( 'course', array(
	'meta_key'   => 'course_type',
	'meta_value' => 'open',
) );

get_template_part( 'template-parts/section-header', null, array(
	'image'    => halveron_thumbnail( $training_page, 'sub-header__image', array( 'alt' => '', 'loading' => false ) ),
	'modifier' => 'sub-header--image',
	'page'     => $training_page,
) );
?>
<section class="section band--purple training" id="about" aria-labelledby="training-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="training-title"><?php echo esc_html( halveron_text( 'training_courses_heading', $training_page->ID ) ); ?></h2>
		</div>
		<p class="training__intro"><?php echo esc_html( halveron_text( 'training_courses_intro', $training_page->ID ) ); ?></p>
		<div class="training__grid">
			<div id="open-training">
				<h3 class="rule-heading">Open training</h3>
				<p><?php echo esc_html( halveron_text( 'training_open_text', $training_page->ID ) ); ?></p>
				<?php get_template_part( 'template-parts/course-list', null, array( 'courses' => $open ) ); ?>
			</div>
			<div class="training__custom" id="custom-training">
				<h3 class="rule-heading">Custom training</h3>
				<p><?php echo esc_html( halveron_text( 'training_custom_text', $training_page->ID ) ); ?></p>
				<?php get_template_part( 'template-parts/training-list', null, array( 'rows' => halveron_rows( 'training_custom_list', $training_page->ID ) ) ); ?>
				<a class="button" href="<?php echo esc_url( halveron_url( halveron_text( 'training_custom_button_url', $training_page->ID ) ) ); ?>"><?php echo esc_html( halveron_text( 'training_custom_button_label', $training_page->ID ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="section" id="clients" aria-labelledby="clients-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="clients-title"><?php echo esc_html( halveron_text( 'training_clients_heading', $training_page->ID ) ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/client-carousel', null, array( 'organisations' => halveron_organisations( 'client' ) ) ); ?>
	</div>
</section>

<section class="section band--lavender" aria-labelledby="facts-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="facts-title"><?php echo esc_html( halveron_text( 'training_facts_heading', $training_page->ID ) ); ?></h2>
			<p class="section-head__intro"><?php echo esc_html( halveron_text( 'training_facts_intro', $training_page->ID ) ); ?></p>
		</div>
		<ul class="stat-grid">
			<?php foreach ( halveron_rows( 'training_stats', $training_page->ID ) as $index => $stat ) : ?>
				<li class="<?php echo esc_attr( HALVERON_STAT_TINTS[ $index % count( HALVERON_STAT_TINTS ) ] ); ?>"><p class="stat__figure"><?php echo esc_html( (string) ( $stat['figure'] ?? '' ) ); ?></p><p class="stat__label"><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></p></li>
			<?php endforeach; ?>
			<li class="stat stat--quote">
				<blockquote>
					<p class="stat__quote"><?php echo esc_html( halveron_quote( halveron_text( 'training_quote', $training_page->ID ) ) ); ?></p>
					<cite class="stat__cite"><?php echo esc_html( halveron_text( 'training_quote_cite', $training_page->ID ) ); ?></cite>
				</blockquote>
			</li>
			<li class="stat stat--image stat--wide">
				<?php echo halveron_image( (int) halveron_meta( 'training_image', $training_page->ID ), 'stat__image', array( 'loading' => 'lazy' ) ); ?>
				<p class="stat__label"><?php echo esc_html( halveron_text( 'training_image_caption', $training_page->ID ) ); ?></p>
			</li>
		</ul>
	</div>
</section>

<section class="section" aria-labelledby="partners-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="partners-title"><?php echo esc_html( halveron_text( 'training_partners_heading', $training_page->ID ) ); ?></h2>
		</div>
		<?php get_template_part( 'template-parts/partner-list', null, array( 'organisations' => halveron_organisations( 'accrediting' ) ) ); ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/section-news', null, array( 'page' => $training_page ) );

get_footer();

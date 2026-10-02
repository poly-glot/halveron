<?php
get_header();

$page_id = (int) get_queried_object_id();
$values  = halveron_rows( 'about_values', $page_id );
$groups  = array();

foreach ( halveron_by_menu_order( 'person' ) as $person ) {
	$groups[ halveron_text( 'group', $person->ID ) ][] = $person;
}

get_template_part( 'template-parts/page-header', null, array(
	'after'      => 'template-parts/about-intro',
	'after_args' => array( 'page_id' => $page_id ),
	'title'      => get_the_title( $page_id ),
) );
?>
<div class="section band--cyan">
	<div class="container">
		<h2 class="display values__title"><?php echo esc_html( halveron_text( 'about_values_heading', $page_id ) ); ?></h2>
		<section class="carousel" aria-label="Our values" data-carousel>
			<?php get_template_part( 'template-parts/carousel-button', null, array( 'direction' => 'previous', 'label' => 'Previous value' ) ); ?>
			<ul class="carousel__track" tabindex="0" data-carousel-track>
				<?php foreach ( $values as $value ) : ?>
					<li class="carousel__slide value" data-carousel-slide>
						<h3 class="value__title"><?php echo esc_html( (string) ( $value['title'] ?? '' ) ); ?></h3>
						<p class="value__text"><?php echo esc_html( (string) ( $value['text'] ?? '' ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php get_template_part( 'template-parts/carousel-button', null, array( 'direction' => 'next', 'label' => 'Next value' ) ); ?>
			<?php get_template_part( 'template-parts/carousel-dots', null, array( 'titles' => array_map( fn ( array $value ): string => (string) ( $value['title'] ?? '' ), $values ) ) ); ?>
		</section>
	</div>
</div>
<?php
get_template_part( 'template-parts/team-grid', null, array(
	'heading' => halveron_text( 'about_management_heading', $page_id ),
	'id'      => 'management-title',
	'intro'   => halveron_text( 'about_management_intro', $page_id ),
	'people'  => $groups['management'] ?? array(),
) );

get_template_part( 'template-parts/team-grid', null, array(
	'heading' => halveron_text( 'about_advisory_heading', $page_id ),
	'id'      => 'advisory-title',
	'intro'   => halveron_text( 'about_advisory_intro', $page_id ),
	'people'  => $groups['advisory'] ?? array(),
) );
?>
<section class="section band--paper" aria-labelledby="csr-title">
	<div class="container">
		<div class="section-head">
			<h2 class="display" id="csr-title"><?php echo esc_html( halveron_text( 'about_csr_heading', $page_id ) ); ?></h2>
		</div>
		<div class="prose">
			<?php echo halveron_rich( halveron_text( 'about_csr_text', $page_id ) ); ?>
			<p><a class="button" href="<?php echo esc_url( halveron_url( halveron_text( 'about_csr_button_url', $page_id ) ) ); ?>"><?php echo esc_html( halveron_text( 'about_csr_button_label', $page_id ) ); ?></a></p>
		</div>
	</div>
</section>
<?php
get_footer();

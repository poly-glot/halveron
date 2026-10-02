<?php
get_header();

$careers_page  = get_queried_object();
$careers_email = halveron_option( 'careers_email' );
$voices        = halveron_rows( 'careers_voices', $careers_page->ID );
$people        = halveron_posts_by_id( array_column( $voices, 'person' ), 'person' );

get_template_part( 'template-parts/section-header', null, array(
	'modifier' => 'sub-header--black',
	'page'     => $careers_page,
	'strip'    => halveron_thumbnail( $careers_page, 'sub-header__strip', array( 'alt' => '', 'loading' => false ) ),
) );
?>
<section class="section band--purple" aria-labelledby="looking-title">
	<div class="with-aside container">
		<div class="with-aside__main">
			<div class="stack" id="working-here">
				<h2 class="display" id="looking-title"><?php echo esc_html( halveron_text( 'careers_looking_heading', $careers_page->ID ) ); ?></h2>
				<div class="prose">
					<?php echo halveron_rich( halveron_text( 'careers_looking_text', $careers_page->ID ) ); ?>
				</div>
			</div>
			<section aria-labelledby="work-title">
				<h2 class="rule-heading" id="work-title"><?php echo esc_html( halveron_text( 'careers_work_heading', $careers_page->ID ) ); ?></h2>
				<ul class="grid-2">
					<?php foreach ( halveron_rows( 'careers_work_columns', $careers_page->ID ) as $column ) : ?>
						<li class="stack">
							<h3 class="caps-heading"><?php echo esc_html( (string) ( $column['title'] ?? '' ) ); ?></h3>
							<?php echo halveron_paragraphs( (string) ( $column['text'] ?? '' ) ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<aside class="with-aside__aside" aria-labelledby="start-title">
			<div class="stack">
				<h2 class="rule-heading" id="start-title"><?php echo esc_html( halveron_text( 'careers_aside_heading', $careers_page->ID ) ); ?></h2>
				<h3 class="subheading"><?php echo esc_html( halveron_text( 'careers_aside_subheading', $careers_page->ID ) ); ?></h3>
				<?php echo halveron_rich( halveron_text( 'careers_aside_text', $careers_page->ID ) ); ?>
				<a class="button button--light" href="<?php echo esc_url( halveron_mailto( $careers_email ) ); ?>"><?php echo esc_html( halveron_text( 'careers_aside_button_label', $careers_page->ID ) ); ?></a>
			</div>
		</aside>
	</div>
</section>

<section class="section band--cyan" aria-labelledby="voices-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="voices-title"><?php echo esc_html( halveron_text( 'careers_voices_heading', $careers_page->ID ) ); ?></h2>
		</div>
		<ul class="voices">
			<?php foreach ( $voices as $voice ) : ?>
				<?php
				$person = $people[ (int) ( $voice['person'] ?? 0 ) ] ?? null;

				if ( ! $person ) {
					continue;
				}
				?>
				<li>
					<figure class="voice">
						<figcaption class="voice__person">
							<?php echo halveron_portrait( $person, 'voice__portrait' ); ?>
							<span class="voice__name"><?php echo esc_html( get_the_title( $person ) ); ?></span>
							<span class="voice__role"><?php echo esc_html( halveron_text( 'role', $person->ID ) ); ?></span>
						</figcaption>
						<blockquote class="voice__quote"><p><?php echo esc_html( halveron_quote( (string) ( $voice['quote'] ?? '' ) ) ); ?></p></blockquote>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<section class="section" id="business-schools" aria-labelledby="schools-title">
	<div class="container stack">
		<h2 class="display" id="schools-title"><?php echo esc_html( halveron_text( 'careers_schools_heading', $careers_page->ID ) ); ?></h2>
		<div class="prose">
			<?php echo halveron_rich( halveron_text( 'careers_schools_text', $careers_page->ID ) ); ?>
		</div>
		<a class="text-link" href="<?php echo esc_url( halveron_mailto( $careers_email ) ); ?>"><?php echo esc_html( halveron_text( 'careers_schools_link_label', $careers_page->ID ) ); ?><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-right" /></svg></a>
	</div>
</section>
<?php
get_template_part( 'template-parts/section-news', null, array( 'page' => $careers_page ) );

get_footer();

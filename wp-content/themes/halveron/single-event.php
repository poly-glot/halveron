<?php
get_header();

while ( have_posts() ) :
	the_post();

	$event_id = get_the_ID();
	$date     = halveron_text( 'event_date', $event_id );
	$agenda   = halveron_rows( 'agenda', $event_id );
	$host     = halveron_post( halveron_meta( 'host_organisation', $event_id ), 'organisation' );
	$facts    = array_filter( array(
		'Date'  => '' === $date ? '' : sprintf( '<time datetime="%s">%s</time>', esc_attr( $date ), esc_html( halveron_date( $date, 'l j F Y' ) ) ),
		'Time'  => esc_html( halveron_text( 'time', $event_id ) ),
		'Host'  => $host ? esc_html( get_the_title( $host ) ) : '',
		'Venue' => esc_html( halveron_text( 'venue', $event_id ) ),
	) );

	get_template_part( 'template-parts/sub-header', null, array(
		'image'    => halveron_thumbnail( get_post(), 'sub-header__image', array( 'loading' => false ) ),
		'modifier' => 'sub-header--image',
		'title'    => get_the_title(),
	) );
	?>
	<div class="with-aside container section">
		<div class="with-aside__main">
			<p class="lede"><?php echo esc_html( halveron_text( 'summary', $event_id ) ); ?></p>
			<?php if ( $agenda ) : ?>
				<section class="stack" aria-labelledby="agenda-title">
					<h2 class="heading" id="agenda-title">Agenda</h2>
					<ol class="agenda">
						<?php foreach ( $agenda as $slot ) : ?>
							<li class="agenda__item"><span class="agenda__time"><?php echo esc_html( (string) ( $slot['time'] ?? '' ) ); ?></span><span><?php echo esc_html( (string) ( $slot['item'] ?? '' ) ); ?></span></li>
						<?php endforeach; ?>
					</ol>
				</section>
				<?php echo halveron_rich( halveron_text( 'registration', $event_id ) ); ?>
			<?php else : ?>
				<section class="stack" aria-labelledby="happened-title">
					<h2 class="heading" id="happened-title">What happened</h2>
					<div class="prose">
						<?php the_content(); ?>
					</div>
				</section>
			<?php endif; ?>
			<a class="text-link" href="<?php echo esc_url( halveron_page_url( 'forum' ) ); ?>"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-left" /></svg>Back to the Forum</a>
		</div>
		<aside class="with-aside__aside" aria-label="Event details">
			<?php get_template_part( 'template-parts/facts', null, array( 'facts' => $facts ) ); ?>
		</aside>
	</div>
	<?php
endwhile;

get_footer();

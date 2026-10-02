<?php
get_header();

$conference_page = get_queried_object();
$events_email    = halveron_option( 'events_email' );
$conference_name            = halveron_text( 'conference_name', $conference_page->ID );
$lines           = array_filter( array(
	$conference_name,
	halveron_date( halveron_text( 'conference_date', $conference_page->ID ), 'l j F Y' ),
	halveron_text( 'conference_location', $conference_page->ID ),
) );
$booking_url     = halveron_mailto( $events_email, $conference_name );
$speakers        = halveron_rows( 'conference_speakers', $conference_page->ID );
$people          = halveron_posts_by_id( array_merge( array_column( $speakers, 'person' ), array( halveron_meta( 'conference_contact', $conference_page->ID ) ) ), 'person' );
$contact         = $people[ (int) halveron_meta( 'conference_contact', $conference_page->ID ) ] ?? null;
$past_link       = halveron_post( halveron_meta( 'conference_past_link', $conference_page->ID ), 'post' );

halveron_prime_attachments( array( halveron_meta( 'conference_venue_image', $conference_page->ID ), halveron_meta( 'conference_past_image', $conference_page->ID ) ) );

get_template_part( 'template-parts/section-header', null, array(
	'image'     => halveron_thumbnail( $conference_page, 'sub-header__image', array( 'loading' => false ) ),
	'lines'     => $lines,
	'modifier'  => 'sub-header--image',
	'page'      => $conference_page,
	'statement' => false,
) );
?>
<section class="section band--purple" aria-labelledby="about">
	<div class="container">
		<h2 class="display" id="about"><?php echo esc_html( halveron_text( 'conference_theme_heading', $conference_page->ID ) ); ?></h2>
		<div class="prose prose--wide">
			<?php echo halveron_rich( halveron_text( 'conference_theme_text', $conference_page->ID ) ); ?>
		</div>
	</div>
	<div class="with-aside container">
		<section class="with-aside__main" aria-labelledby="speakers-title">
			<h2 class="rule-heading" id="speakers-title"><?php echo esc_html( halveron_text( 'conference_speakers_heading', $conference_page->ID ) ); ?></h2>
			<ul class="speakers">
				<?php foreach ( $speakers as $speaker ) : ?>
					<?php
					$person = $people[ (int) ( $speaker['person'] ?? 0 ) ] ?? null;

					if ( ! $person ) {
						continue;
					}
					?>
					<li class="speaker">
						<div class="speaker__body">
							<h3 class="speaker__name"><?php echo esc_html( get_the_title( $person ) ); ?></h3>
							<p class="speaker__role"><?php echo esc_html( halveron_text( 'role', $person->ID ) ); ?></p>
							<p class="speaker__session">Session: <?php echo esc_html( (string) ( $speaker['session'] ?? '' ) ); ?></p>
							<p class="speaker__bio"><?php echo esc_html( (string) ( $speaker['text'] ?? '' ) ); ?></p>
						</div>
						<?php echo halveron_portrait( $person, 'speaker__portrait' ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<aside class="with-aside__aside" id="next-conference" aria-labelledby="tickets-title">
			<div class="ticket-box">
				<h2 class="rule-heading ticket-box__title" id="tickets-title"><?php echo esc_html( halveron_text( 'conference_tickets_heading', $conference_page->ID ) ); ?></h2>
				<?php echo halveron_paragraphs( halveron_text( 'conference_tickets_text', $conference_page->ID ) ); ?>
				<ul class="ticket-box__list">
					<?php foreach ( halveron_rows( 'conference_tickets_list', $conference_page->ID ) as $ticket ) : ?>
						<li><?php echo esc_html( (string) ( $ticket['text'] ?? '' ) ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php echo halveron_rich( halveron_text( 'conference_booking_text', $conference_page->ID ) ); ?>
				<?php get_template_part( 'template-parts/book-button', null, array( 'label' => halveron_text( 'conference_book_label', $conference_page->ID ), 'name' => $conference_name, 'url' => $booking_url ) ); ?>
			</div>
		</aside>
	</div>
</section>

<section class="section band--cyan" aria-labelledby="venue-title">
	<div class="container">
		<h2 class="heading" id="venue-title"><?php echo esc_html( halveron_text( 'conference_venue_heading', $conference_page->ID ) ); ?></h2>
		<div class="venue">
			<?php echo halveron_image( (int) halveron_meta( 'conference_venue_image', $conference_page->ID ), 'venue__image', array( 'loading' => 'lazy' ) ); ?>
			<div class="venue__text"><?php echo halveron_rich( halveron_text( 'conference_venue_text', $conference_page->ID ) ); ?></div>
		</div>
	</div>
</section>

<section class="section" aria-label="Book your place">
	<div class="container">
		<div class="event-callout">
			<p class="event-callout__lead"><?php echo esc_html( halveron_text( 'conference_callout_text', $conference_page->ID ) ); ?></p>
			<p class="event-callout__lines"><?php foreach ( $lines as $line ) : ?><span><?php echo esc_html( $line ); ?></span><?php endforeach; ?></p>
			<?php get_template_part( 'template-parts/book-button', null, array( 'label' => halveron_text( 'conference_book_label', $conference_page->ID ), 'name' => $conference_name, 'url' => $booking_url ) ); ?>
		</div>
	</div>
</section>

<section class="section band--paper" id="past-conferences" aria-labelledby="past-title">
	<div class="container">
		<h2 class="display" id="past-title"><?php echo esc_html( halveron_text( 'conference_past_heading', $conference_page->ID ) ); ?></h2>
		<div class="media-block media-block--plain">
			<?php echo halveron_image( (int) halveron_meta( 'conference_past_image', $conference_page->ID ), 'media-block__image', array( 'loading' => 'lazy' ) ); ?>
			<div class="media-block__panel">
				<h3 class="subheading"><?php echo esc_html( halveron_text( 'conference_past_title', $conference_page->ID ) ); ?></h3>
				<?php echo halveron_rich( halveron_text( 'conference_past_text', $conference_page->ID ) ); ?>
				<?php if ( $past_link ) : ?>
					<a class="text-link" href="<?php echo esc_url( get_permalink( $past_link ) ); ?>"><?php echo esc_html( halveron_text( 'conference_past_link_label', $conference_page->ID ) ); ?><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-right" /></svg></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section class="section" id="contact" aria-labelledby="contact-title">
	<div class="with-aside container">
		<div class="with-aside__main">
			<h2 class="display" id="contact-title"><?php echo esc_html( halveron_text( 'conference_contact_heading', $conference_page->ID ) ); ?></h2>
			<?php echo halveron_paragraphs( halveron_text( 'conference_contact_text', $conference_page->ID ) ); ?>
		</div>
		<?php if ( $contact ) : ?>
			<div class="with-aside__aside">
				<?php
				get_template_part( 'template-parts/person-aside', null, array(
					'button_label' => halveron_text( 'conference_contact_button_label', $conference_page->ID ),
					'button_url'   => halveron_mailto( $events_email ),
					'extra_email'  => $events_email,
					'intro'        => 'Talk to ' . halveron_first_name( $contact ) . ':',
					'person'       => $contact,
				) );
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();

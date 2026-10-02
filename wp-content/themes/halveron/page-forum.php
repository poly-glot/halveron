<?php
get_header();

$forum_page  = get_queried_object();
$forum_email = halveron_option( 'forum_email' );
$events      = halveron_posts_by_id( array_merge( halveron_ids( halveron_meta( 'forum_next_event', $forum_page->ID ) ), halveron_ids( halveron_meta( 'forum_past_events', $forum_page->ID ) ) ), 'event' );
$next_events = array_values( array_intersect_key( $events, array_flip( halveron_ids( halveron_meta( 'forum_next_event', $forum_page->ID ) ) ) ) );
$past_events = array_values( array_intersect_key( $events, array_flip( halveron_ids( halveron_meta( 'forum_past_events', $forum_page->ID ) ) ) ) );
$contact     = halveron_post( halveron_meta( 'forum_contact', $forum_page->ID ), 'person' );

get_template_part( 'template-parts/section-header', null, array(
	'image'    => halveron_thumbnail( $forum_page, 'sub-header__image', array( 'alt' => '', 'loading' => false ) ),
	'modifier' => 'sub-header--image',
	'page'     => $forum_page,
) );
?>
<section class="section band--purple" aria-labelledby="about">
	<div class="with-aside container">
		<div class="with-aside__main">
			<h2 class="display" id="about"><?php echo esc_html( halveron_text( 'forum_about_heading', $forum_page->ID ) ); ?></h2>
			<div class="prose">
				<?php echo halveron_rich( halveron_text( 'forum_about_text', $forum_page->ID ) ); ?>
			</div>
			<h3 class="rule-heading" id="next-event"><?php echo esc_html( halveron_text( 'forum_next_heading', $forum_page->ID ) ); ?></h3>
			<p><?php echo esc_html( halveron_text( 'forum_next_text', $forum_page->ID ) ); ?></p>
			<?php get_template_part( 'template-parts/event-list', null, array( 'events' => $next_events ) ); ?>
			<h3 class="rule-heading" id="past-events"><?php echo esc_html( halveron_text( 'forum_past_heading', $forum_page->ID ) ); ?></h3>
			<?php get_template_part( 'template-parts/event-list', null, array( 'events' => $past_events ) ); ?>
		</div>
		<aside class="with-aside__aside" aria-labelledby="forum-contact-title">
			<h2 class="rule-heading" id="forum-contact-title"><?php echo esc_html( halveron_text( 'forum_contact_heading', $forum_page->ID ) ); ?></h2>
			<?php if ( $contact ) : ?>
				<?php
				get_template_part( 'template-parts/person-aside', null, array(
					'extra_email' => $forum_email,
					'intro'       => halveron_text( 'forum_contact_text', $forum_page->ID ),
					'person'      => $contact,
				) );
				?>
			<?php endif; ?>
		</aside>
	</div>
</section>

<section class="section" id="membership" aria-labelledby="membership-title">
	<div class="container">
		<h2 class="display" id="membership-title"><?php echo esc_html( halveron_text( 'forum_membership_heading', $forum_page->ID ) ); ?></h2>
		<div class="prose">
			<?php echo halveron_rich( halveron_text( 'forum_membership_text', $forum_page->ID ) ); ?>
		</div>
		<a class="button" href="<?php echo esc_url( halveron_mailto( $forum_email, halveron_text( 'forum_membership_subject', $forum_page->ID ) ) ); ?>"><?php echo esc_html( halveron_text( 'forum_membership_button_label', $forum_page->ID ) ); ?></a>
	</div>
</section>

<section class="section band--lavender" aria-labelledby="members-title">
	<div class="container">
		<div class="section-head section-head--centred">
			<h2 class="display" id="members-title"><?php echo esc_html( halveron_text( 'forum_members_heading', $forum_page->ID ) ); ?></h2>
			<p class="visually-hidden"><?php echo esc_html( halveron_text( 'forum_members_intro', $forum_page->ID ) ); ?></p>
		</div>
		<?php
		get_template_part( 'template-parts/logo-carousel', null, array(
			'label'         => 'Forum members',
			'next'          => 'Next members',
			'organisations' => halveron_organisations( 'forum_member' ),
			'previous'      => 'Previous members',
		) );
		?>
	</div>
</section>
<?php
get_template_part( 'template-parts/section-news', null, array( 'page' => $forum_page ) );

get_footer();

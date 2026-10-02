<?php
$job      = $args['job'];
$title    = get_the_title( $job );
$note     = halveron_text( 'note', $job->ID );
$sections = array(
	'What you will do' => halveron_rows( 'responsibilities', $job->ID ),
	'What you bring'   => halveron_rows( 'you_bring', $job->ID ),
);
?>
<dl class="accordion__facts">
	<div><dt>Location</dt><dd><?php echo esc_html( halveron_text( 'location', $job->ID ) ); ?></dd></div>
	<div><dt>Type</dt><dd><?php echo esc_html( HALVERON_JOB_TYPES[ halveron_text( 'job_type', $job->ID ) ] ?? '' ); ?></dd></div>
</dl>
<?php echo halveron_paragraphs( halveron_text( 'summary', $job->ID ) ); ?>
<?php foreach ( array_filter( $sections ) as $heading => $rows ) : ?>
	<h4><?php echo esc_html( $heading ); ?></h4>
	<ul>
		<?php foreach ( $rows as $row ) : ?>
			<li><?php echo esc_html( (string) ( $row['text'] ?? '' ) ); ?></li>
		<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
<?php if ( '' !== $note ) : ?>
	<?php echo halveron_paragraphs( $note ); ?>
<?php endif; ?>
<a class="button" href="<?php echo esc_url( halveron_mailto( halveron_option( 'careers_email' ), $title ) ); ?>">Apply for this role<span class="visually-hidden">: <?php echo esc_html( $title ); ?></span></a>

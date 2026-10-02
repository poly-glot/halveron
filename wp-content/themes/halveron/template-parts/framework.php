<?php
$post_id = $args['post_id'];
$contact = halveron_post( halveron_meta( 'contact', $post_id ), 'person' );
?>
<div class="with-aside container">
	<div class="with-aside__main">
		<section class="stack" aria-labelledby="framework-title">
			<h2 class="heading" id="framework-title">A framework for delivering results</h2>
			<div class="prose prose--columns">
				<?php echo halveron_rich( halveron_text( 'body', $post_id ) ); ?>
			</div>
			<?php
			get_template_part( 'template-parts/pull-quote', null, array(
				'attribution' => halveron_text( 'quote_attribution', $post_id ),
				'quote'       => halveron_text( 'quote', $post_id ),
			) );
			?>
		</section>
		<?php if ( halveron_rows( 'diagram_steps', $post_id ) ) : ?>
			<?php get_template_part( 'template-parts/flow-diagram', null, array( 'post_id' => $post_id ) ); ?>
		<?php endif; ?>
	</div>
	<?php if ( $contact ) : ?>
		<aside class="with-aside__aside" aria-label="Contact">
			<?php get_template_part( 'template-parts/person-aside', null, array( 'person' => $contact ) ); ?>
		</aside>
	<?php endif; ?>
</div>

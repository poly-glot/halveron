<form class="search-panel__form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
	<label class="visually-hidden" for="<?php echo esc_attr( $args['id'] ); ?>">Search our website</label>
	<input class="search-panel__input" id="<?php echo esc_attr( $args['id'] ); ?>" type="search" name="s" value="<?php echo esc_attr( $args['value'] ?? '' ); ?>" placeholder="Search our website…" autocomplete="off">
	<button class="search-panel__button" type="submit">
		<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-search" /></svg>
		<span class="visually-hidden">Search</span>
	</button>
</form>

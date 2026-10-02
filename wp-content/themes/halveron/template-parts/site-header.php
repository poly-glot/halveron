<header class="site-header">
	<div class="search-panel" id="search-panel" hidden>
		<div class="search-panel__inner container">
			<?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'search-panel-input' ) ); ?>
			<button class="search-panel__button search-panel__button--close" type="button" data-search-close>
				<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-close" /></svg>
				<span class="visually-hidden">Close search</span>
			</button>
		</div>
	</div>
	<div class="site-header__utility container">
		<?php get_template_part( 'template-parts/logo' ); ?>
		<ul class="utility-nav">
			<li class="utility-nav__item utility-nav__item--contact"><a class="utility-nav__link" href="<?php echo esc_url( halveron_page_url( 'contact' ) ); ?>">Contact<span class="visually-hidden"> us</span></a></li>
			<li class="utility-nav__item">
				<a class="utility-nav__link utility-nav__link--phone" href="<?php echo esc_url( 'tel:' . halveron_option( 'phone_href' ) ); ?>">
					<svg class="icon utility-nav__icon" aria-hidden="true" focusable="false"><use href="#icon-phone" /></svg>
					<span class="utility-nav__number"><span class="visually-hidden">Telephone </span><?php echo str_replace( ' ', '&nbsp;', esc_html( halveron_option( 'phone' ) ) ); ?></span>
				</a>
			</li>
		</ul>
		<a class="site-search" href="<?php echo esc_url( home_url( '/search/' ) ); ?>" data-search-link>
			<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-search" /></svg>
			<span class="visually-hidden">Search the site</span>
		</a>
		<button class="site-search" type="button" aria-expanded="false" aria-controls="search-panel" data-search-toggle hidden>
			<svg class="icon site-search__open" aria-hidden="true" focusable="false"><use href="#icon-search" /></svg>
			<svg class="icon site-search__close" aria-hidden="true" focusable="false"><use href="#icon-close" /></svg>
			<span class="visually-hidden site-search__open">Search the site</span>
			<span class="visually-hidden site-search__close">Close search</span>
		</button>
	</div>
	<nav class="site-nav" aria-label="Main">
		<div class="site-nav__bar container">
			<button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav-menu" hidden>
				<svg class="icon site-nav__open" aria-hidden="true" focusable="false"><use href="#icon-menu" /></svg>
				<svg class="icon site-nav__close" aria-hidden="true" focusable="false"><use href="#icon-close" /></svg>
				<span class="site-nav__open">Menu</span>
				<span class="site-nav__close">Close menu</span>
			</button>
			<div class="site-nav__menu" id="site-nav-menu">
				<?php halveron_menu( 'primary', 'site-nav__list' ); ?>
				<?php halveron_menu( 'secondary', 'site-nav__list site-nav__list--sections' ); ?>
			</div>
		</div>
	</nav>
</header>

<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if ( get_stylesheet() !== 'halveron' ) {
	switch_theme( 'halveron' );
	echo "theme: switched to halveron\n";
}

if ( ! function_exists( 'halveron_setup' ) ) {
	require_once get_theme_file_path( 'functions.php' );
	halveron_setup();
}

kses_remove_filters();

function halveron_seed_media_signature(): string {
	return md5( wp_json_encode( [ wp_get_registered_image_subsizes(), wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ] ) );
}

function halveron_seed_attachment( $filename, $alt ) {
	$existing = get_posts( [
		'meta_key'    => '_halveron_seed_file',
		'meta_value'  => $filename,
		'numberposts' => 1,
		'post_status' => 'any',
		'post_type'   => 'attachment',
	] );

	$signature = halveron_seed_media_signature();

	if ( $existing && get_post_meta( $existing[0]->ID, '_halveron_seed_signature', true ) === $signature ) {
		$id = (int) $existing[0]->ID;
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		return [ $id, 0 ];
	}

	if ( $existing ) {
		wp_delete_attachment( $existing[0]->ID, true );
		echo "media REGENERATING {$filename}\n";
	}

	$source = __DIR__ . '/img/' . $filename;

	if ( ! file_exists( $source ) ) {
		echo "media MISSING {$filename}\n";
		return [ 0, 0 ];
	}

	$tmp = wp_tempnam( $filename );
	copy( $source, $tmp );

	$id = media_handle_sideload( [ 'name' => $filename, 'tmp_name' => $tmp ], 0 );

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		echo "media FAILED {$filename}: " . $id->get_error_message() . "\n";
		return [ 0, 0 ];
	}

	update_post_meta( $id, '_halveron_seed_file', $filename );
	update_post_meta( $id, '_halveron_seed_signature', $signature );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );

	return [ (int) $id, 1 ];
}

function halveron_seed_post( $type, $path, $args, $meta = [], $thumb = 0 ) {
	$found = get_page_by_path( $path, OBJECT, $type );
	$found = $found && $found->post_type === $type ? $found : null;
	$args  = array_merge( [ 'post_name' => basename( $path ), 'post_status' => 'publish', 'post_type' => $type ], $args );

	if ( $found ) {
		$args['ID'] = $found->ID;
		$id         = wp_update_post( $args );
		$created    = 0;
	} else {
		$id      = wp_insert_post( $args );
		$created = 1;
	}

	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}

	if ( $thumb ) {
		set_post_thumbnail( $id, $thumb );
	}

	return [ (int) $id, $created ];
}

function halveron_seed_pick( $ids, $slugs ) {
	return array_values( array_filter( array_map( fn( $slug ) => $ids[ $slug ] ?? 0, $slugs ) ) );
}

function halveron_seed_menu( $name, $items ) {
	$menu     = wp_get_nav_menu_object( $name );
	$id       = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	$existing = array_column( wp_get_nav_menu_items( $id ) ?: [], null, 'title' );
	$created  = 0;

	foreach ( array_values( $items ) as $index => $item ) {
		$item   += [ 'menu-item-object-id' => 0, 'menu-item-position' => $index + 1, 'menu-item-status' => 'publish', 'menu-item-url' => '' ];
		$current = $existing[ $item['menu-item-title'] ] ?? null;

		if ( ! $current ) {
			wp_update_nav_menu_item( $id, 0, $item );
			$created++;
		} elseif ( (int) $current->menu_order !== $item['menu-item-position'] || (int) $current->object_id !== (int) $item['menu-item-object-id'] || ( 'custom' === $item['menu-item-type'] && $current->url !== $item['menu-item-url'] ) ) {
			wp_update_nav_menu_item( $id, $current->db_id, $item );
		}
	}

	return [ (int) $id, $created ];
}

update_option( 'blogdescription', 'Operations and customer-experience consultancy' );
update_option( 'blogname', 'Halveron Consulting' );
update_option( 'date_format', 'j F Y' );
update_option( 'start_of_week', 1 );
update_option( 'time_format', 'H:i' );
update_option( 'timezone_string', 'Europe/London' );

echo "settings: identity, date formats and timezone set\n";

$photos = [
	'hero-home.jpg'                             => '',
	'person-ruth-calloway.jpg'                  => 'Portrait of Ruth Calloway',
	'person-marcus-adeyemi.jpg'                 => 'Portrait of Marcus Adeyemi',
	'person-anjali-mehta.jpg'                   => 'Portrait of Anjali Mehta',
	'person-tom-whitlock.jpg'                   => 'Portrait of Tom Whitlock',
	'person-grace-okonkwo.jpg'                  => 'Portrait of Grace Okonkwo',
	'person-daniel-fry.jpg'                     => 'Portrait of Daniel Fry',
	'person-margaret-ellison.jpg'               => 'Portrait of Professor Margaret Ellison',
	'person-helen-ashby.jpg'                    => 'Portrait of Helen Ashby',
	'person-rafael-moreno.jpg'                  => 'Portrait of Rafael Moreno',
	'cap-business-transformation.jpg'           => '',
	'cap-operations-strategy.jpg'               => '',
	'cap-operating-model-design.jpg'            => '',
	'cap-organisational-effectiveness.jpg'      => '',
	'cap-customer-experience.jpg'               => '',
	'cap-process-improvement.jpg'               => '',
	'cap-digital-at-pace.jpg'                   => '',
	'cap-learning-and-development.jpg'          => '',
	'sector-banking.jpg'                        => '',
	'sector-insurance.jpg'                      => '',
	'sector-utilities.jpg'                      => '',
	'sector-travel.jpg'                         => '',
	'sector-education.jpg'                      => '',
	'sector-public-sector.jpg'                  => '',
	'sector-healthcare.jpg'                     => '',
	'sector-managed-services.jpg'               => '',
	'insight-start-with-demand.jpg'             => "A hand-drawn tally of customer calls by hour on a team leader's desk",
	'insight-future-of-service-2026.jpg'        => 'Delegates listening to a session in a high-ceilinged conference hall',
	'insight-regulation-work-for-you.jpg'       => 'Two managers reviewing a printed process map at a meeting table',
	'insight-public-sector-service-academy.jpg' => 'Public-sector managers working through an exercise around a workshop table',
	'insight-customer-journey-insurer.jpg'      => 'A claims handler on a call at her desk in an open-plan office',
	'insight-contact-centre-demand-report.jpg'  => 'A modern contact centre at the start of the morning shift',
	'insight-failure-demand-explained.jpg'      => 'A stack of returned letters on a post-room table',
	'insight-complaints-handling-utility.jpg'   => 'A team leader and a field engineer discussing complaint causes at a whiteboard',
	'insight-technology-in-travel.jpg'          => 'Passengers using self-service kiosks and a staffed desk in a ferry terminal',
	'insight-lean-forum-100-members.jpg'        => 'Forum members listening to a host beside a team performance board',
	'insight-back-office-productivity-bank.jpg' => 'A bank operations team holding a morning huddle at a planning board',
	'insight-coaching-hybrid-teams.jpg'         => 'A team leader coaching a colleague over a video call',
	'insight-change-delivery-council.jpg'       => 'Council officers sorting a wall of project cards in a meeting room',
	'event-forum-november-2026.jpg'             => 'Operators at their desks in a rail control centre',
	'event-forum-june-2026.jpg'                 => "Forum members touring a building society's operations floor",
	'event-forum-march-2026.jpg'                => "A field engineer and a scheduler planning the day's jobs together",
	'training-hero.jpg'                         => '',
	'training-classroom.jpg'                    => 'A trainer leading a session for a small group of delegates',
	'training-workshop.jpg'                     => 'Course delegates mapping a process together with sticky notes',
	'training-coaching.jpg'                     => 'A team leader coaching a colleague at her desk',
	'forum-hero.jpg'                            => '',
	'conference-hero.jpg'                       => 'Delegates arriving at a converted Victorian printworks in London',
	'conference-venue.jpg'                      => 'The main hall at The Assembly Rooms, set with round tables',
	'conference-last-year.jpg'                  => 'Delegates listening to a session at the Future of Service Conference 2026',
	'map-woodstock.jpg'                         => '',
];

$img           = [];
$created_media = 0;

foreach ( $photos as $file => $alt ) {
	[ $img[ $file ], $created ] = halveron_seed_attachment( $file, $alt );
	$created_media += $created;
}

echo "media: {$created_media} created, " . count( array_filter( $img ) ) . ' of ' . count( $photos ) . " ready\n";

$people = [
	'ruth-calloway'    => [
		'meta'  => [
			'bio'        => 'Ruth joined Halveron in 2012 and has been Managing Director since 2019. Before that she spent fifteen years in a large British insurer, rising from claims team leader to director of customer operations for a business of 3,000 people. She still leads client work in utilities and large transformation programmes. Ruth read Economics at the University of Edinburgh. She keeps bees at her home in the Cotswolds and claims they taught her most of what she knows about flow.',
			'email'      => 'ruth.calloway@halveron.junaid.guru',
			'first_name' => 'Ruth',
			'group'      => 'management',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'I ask what the customer would say about this before I ask what it costs.',
			'role'       => 'Managing Director',
		],
		'title' => 'Ruth Calloway',
	],
	'marcus-adeyemi'   => [
		'meta'  => [
			'bio'        => "Marcus leads Halveron's consulting practice and its work with banks, building societies and outsourcers. He trained as an industrial engineer, then spent twelve years in a national retail bank, latterly as operations director for mortgages and savings. Since joining Halveron in 2015 he has led more than forty operating model and process engagements. Marcus holds an MEng from the University of Birmingham and an MBA from the University of Warwick. At weekends he coaches an under-twelves football team in Oxford.",
			'email'      => 'marcus.adeyemi@halveron.junaid.guru',
			'first_name' => 'Marcus',
			'group'      => 'management',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'Show me the work as it really happens, then we can talk about the plan.',
			'role'       => 'Director of Consulting',
		],
		'title' => 'Marcus Adeyemi',
	],
	'anjali-mehta'     => [
		'meta'  => [
			'bio'        => 'Anjali leads our customer-experience practice and organises the Future of Service Conference. She spent ten years in customer insight and service design, first at a large energy supplier and then at a travel company, where she led a redesign of refunds and disruption handling. She joined Halveron in 2017 and works mainly with insurers, utilities and travel operators. Anjali has a BSc in Psychology from the University of Manchester. She is a keen street photographer and exhibits with a local camera club.',
			'email'      => 'anjali.mehta@halveron.junaid.guru',
			'first_name' => 'Anjali',
			'group'      => 'management',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'I listen to a hundred real customer calls before I draw a single journey map.',
			'role'       => 'Head of Customer Experience',
		],
		'title' => 'Anjali Mehta',
	],
	'tom-whitlock'     => [
		'meta'  => [
			'bio'        => "Tom leads Halveron's training, from the accredited open courses to custom programmes and coaching. He began his career as a secondary-school history teacher, then ran learning and development for a large business-services outsourcer before joining Halveron in 2014. He designed our Lean Practitioner course and still teaches on it most months. Tom read History at Durham University and took his PGCE at the University of Exeter. He walks a long-distance footpath every summer and is slowly working through them all.",
			'email'      => 'tom.whitlock@halveron.junaid.guru',
			'first_name' => 'Tom',
			'group'      => 'management',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'People learn most when the problem on the table is their own.',
			'role'       => 'Head of Learning and Development',
		],
		'title' => 'Tom Whitlock',
	],
	'grace-okonkwo'    => [
		'meta'  => [
			'bio'        => 'Grace leads our digital work, helping clients redesign processes and services before they invest in technology. She started as a software engineer, then became a product manager at a digital agency and later at a large general insurer, where she led its online claims service. She joined Halveron in 2020. Grace holds a BSc in Computer Science from the University of Leeds and is a qualified agile practitioner. Outside work she plays the cello in a community orchestra.',
			'email'      => 'grace.okonkwo@halveron.junaid.guru',
			'first_name' => 'Grace',
			'group'      => 'management',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'I would rather test a rough prototype on Tuesday than polish a specification for a month.',
			'role'       => 'Principal Consultant, Digital',
		],
		'title' => 'Grace Okonkwo',
	],
	'daniel-fry'       => [
		'meta'  => [
			'bio'        => 'Daniel leads our work with councils, health providers and other public bodies, and convenes the Lean Service Forum. He spent fifteen years in local government, latterly as head of customer services for a unitary authority, before joining Halveron in 2016. He set up our service academy for public-sector leaders. Daniel read Politics at the University of York and holds an MSc in Public Policy from the London School of Economics. He is a governor at his local primary school.',
			'email'      => 'daniel.fry@halveron.junaid.guru',
			'first_name' => 'Daniel',
			'group'      => 'management',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => "I measure success by what the council's own team can do once we have gone.",
			'role'       => 'Principal Consultant, Public Sector',
		],
		'title' => 'Daniel Fry',
	],
	'margaret-ellison' => [
		'meta'  => [
			'bio'        => "Margaret is Professor of Service Operations at Brackenfield Business School, where she leads a research group studying demand, capacity and productivity in service organisations. Her research on failure demand in public services informs our methods, and she reviews the content of our accredited courses. Before her academic career she worked in operations planning for a large logistics company. Margaret holds a PhD from the University of Cambridge. She sings with a chamber choir and has walked the length of Hadrian's Wall twice.",
			'email'      => 'margaret.ellison@halveron.junaid.guru',
			'first_name' => 'Margaret',
			'group'      => 'advisory',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'Good research should change what a manager does on Monday morning, or it has not finished.',
			'role'       => 'Adviser, Service Operations Research',
		],
		'title' => 'Professor Margaret Ellison',
	],
	'helen-ashby'      => [
		'meta'  => [
			'bio'        => 'Helen spent thirty years in retail financial services, the last eight as chief operating officer of a large UK building society. She now holds non-executive roles on the boards of a mutual insurer and a consumer charity, and advises Halveron on banking and insurance work and on regulatory change. Helen read Mathematics at the University of Oxford and is a Fellow of a professional banking institute. She sails a small wooden dinghy on the south coast whenever the weather allows.',
			'email'      => 'helen.ashby@halveron.junaid.guru',
			'first_name' => 'Helen',
			'group'      => 'advisory',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => 'I keep asking the awkward question until the answer would satisfy a customer and a regulator.',
			'role'       => 'Adviser, Financial Services',
		],
		'title' => 'Helen Ashby',
	],
	'rafael-moreno'    => [
		'meta'  => [
			'bio'        => "Rafael was chief technology officer of a European travel group for seven years, leading its move to online booking, self-service changes and automated disruption handling. Before that he held engineering and operations roles at a telecoms provider in Madrid and London. He now advises several scale-up businesses and helps Halveron's digital team test new tools and approaches. Rafael holds an MSc in Computing from Imperial College London. He is a committed cyclist and rides the length of Spain every spring.",
			'email'      => 'rafael.moreno@halveron.junaid.guru',
			'first_name' => 'Rafael',
			'group'      => 'advisory',
			'phone'      => '+44 (0)1632 960410',
			'phone_href' => '+441632960410',
			'quote'      => "Technology should remove work from the system, not move it to someone else's desk.",
			'role'       => 'Adviser, Digital Operations',
		],
		'title' => 'Rafael Moreno',
	],
];

$person_ids     = [];
$created_people = 0;
$position       = 0;

foreach ( $people as $slug => $person ) {
	[ $person_ids[ $slug ], $created ] = halveron_seed_post( 'person', $slug, [ 'menu_order' => ++$position, 'post_title' => $person['title'] ], $person['meta'], $img[ "person-{$slug}.jpg" ] );
	$created_people += $created;
}

echo "people: {$created_people} created, " . count( $person_ids ) . " total\n";

$organisations = [
	'harrowden-building-society'   => [
		'file'  => 'harrowden-building-society.svg',
		'meta'  => [
			'descriptor' => 'A mutual building society with 40 branches across the East Midlands and around 600,000 members.',
			'kind'       => 'client',
		],
		'title' => 'Harrowden Building Society',
	],
	'lindmere-mutual'              => [
		'file'  => 'lindmere-mutual.svg',
		'meta'  => [
			'descriptor' => 'A mutual insurer offering home, motor and travel cover to about a million policyholders.',
			'kind'       => 'client',
		],
		'title' => 'Lindmere Mutual',
	],
	'wendle-water'                 => [
		'file'  => 'wendle-water.svg',
		'meta'  => [
			'descriptor' => 'A regional water and sewerage company serving 1.8 million people across rural and coastal England.',
			'kind'       => 'client',
		],
		'title' => 'Wendle Water',
	],
	'corvale-railways'             => [
		'file'  => 'corvale-railways.svg',
		'meta'  => [
			'descriptor' => 'A train operator running commuter and regional services into two large cities.',
			'kind'       => 'client',
		],
		'title' => 'Corvale Railways',
	],
	'hollinbrook-university'       => [
		'file'  => 'hollinbrook-university.svg',
		'meta'  => [
			'descriptor' => 'A campus university of 22,000 students with a strong record in engineering and health sciences.',
			'kind'       => 'client',
		],
		'title' => 'Hollinbrook University',
	],
	'elmshire-county-council'      => [
		'file'  => 'elmshire-county-council.svg',
		'meta'  => [
			'descriptor' => 'A large shire county council responsible for social care, highways, schools and libraries.',
			'kind'       => 'client',
		],
		'title' => 'Elmshire County Council',
	],
	'wealdmoor-health'             => [
		'file'  => 'wealdmoor-health.svg',
		'meta'  => [
			'descriptor' => 'A partnership of community health and hospital services covering a mixed urban and rural area.',
			'kind'       => 'client',
		],
		'title' => 'Wealdmoor Health Partnership',
	],
	'orrin-facilities'             => [
		'file'  => 'orrin-facilities.svg',
		'meta'  => [
			'descriptor' => 'A facilities and business-services outsourcer working for public and private clients across the UK.',
			'kind'       => 'client',
		],
		'title' => 'Orrin Facilities Group',
	],
	'service-operations-institute' => [
		'file'  => 'service-operations-institute.svg',
		'meta'  => [
			'descriptor' => 'A professional body for people who manage and improve service operations, setting standards for practitioner qualifications.',
			'kind'       => 'accrediting',
			'role'       => 'Accredits the Lean Practitioner course',
		],
		'title' => 'Service Operations Institute',
	],
	'centre-for-customer-practice' => [
		'file'  => 'centre-for-customer-practice.svg',
		'meta'  => [
			'descriptor' => 'An independent membership body that promotes good practice in customer service and accredits service training.',
			'kind'       => 'accrediting',
			'role'       => 'Accredits the Service Excellence course',
		],
		'title' => 'Centre for Customer Practice',
	],
	'brackenfield-business-school' => [
		'file'  => 'brackenfield-business-school.svg',
		'meta'  => [
			'descriptor' => 'A business school with a research group in service operations; it reviews our course content and co-authors research.',
			'kind'       => 'accrediting',
			'role'       => 'Academic partner for course design and research',
		],
		'title' => 'Brackenfield Business School',
	],
	'coaching-standards-council'   => [
		'file'  => 'coaching-standards-council.svg',
		'meta'  => [
			'descriptor' => 'A not-for-profit council that publishes standards and a code of practice for workplace coaches.',
			'kind'       => 'accrediting',
			'role'       => 'Accredits our operational coaching course',
		],
		'title' => 'Coaching Standards Council',
	],
	'quaystone-analytics'          => [
		'file'  => 'quaystone-analytics.svg',
		'meta'  => [
			'descriptor' => 'Demand and workforce analytics that show what customers ask for, when, and how often they have to ask again.',
			'kind'       => 'technology',
		],
		'title' => 'Quaystone Analytics',
	],
	'tidewell-contact-systems'     => [
		'file'  => 'tidewell-contact-systems.svg',
		'meta'  => [
			'descriptor' => 'Cloud contact-centre software that brings phone, chat, email and messaging into one view of each customer.',
			'kind'       => 'technology',
		],
		'title' => 'Tidewell Contact Systems',
	],
	'loomwork-automation'          => [
		'file'  => 'loomwork-automation.svg',
		'meta'  => [
			'descriptor' => 'A low-code workflow platform for routing, tracking and automating casework without a long build.',
			'kind'       => 'technology',
		],
		'title' => 'Loomwork Automation',
	],
	'ledgerline-knowledge'         => [
		'file'  => 'ledgerline-knowledge.svg',
		'meta'  => [
			'descriptor' => 'Knowledge-management tools that give customers and frontline staff the same clear answer to the same question.',
			'kind'       => 'technology',
		],
		'title' => 'Ledgerline Knowledge',
	],
	'vellmore-building-society'    => [
		'file'  => 'member-vellmore-building-society.svg',
		'meta'  => [
			'descriptor' => 'A mutual building society with 46 branches across the West of England.',
			'kind'       => 'forum_member',
		],
		'title' => 'Vellmore Building Society',
	],
	'calderbrook-water'            => [
		'file'  => 'member-calderbrook-water.svg',
		'meta'  => [ 'descriptor' => 'A regional water and wastewater company in the north of England.', 'kind' => 'forum_member' ],
		'title' => 'Calderbrook Water',
	],
	'northwyn-rail'                => [
		'file'  => 'member-northwyn-rail.svg',
		'meta'  => [
			'descriptor' => 'A regional passenger rail operator running services across the north-west.',
			'kind'       => 'forum_member',
		],
		'title' => 'Northwyn Rail',
	],
	'ostrey-assurance'             => [
		'file'  => 'member-ostrey-assurance.svg',
		'meta'  => [
			'descriptor' => 'A specialist insurer of homes, small businesses and classic vehicles.',
			'kind'       => 'forum_member',
		],
		'title' => 'Ostrey Assurance',
	],
	'pellingford-homes'            => [
		'file'  => 'member-pellingford-homes.svg',
		'meta'  => [ 'descriptor' => 'A housing association managing 22,000 homes in the Midlands.', 'kind' => 'forum_member' ],
		'title' => 'Pellingford Homes',
	],
	'ashmere-community-health'     => [
		'file'  => 'member-ashmere-community-health.svg',
		'meta'  => [
			'descriptor' => 'A community health provider running district nursing and therapy services.',
			'kind'       => 'forum_member',
		],
		'title' => 'Ashmere Community Health',
	],
];

$allow_svg = fn( $mimes ) => $mimes + [ 'svg' => 'image/svg+xml' ];
add_filter( 'upload_mimes', $allow_svg );

$created_logos = 0;

foreach ( $organisations as $organisation ) {
	[ $img[ $organisation['file'] ], $created ] = halveron_seed_attachment( $organisation['file'], $organisation['title'] );
	$created_logos += $created;
}

remove_filter( 'upload_mimes', $allow_svg );

$organisation_ids      = [];
$created_organisations = 0;
$position              = 0;

foreach ( $organisations as $slug => $organisation ) {
	[ $organisation_ids[ $slug ], $created ] = halveron_seed_post( 'organisation', $slug, [ 'menu_order' => ++$position, 'post_title' => $organisation['title'] ], $organisation['meta'] + [ 'logo' => $img[ $organisation['file'] ] ] );
	$created_organisations += $created;
}

echo "organisations: {$created_organisations} created, " . count( $organisation_ids ) . " total, {$created_logos} logos created\n";

$capabilities = [
	'business-transformation'      => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => 'A short diagnostic of demand, capacity, cost and the change already under way. We talk to frontline staff as well as directors, because the people closest to the work usually know where it breaks.',
					'title' => 'See it clearly',
				],
				[
					'text'  => 'We help the executive team settle on four or five measurable outcomes and the trade-offs they are willing to make. Every project in the programme must then earn its place against them, and those that cannot are stopped or merged.',
					'title' => 'Agree the outcomes',
				],
				[
					'text'  => 'Work is grouped into short stages with their own benefits and decision points. If a stage does not deliver what it promised, you learn early and cheaply, and adjust the next one before committing more money.',
					'title' => 'Deliver in stages',
				],
				[
					'text'  => 'From the first week, your managers work beside ours. By the final stage they lead the work, and we provide coaching, challenge and a second opinion rather than extra people on the payroll.',
					'title' => 'Hand over the reins',
				],
			],
			'body'              => <<<'HTML'
				<p>Most transformation programmes begin with a target operating model and a business case. Fewer begin with an honest account of how the organisation works today. We start there: how demand arrives, where work queues, which decisions are escalated and why, and how much change is already in flight. In a typical six-to-eight-week diagnostic we find that a quarter or more of current projects overlap, compete for the same people or no longer serve the strategy.</p>
				<p>With that picture in hand, we help the executive team agree a small number of outcomes, measured from the customer's side as well as the finance director's. We then sequence the work into stages of three to six months, each with its own benefits case, so the programme funds itself as it goes rather than asking the board for faith. A lean programme office, usually staffed by your own people, tracks benefits rather than activity.</p>
				<p>Our role changes as the programme matures. Early on we lead the design work and run the first stage alongside your teams. Later we coach your leaders to run each stage themselves, and we step back as they step forward.</p>
				<p>Results depend on the starting point and the ambition, but clients typically release between 15 and 25 per cent of operating cost over two to three years while customer satisfaction holds or improves. Just as valuable, they finish with a leadership team that knows how to run the next change without us.</p>
				HTML,
			'contact'           => $person_ids['ruth-calloway'],
			'image'             => $img['cap-business-transformation.jpg'],
			'intro'             => 'Large change programmes fail less often for lack of ambition than for lack of grip. We help leadership teams set a clear destination, sequence the work into stages that pay back early, and build the governance that keeps people, budgets and benefits honest throughout.',
			'meta_description'  => 'Halveron helps leadership teams plan, govern and deliver transformation in stages that pay back early, with someone owning every benefit.',
			'quote'             => 'Halveron helped us stop running forty projects and start running one programme. For the first time the board could see what each stage would deliver, and when.',
			'quote_attribution' => 'Chief Operating Officer, a UK building society',
			'summary'           => 'Transformation succeeds when the destination is clear, each stage pays back early and someone owns every benefit. We help leadership teams plan, govern and deliver change that customers and staff notice for the right reasons.',
			'tab_button'        => 'How we deliver business transformation',
			'wheel_label'       => "Business\ntransformation",
		],
		'related' => [ 'future-of-service-2026', 'regulation-work-for-you', 'change-delivery-council' ],
		'title'   => 'Business transformation',
	],
	'operations-strategy'          => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => 'We measure what customers ask for, through which channels and how often they have to ask twice. Demand is the starting point for every later decision about capacity, sites and cost.',
					'title' => 'Understand the demand',
				],
				[
					'text'  => 'We set out the realistic options for sites, sourcing, channels and automation, with their costs, risks and effect on customers, and help your leadership team choose. A strategy without choices is a wish list.',
					'title' => 'Make the choices',
				],
				[
					'text'  => 'Each choice becomes a costed plan with owners, milestones and the measures that will show whether it is working. We test the plan with the managers who will deliver it before it goes to the board.',
					'title' => 'Plan the move',
				],
				[
					'text'  => 'We support implementation through the difficult middle months, adjusting the plan as evidence comes in, reporting progress to the board and building the routines your managers will use to keep the operation on course.',
					'title' => 'Make it stick',
				],
			],
			'body'              => <<<'HTML'
				<p>Many organisations have a corporate strategy and an operations budget, with very little joining them. Decisions about where work is done, by whom and through which channels get made one at a time, often under pressure, and the operation slowly becomes a collection of exceptions. We help you put the joining piece back.</p>
				<p>We start by understanding demand: how much arrives, through which channels, how predictable it is and how much of it you created yourselves through errors and delays. From there we look at capacity and cost, the balance of in-house and outsourced work, the role of each site, and how far self-service and automation can sensibly go. The output is a short strategy, usually no more than a dozen pages, with clear choices and the reasons for them.</p>
				<p>Implementation is where most strategies stall, so we stay for it. Typical work includes:</p>
				<ul>
				<li>planning and running a site consolidation or a move to hybrid working;</li>
				<li>re-letting or bringing back in-house an outsourced service;</li>
				<li>building workforce plans that match staffing to demand by the half hour;</li>
				<li>setting a small number of operational measures that the board and the front line both recognise.</li>
				</ul>
				<p>Clients typically see unit costs fall by 10 to 20 per cent within two years of implementation, alongside fewer handoffs and steadier service levels. The exact figures depend on where you start, and we will tell you plainly at the diagnostic stage what we think is achievable.</p>
				HTML,
			'contact'           => $person_ids['marcus-adeyemi'],
			'image'             => $img['cap-operations-strategy.jpg'],
			'intro'             => "An operations strategy should tell every manager what to do on Monday morning. We connect your organisation's goals to choices about capacity, locations, channels and sourcing, then help you put those choices into practice with measures your teams understand.",
			'meta_description'  => 'Halveron connects your operations strategy to choices about capacity, channels, locations and sourcing, then helps you put those choices into practice.',
			'quote'             => 'We had a lot of good ideas and no way to choose between them. Halveron gave us a strategy short enough to remember and clear enough to act on.',
			'quote_attribution' => 'Operations Director, a national insurance provider',
			'summary'           => 'Your operations strategy decides much of what you spend and how customers feel about you. We connect it to choices about capacity, channels, locations and sourcing, then help you put those choices into practice.',
			'tab_button'        => 'How we deliver operations strategy',
			'wheel_label'       => "Operations\nstrategy and\nimplementation",
		],
		'related' => [ 'start-with-demand', 'contact-centre-demand-report', 'regulation-work-for-you' ],
		'title'   => 'Operations strategy and implementation',
	],
	'operating-model-design'       => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => 'We name the few outcomes that matter most to your customers and follow each one through the organisation. The current model, and every option for the new one, is judged by how well it delivers them.',
					'title' => 'Start with the customer',
				],
				[
					'text'  => 'Services and value streams come first, then roles and decision rights, then processes, sites, systems and data. Each layer is agreed with your leadership team before the next one is built on it.',
					'title' => 'Design in layers',
				],
				[
					'text'  => 'We walk real cases through the new design with the people who will run it, from the simple to the awkward. Problems found in a workshop cost a fraction of those found after go-live.',
					'title' => 'Test before building',
				],
				[
					'text'  => 'The transition is planned in phases that protect service levels. We track the benefits of each phase and adjust the next one in the light of what we and your teams learn.',
					'title' => 'Move in phases',
				],
			],
			'body'              => <<<'HTML'
				<p>An operating model is easy to draw and hard to build. Organisation charts and capability maps look tidy on a slide, but the real model is found in how work flows between teams, who can make which decisions, and which systems people actually use. We design for that reality.</p>
				<p>Our method follows the customer. We identify the handful of things customers most need from you and trace how each one is delivered today, team by team and system by system. That shows where the current model adds cost without adding value: duplicated teams, unclear ownership, specialists doing routine work, or a central function that every request must pass through twice.</p>
				<p>We then design the future model in layers. First the services and the value streams that deliver them; then the organisation, roles and decision rights; then the processes, locations, technology and data that support them. At each layer we test the design against real cases with the people who will work in it. This is where most of the hidden problems appear, and it costs far less to find them on paper than after a restructure.</p>
				<p>A typical design phase takes ten to fourteen weeks, followed by a phased transition. Clients usually see 15 to 30 per cent less effort spent on internal handoffs and rework, and clearer accountability for every customer outcome. Because the design is tested before it is built, transition is quicker and calmer than people expect.</p>
				HTML,
			'contact'           => $person_ids['marcus-adeyemi'],
			'image'             => $img['cap-operating-model-design.jpg'],
			'intro'             => 'Our operating model method has been refined over more than sixty engagements across the service sector. We start with what customers need from you, then design the structures, roles, processes and systems to deliver it, and test the design before anyone moves desk.',
			'meta_description'  => 'Halveron designs structures, roles, processes and systems around what customers need, and tests the design before anyone moves desk.',
			'quote'             => 'The design work was the first time our finance, operations and technology teams had looked at the same picture. That alone saved us months.',
			'quote_attribution' => 'Chief Customer Officer, a regional water company',
			'summary'           => 'An operating model is how your organisation turns strategy into service every day. We design structures, roles, processes and systems around what customers need, and test the design before anyone moves desk.',
			'tab_button'        => 'How we deliver operating model design',
			'wheel_label'       => "Operating\nmodel design",
		],
		'related' => [ 'regulation-work-for-you', 'back-office-productivity-bank', 'start-with-demand' ],
		'title'   => 'Operating model design',
	],
	'organisational-effectiveness' => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => 'We measure spans, layers and decision paths, review how information flows, and observe how managers really spend their week. Opinions about the organisation are useful; facts about it are better.',
					'title' => 'Gather the evidence',
				],
				[
					'text'  => 'We help leaders agree which decisions sit where, then write them down in a form that people actually use. Most routine escalations disappear once ownership is clear, and senior time is freed for the decisions that need it.',
					'title' => 'Clarify who decides',
				],
				[
					'text'  => 'Short daily and weekly reviews let teams see their own performance and solve problems at source. We help managers run the first few, then step back once the habit has formed.',
					'title' => 'Build the routines',
				],
				[
					'text'  => 'First-line managers make or break any organisation. We coach them in the new routines and build a development path that keeps improving their skills long after our work has finished.',
					'title' => 'Develop the managers',
				],
			],
			'body'              => <<<'HTML'
				<p>When an operation underperforms, the first instinct is often to look at the people. In our experience the cause is more often the organisation around them: too many layers between the customer and a decision, managers with teams too large to know or too small to justify, and meetings that report on work rather than improve it.</p>
				<p>We start with evidence. We review spans of control and management layers, map who decides what, and observe how managers actually spend their week. We also run short, structured conversations with staff at every level about what helps and what gets in the way. The picture is usually consistent: decisions travel too far, information arrives late, and managers spend more time in reporting than with their teams.</p>
				<p>From there we work with your leaders on practical changes:</p>
				<ul>
				<li>simpler structures with sensible spans and fewer layers;</li>
				<li>clear decision rights, written down and used;</li>
				<li>daily and weekly routines that let teams see their performance and fix problems early;</li>
				<li>role profiles and development plans for first-line managers, who carry most of the load.</li>
				</ul>
				<p>Typical results include one fewer management layer, a 20 to 40 per cent cut in time spent on internal reporting, and measurable gains in staff engagement within a year. We are careful to protect the expertise and goodwill an organisation has built; the aim is a better organisation, not simply a smaller one.</p>
				HTML,
			'contact'           => $person_ids['tom-whitlock'],
			'image'             => $img['cap-organisational-effectiveness.jpg'],
			'intro'             => 'Good people in a poorly designed organisation spend their days working around it. We look at spans and layers, decision rights, handoffs and management routines, and help you build an organisation where accountability is clear and good work is easier to do.',
			'meta_description'  => 'Halveron reviews structure, decision rights and management routines, and helps you build an organisation where good work is easier to do.',
			'quote'             => 'Our team leaders now spend most of the morning with their teams instead of in meetings. The difference in the call centre was visible within a month.',
			'quote_attribution' => 'Head of Customer Operations, a UK energy supplier',
			'summary'           => 'Good people in a poorly designed organisation spend their days working around it. We look at structure, decision rights and management routines, and help you make good work easier to do.',
			'tab_button'        => 'How we deliver organisational effectiveness',
			'wheel_label'       => "Organisational\neffectiveness",
		],
		'related' => [ 'public-sector-service-academy', 'change-delivery-council', 'coaching-hybrid-teams' ],
		'title'   => 'Organisational effectiveness',
	],
	'customer-experience'          => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => 'We gather evidence from calls, complaints, chats, surveys and case files, and from customers themselves. Their words, not our assumptions or the latest survey score, set the priorities for the work.',
					'title' => 'Listen to customers',
				],
				[
					'text'  => 'We map the journeys that matter most across every channel, showing contacts, waits and handoffs. The maps make failure demand visible to everyone who sees them, from the board to the front line.',
					'title' => 'Map real journeys',
				],
				[
					'text'  => 'Your teams and ours redesign the broken steps, then prototype the changes quickly: a new letter, a clearer web page, a call guide or a revised policy that frontline staff can apply.',
					'title' => 'Redesign and prototype',
				],
				[
					'text'  => 'Each change is tested with real customers and measured before it is scaled up. What works is rolled out across the operation and every channel; what does not is changed or dropped.',
					'title' => 'Test and roll out',
				],
			],
			'body'              => <<<'HTML'
				<p>Most organisations measure each channel on its own terms: call handling time, web visits, app ratings, letters sent. Customers experience something else entirely: one attempt to get something done, often spread over several channels and several days. When those two views do not meet, cost rises and goodwill falls.</p>
				<p>We start with the customer's view. We listen to calls, read complaints and web chats, follow cases through back-office systems and, where we can, talk to customers directly. From that evidence we map the journeys that matter most, showing every contact, wait and handoff. The maps almost always reveal failure demand: customers contacting you again because something did not happen, was not clear or was done wrongly the first time. In a typical service operation this accounts for between a third and a half of all contact.</p>
				<p>We then work with your teams to redesign the journeys. That can mean clearer letters and web pages, better information for frontline staff, changes to policy, or moving a step to where it can be done right first time. We prototype changes quickly and test them with real customers before they are rolled out.</p>
				<p>Clients typically see contact volumes fall by 15 to 30 per cent on redesigned journeys, with fewer complaints and higher satisfaction scores. Because the work removes demand rather than processing it faster, the savings tend to last.</p>
				HTML,
			'contact'           => $person_ids['anjali-mehta'],
			'image'             => $img['cap-customer-experience.jpg'],
			'intro'             => 'Customers do not think in channels. They want an answer, wherever they ask. We map real journeys across phone, web, app, letter and branch, find where they break, and redesign them so each contact moves the customer forward rather than back.',
			'meta_description'  => 'Halveron maps real customer journeys across phone, web, app and letter, finds where they break and redesigns them so each contact moves forward.',
			'quote'             => 'We thought we had a contact-centre problem. Halveron showed us we had a letters problem, a website problem and a policy problem. Fixing those fixed the calls.',
			'quote_attribution' => 'Director of Customer Service, a mutual insurer',
			'summary'           => 'Customers move between phone, web, app and letter without a second thought. We map their real journeys, find where they break and redesign them so each contact moves the customer forward.',
			'tab_button'        => 'How we deliver customer experience',
			'wheel_label'       => "Multichannel\ncustomer\nexperience",
		],
		'related' => [ 'future-of-service-2026', 'customer-journey-insurer', 'contact-centre-demand-report' ],
		'title'   => 'Multichannel customer experience',
	],
	'process-improvement'          => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => "We agree what the process is for from the customer's point of view. Every step in the current process is then judged against that purpose rather than against internal habit or history.",
					'title' => 'Start with purpose',
				],
				[
					'text'  => 'We measure real end-to-end times, loops and waits on live cases. The data usually surprises everyone, including the people who designed the process and the managers who report on it each month.',
					'title' => 'Study the flow',
				],
				[
					'text'  => 'The people who do the work lead the redesign, with our coaching. They remove steps that add no value, simplify the rest and move expertise to the point where it prevents errors.',
					'title' => 'Redesign together',
				],
				[
					'text'  => 'Changes are tried on live work in one team for a few weeks, measured, refined and then extended. Visible results build confidence, and the method spreads from team to team with them.',
					'title' => 'Test, then extend',
				],
			],
			'body'              => <<<'HTML'
				<p>Service processes grow by accretion. A check is added after one mistake, a form after one complaint, an approval after one audit finding. Each makes sense on its own; together they slow everything down and add cost the customer never sees the value of.</p>
				<p>We take a systems view. Rather than starting with process maps, we start with demand and purpose: what customers ask for, and what the process is for. We then study how the work flows today, measuring how long cases really take from end to end, how often they loop back, and where they wait. Our consultants work alongside your staff in the operation itself, not in a project room down the corridor.</p>
				<p>The redesign is led by the people who do the work. We help them strip out steps that add no value, move expertise to the front of the process, and build simple measures that show flow rather than activity. Changes are tested on live work in a small area first, then extended once the results are clear.</p>
				<p>Typical outcomes include end-to-end times cut by 40 to 60 per cent, productivity gains of 15 to 30 per cent and a noticeable fall in complaints. Just as important, teams finish with the skills to keep improving. We often train a group of internal practitioners through our accredited Lean Practitioner course during the work, so the method stays when we go.</p>
				HTML,
			'contact'           => $person_ids['daniel-fry'],
			'image'             => $img['cap-process-improvement.jpg'],
			'intro'             => 'We use lean and systems thinking to take waste out of service processes: the rework, the chasing, the duplicate checks. Your own teams do the analysis alongside us, so they understand the new process and can keep improving it once we leave.',
			'meta_description'  => 'Halveron uses lean and systems thinking, with your own teams, to remove rework, chasing and duplicate checks from service processes.',
			'quote'             => 'The team found more waste in a fortnight with Halveron than our previous programme found in a year, because this time they were looking for it themselves.',
			'quote_attribution' => 'Head of Operations, a regional building society',
			'summary'           => 'Most service processes carry work that customers never asked for: rework, chasing and duplicate checks. We use lean and systems thinking, with your own teams, to remove it and keep it out.',
			'tab_button'        => 'How we deliver process improvement',
			'wheel_label'       => "Process\nimprovement",
		],
		'related' => [ 'complaints-handling-utility', 'failure-demand-explained', 'back-office-productivity-bank' ],
		'title'   => 'Process improvement',
	],
	'digital-at-pace'              => [
		'meta'    => [
			'approach'            => [
				[
					'text'  => 'We gather evidence on what customers ask for and where they get stuck, from contact data, web analytics and conversations. It shows which journeys to redesign first and what good would look like for customers.',
					'title' => 'Insight',
				],
				[
					'text'  => 'We redesign the journey and the process behind it before choosing any technology. A simpler process is cheaper to build, easier to change and kinder to customers and staff alike.',
					'title' => 'Design',
				],
				[
					'text'  => 'We use low-code workflow and automation tools to route, track and complete work. Prototypes reach real users in days, so we learn from real evidence rather than from long specifications written in advance.',
					'title' => 'Workflow',
				],
				[
					'text'  => 'We design the screens, messages and letters customers see, and test them with real people. Small changes in wording and layout often move more customers to self-service than new features do.',
					'title' => 'Experience',
				],
			],
			'body'                => <<<'HTML'
				<p>Digital programmes in service organisations often follow a familiar pattern. A platform is chosen, the existing process is configured into it, and eighteen months later the organisation has a modern system running an old way of working. Customers find a new front door that leads to the same queue.</p>
				<p>We work in a different order. We begin with demand and the journey, using the same evidence as our customer-experience work, and redesign the process so that it is simple enough to automate. Only then do we look at technology. In many cases the answer is modest: a better online form, a workflow tool that routes cases to the right person, or a knowledge base that gives staff and customers the same answer.</p>
				<p>Speed comes from small, tested steps. We prototype a new service in days, test it with real customers and staff, and refine it before anything is built at scale. Our consultants work with your own technology teams and, where it helps, with a small network of specialist partners in analytics, contact-centre systems, workflow automation and knowledge management. We have no commercial ties that favour one product over another.</p>
				<p>Typical results include a working service in front of customers within eight to twelve weeks, a rise of 20 to 40 percentage points in the share of requests completed without staff involvement on redesigned journeys, and lower build costs because the process is simpler. Each engagement starts with a clear view of what is realistic for your systems and budget.</p>
				HTML,
			'contact'             => $person_ids['grace-okonkwo'],
			'diagram_description' => 'Six steps arranged around customer demand: understand demand, redesign the work, prototype in days, test with customers, build and integrate, and measure and improve, after which the cycle begins again.',
			'diagram_hub'         => "Customer\ndemand",
			'diagram_steps'       => [
				[ 'label' => 'Understand demand' ],
				[ 'label' => 'Redesign the work' ],
				[ 'label' => 'Prototype in days' ],
				[ 'label' => 'Test with customers' ],
				[ 'label' => 'Build and integrate' ],
				[ 'label' => 'Measure and improve' ],
			],
			'diagram_title'       => 'How we deliver digital at pace',
			'image'               => $img['cap-digital-at-pace.jpg'],
			'intro'               => 'New technology rarely fixes a broken process. It usually makes it faster at going wrong. We redesign the work first, then prototype and test digital services with real customers in weeks, working with your technology teams and selected partners.',
			'meta_description'    => 'Halveron redesigns the work first, then prototypes and tests digital services with real customers in weeks, alongside your technology teams.',
			'partners'            => halveron_seed_pick( $organisation_ids, [ 'quaystone-analytics', 'tidewell-contact-systems', 'loomwork-automation', 'ledgerline-knowledge' ] ),
			'partners_heading'    => 'Our partner network',
			'partners_intro'      => 'We work with a small group of specialist technology firms. We choose them for the job, and we take no commission on what you buy.',
			'quote'               => 'They would not let us buy anything until we had fixed the process. It was frustrating for about a month and then it saved us a great deal of money.',
			'quote_attribution'   => 'Chief Digital Officer, a UK rail operator',
			'summary'             => 'We design digital services around redesigned work, not old processes. Working with your technology teams and selected partners, we prototype and test with real customers in weeks, then help you build what works.',
			'tab_button'          => 'How we deliver digital at pace',
			'wheel_label'         => "Digital\nat pace",
		],
		'related' => [ 'technology-in-travel', 'future-of-service-2026', 'customer-journey-insurer' ],
		'title'   => 'Digital at pace',
	],
	'learning-and-development'     => [
		'meta'    => [
			'approach'          => [
				[
					'text'  => 'Every delegate works on a live challenge from their own organisation. The course is the place to practise each tool; the workplace is where the learning counts and where its results are measured.',
					'title' => 'Bring a real problem',
				],
				[
					'text'  => 'Our tutors are consultants who use these methods with clients every week. They teach from current examples, share what has failed as well as what has worked, and answer difficult questions honestly.',
					'title' => 'Learn from practitioners',
				],
				[
					'text'  => 'Practitioner courses are externally accredited by independent bodies and assessed through a workplace project. Delegates earn a recognised qualification for improvement work they have actually done in their own organisation.',
					'title' => 'Accredit the skills',
				],
				[
					'text'  => 'We follow up with delegates and sponsors after the course, and offer coaching while projects are under way. Learning that is supported back at work, by managers and peers, is learning that lasts.',
					'title' => 'Coach beyond the course',
				],
			],
			'body'              => <<<'HTML'
				<p>Training that happens in a classroom and stays there is an expensive day out. Our courses are built the other way round: every delegate brings a real problem from their own organisation, applies each tool to it during the course, and leaves with a plan they can put to work the following week.</p>
				<p>We offer three kinds of learning. Open courses, such as Lean Practitioner and Service Excellence, bring together managers from different organisations and sectors, which is often where the most useful ideas come from. Custom programmes are designed for a single organisation, using its own processes, language and data. Coaching on the job supports managers as they lead improvement work, usually alongside one of our consulting engagements.</p>
				<p>Our practitioner courses are accredited by the Service Operations Institute and the Centre for Customer Practice, and our coaching programme follows the standards of the Coaching Standards Council. Our tutors are working consultants, not full-time trainers, so the examples they use are current and the advice is practical.</p>
				<p>Delegates typically complete an improvement project within three months of their course, and many organisations measure a clear return on the training through those projects alone. We follow up with each delegate's sponsor at three and six months to review progress, and our alumni are welcome to join the Lean Service Forum to keep learning from their peers.</p>
				HTML,
			'contact'           => $person_ids['tom-whitlock'],
			'image'             => $img['cap-learning-and-development.jpg'],
			'intro'             => 'Lasting improvement depends on managers who can lead it. Our accredited courses, on-the-job coaching and leadership programmes build practical skills in lean, service design and operational management, tailored to the challenges your teams face every day.',
			'meta_description'  => 'Accredited open courses, custom programmes and on-the-job coaching in lean, service design and operational management from Halveron.',
			'quote'             => 'Our people came back from the course with a project already half done. Six months later two of them were leading improvement work across the whole division.',
			'quote_attribution' => 'Learning and Development Director, a county council',
			'summary'           => 'Lasting improvement depends on people who can lead it. Our accredited open courses, custom programmes and on-the-job coaching build practical skills in lean, service design and operational management, and leave your teams able to lead change themselves.',
			'tab_button'        => 'How we deliver learning and development',
			'wheel_label'       => "Learning and\ndevelopment",
		],
		'related' => [ 'public-sector-service-academy', 'lean-forum-100-members', 'coaching-hybrid-teams' ],
		'title'   => 'Learning and development',
	],
];

$capability_ids       = [];
$created_capabilities = 0;
$position             = 0;

foreach ( $capabilities as $slug => $capability ) {
	[ $capability_ids[ $slug ], $created ] = halveron_seed_post( 'capability', $slug, [ 'menu_order' => ++$position, 'post_title' => $capability['title'] ], $capability['meta'] );
	$created_capabilities += $created;
}

echo "capabilities: {$created_capabilities} created, " . count( $capability_ids ) . " total\n";

$sectors = [
	'banking'          => [
		'case_studies' => [ 'back-office-productivity-bank', 'customer-journey-insurer', 'complaints-handling-utility', 'change-delivery-council' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Retail banking operations carry decades of layered processes. Mortgage applications pass through several teams, account changes still rely on paper in places, and fraud checks added in a hurry sit awkwardly on top of everything else. Meanwhile regulators expect firms to show, with evidence, that customers receive fair outcomes, and branch networks keep shrinking while the customers who rely on them still need help.</p>
				<p>We start by measuring the demand that reaches each part of the operation and how much of it is caused by delays, unclear letters or errors. In a typical retail lender between a quarter and a third of contact is of this kind. We then redesign the processes that generate it, from mortgage offers and account opening to bereavement and complaints, working alongside the teams who run them. Where it helps, we shift routine work to self-service and give specialists more time for vulnerable customers and complex cases.</p>
				<p>Our banking clients include mutuals and regional lenders, where membership and local reputation matter as much as cost. Typical results include back-office productivity gains of 20 to 30 per cent, faster decisions for customers, and evidence of good outcomes that stands up to scrutiny from the board and the regulator. We also train internal improvement teams, so the gains continue after we leave.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'harrowden-building-society', 'lindmere-mutual', 'orrin-facilities' ] ),
			'contact'           => $person_ids['marcus-adeyemi'],
			'form_intro'        => 'Tell us about the challenge facing your bank or building society, and one of our banking team will be in touch.',
			'image'             => $img['sector-banking.jpg'],
			'intro'             => 'Lenders face tighter margins, rising fraud and closer scrutiny of customer outcomes, all while customers move online. We help banks and building societies redesign the operations behind lending, savings and servicing so that they cost less to run and are easier to use.',
			'meta_description'  => 'Halveron helps banks and building societies cut the cost of serving customers without losing the relationships that set them apart.',
			'quote'             => 'They understood that our members join us for the relationship. Every change they proposed made us quicker without making us feel like a call centre.',
			'quote_attribution' => 'Chief Executive, a UK building society',
			'summary'           => 'Banks and building societies are moving customers online while keeping branches and phone lines that people trust. We help them cut the cost of serving customers without losing the relationships that set them apart.',
		],
		'related'      => [ 'regulation-work-for-you', 'start-with-demand', 'contact-centre-demand-report' ],
		'title'        => 'Banking',
	],
	'insurance'        => [
		'case_studies' => [ 'customer-journey-insurer', 'back-office-productivity-bank', 'complaints-handling-utility', 'change-delivery-council' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Insurance operations are under pressure from several directions at once. Pricing rules have changed how renewals work, outcome-based regulation asks insurers to prove that customers are treated fairly, and extreme weather brings sudden surges in claims. Many insurers still run separate systems for each product line, so a customer with home and motor cover may meet two quite different organisations.</p>
				<p>Our work usually starts with the claims journey. We follow real claims from first notification to settlement, measuring every contact, handoff and wait. The pattern is familiar: customers call to chase progress, suppliers wait for authority, and simple claims queue behind complex ones. We help insurers separate claims by complexity, give frontline handlers the authority to settle more on the first call, and rewrite the letters and messages that generate most follow-up contact. Renewals, mid-term changes and complaints get the same treatment.</p>
				<p>We also help insurers plan for peaks. A surge model that links weather forecasts to staffing and supplier capacity can make the difference between a difficult week and a lost quarter. Clients typically see claims cycle times fall by 30 to 50 per cent on redesigned journeys, repeat contact fall by a quarter or more, and a clear improvement in complaint numbers within a year.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'lindmere-mutual', 'harrowden-building-society', 'orrin-facilities' ] ),
			'contact'           => $person_ids['anjali-mehta'],
			'form_intro'        => 'Tell us about the claims, renewals or service challenge you face, and our insurance team will be in touch.',
			'image'             => $img['sector-insurance.jpg'],
			'intro'             => 'A claim is the moment an insurer keeps its promise, and the moment customers remember. We help insurers redesign claims, renewals and servicing so that customers get clear answers quickly and frontline teams spend their time on the cases that need judgement.',
			'meta_description'  => 'Halveron helps general insurers and mutuals make claims, renewals and service journeys clearer, quicker and less costly to run.',
			'quote'             => 'Halveron showed us that most of our claims calls were customers asking where their claim had got to. Once we told them first, the calls stopped.',
			'quote_attribution' => 'Claims Director, a mutual insurer',
			'summary'           => 'Insurers are judged at the moment of a claim. We help general insurers and mutuals make claims, renewals and service journeys clearer, quicker and less costly to run.',
		],
		'related'      => [ 'regulation-work-for-you', 'future-of-service-2026', 'failure-demand-explained' ],
		'title'        => 'Insurance',
	],
	'utilities'        => [
		'case_studies' => [ 'complaints-handling-utility', 'customer-journey-insurer', 'back-office-productivity-bank', 'change-delivery-council' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Utilities serve whole regions, including many customers in difficult circumstances. Regulators set demanding targets for complaints, vulnerable customers and incident response, with financial penalties for missing them. At the same time, ageing networks bring incidents that can generate thousands of calls in an hour, and billing systems built for a simpler era struggle with smart meters, payment plans and changes of occupier.</p>
				<p>We help utilities reduce the demand they create for themselves. Estimated bills, missed appointments and unclear letters drive a large share of contact; in a typical utility, a third or more of calls are failure demand of this kind. We trace each type back to its cause and work with billing, field and customer teams to fix it at source. We also redesign complaints handling so that more cases are resolved at first contact, and help teams identify and support vulnerable customers consistently.</p>
				<p>For incidents, we help design response plans that connect operations, field teams and customer channels, so that customers hear what is happening before they need to ask. Clients typically see complaints fall by 30 to 50 per cent, faster resolution of those that remain, and better scores in the regulator's customer measures. Every engagement is planned around the regulatory calendar, so improvements arrive in time to count.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'wendle-water', 'elmshire-county-council', 'orrin-facilities' ] ),
			'contact'           => $person_ids['ruth-calloway'],
			'form_intro'        => 'Tell us about the customer or operational challenge facing your utility, and our utilities team will be in touch.',
			'image'             => $img['sector-utilities.jpg'],
			'intro'             => 'Utility customers cannot choose to leave a water company, so trust has to be earned in other ways. We help water and energy companies get billing, metering, complaints and incident response right first time, and support customers who need extra help.',
			'meta_description'  => 'Halveron helps water and energy companies get billing, metering, complaints and incident response right the first time.',
			'quote'             => 'We used to measure how quickly we closed complaints. Halveron got us measuring why they arrived, and that is when the numbers started to fall.',
			'quote_attribution' => 'Head of Customer Service, a regional water company',
			'summary'           => 'Water and energy companies serve every household, including the most vulnerable. We help them get billing, metering, complaints and incident response right the first time.',
		],
		'related'      => [ 'failure-demand-explained', 'contact-centre-demand-report', 'regulation-work-for-you' ],
		'title'        => 'Utilities',
	],
	'travel'           => [
		'case_studies' => [ 'customer-journey-insurer', 'complaints-handling-utility', 'back-office-productivity-bank', 'change-delivery-council' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Travel operations swing between quiet weeks and sudden peaks: school holidays, timetable changes, strikes and severe weather. Customers are often on the move, anxious and short of time. When disruption hits, contact can multiply tenfold in a day, and the refunds, rebookings and complaints that follow can take weeks to clear.</p>
				<p>We help operators prepare for both the ordinary day and the very bad one. We model demand across channels and seasons, then design staffing, self-service and escalation plans that flex with it. For disruption, we help connect operational control rooms with customer channels, so that information reaches customers, staff and partners at the same time and in the same words. We also redesign the journeys behind refunds, delay compensation and booking changes, which in many operators generate more contact than any other activity.</p>
				<p>Technology plays a large part in travel, and our digital team helps operators decide where automation and self-service will reduce work, and where they will only move it. Our sector review on technology in travel operations sets out what we have learned. Clients typically see refund and compensation backlogs cleared in weeks rather than months, a 20 to 30 per cent fall in contact during disruption, and more consistent information across channels.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'corvale-railways', 'lindmere-mutual', 'orrin-facilities' ] ),
			'contact'           => $person_ids['grace-okonkwo'],
			'form_intro'        => 'Tell us about the peaks, disruption or service challenge you face, and our travel team will be in touch.',
			'image'             => $img['sector-travel.jpg'],
			'intro'             => 'In travel, the service is tested hardest when things go wrong. We help rail, coach, aviation and holiday operators plan for peaks and disruption, simplify refunds and changes, and keep customers informed in the moments when information matters most.',
			'meta_description'  => 'Halveron helps rail, coach, airline and holiday operators handle peaks and disruption, simplify refunds and keep customers informed.',
			'quote'             => 'When the next storm came, our customers heard about the cancellations from us before they heard about them from the news. That changed the whole tone of the week.',
			'quote_attribution' => 'Customer Experience Director, a UK train operator',
			'summary'           => 'Travel operators live with peaks, disruption and customers on the move. We help rail, coach, airline and holiday businesses handle both the ordinary day and the very bad one.',
		],
		'related'      => [ 'technology-in-travel', 'future-of-service-2026', 'start-with-demand' ],
		'title'        => 'Travel',
	],
	'education'        => [
		'case_studies' => [ 'change-delivery-council', 'back-office-productivity-bank', 'customer-journey-insurer', 'complaints-handling-utility' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Higher and further education institutions face financial pressure on several fronts, with frozen fees, volatile international recruitment and rising costs. Professional services are often organised faculty by faculty, so the same task is done in different ways across one campus, and students are passed between offices to get a simple answer. Peaks such as clearing, enrolment and results day stretch every team at once.</p>
				<p>We help institutions look at professional services as a whole. We map how students and staff actually get things done, from applying and enrolling to asking for support or claiming expenses, and find where the work is duplicated or bounced between teams. We then help design shared service models that keep specialist expertise close to academic departments while handling routine requests consistently, often through a single front door for student enquiries.</p>
				<p>We pay close attention to the people side of change in universities, where consultation, academic governance and long-standing ways of working all matter. Our training team often runs improvement courses for professional services staff alongside the consulting work, so the institution builds its own capability. Clients typically see faster responses to student enquiries, fewer handoffs in core processes, and savings of 10 to 20 per cent in professional services costs, reinvested in teaching and student support.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'hollinbrook-university', 'elmshire-county-council', 'orrin-facilities' ] ),
			'contact'           => $person_ids['tom-whitlock'],
			'form_intro'        => 'Tell us about the challenge facing your university or college, and our education team will be in touch.',
			'image'             => $img['sector-education.jpg'],
			'intro'             => 'Universities run admissions, student support, accommodation, finance and research administration on budgets that keep tightening. We help institutions simplify professional services, plan for the peaks of the academic year, and give students clear answers in one place.',
			'meta_description'  => 'Halveron helps universities and colleges simplify student services, admissions and professional services on tight budgets.',
			'quote'             => 'Halveron understood that a university is not a bank. They worked with our academic colleagues rather than around them, and the changes have lasted.',
			'quote_attribution' => 'Registrar and Chief Operating Officer, a UK university',
			'summary'           => 'Universities and colleges run complex services for students, staff and researchers on tight budgets. We help them simplify student services, admissions and professional services without losing what makes each institution distinctive.',
		],
		'related'      => [ 'public-sector-service-academy', 'coaching-hybrid-teams', 'lean-forum-100-members' ],
		'title'        => 'Education',
	],
	'public-sector'    => [
		'case_studies' => [ 'change-delivery-council', 'complaints-handling-utility', 'customer-journey-insurer', 'back-office-productivity-bank' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Local and central public services face a familiar squeeze. Demand for adult and children's social care keeps rising, budgets do not keep pace, and residents expect the same online convenience they get from their bank. Many authorities have run several rounds of savings already, and the easy options have gone.</p>
				<p>We help public bodies find savings that also improve services. That starts with demand: understanding why residents contact the council, how much of that contact could be prevented, and where cases bounce between departments. We then work with frontline teams to redesign services such as council tax, housing, waste and adult social care assessments, testing changes on live cases before rolling them out. In a typical authority, between a third and a half of contact in high-volume services is avoidable.</p>
				<p>Lasting change in the public sector depends on people inside the organisation, so we always build internal capability alongside the consulting work. Through our public-sector service academy we train managers and improvement leads in the same methods we use, and coach them as they lead projects of their own. Clients typically see avoidable contact fall by 20 to 40 per cent in redesigned services, shorter waiting times for assessments, and a programme that continues under its own steam once our work is done.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'elmshire-county-council', 'wealdmoor-health', 'hollinbrook-university', 'orrin-facilities' ] ),
			'contact'           => $person_ids['daniel-fry'],
			'form_intro'        => 'Tell us about the service or change challenge facing your organisation, and our public sector team will be in touch.',
			'image'             => $img['sector-public-sector.jpg'],
			'intro'             => 'Councils and public bodies are asked to do more each year with less. We help them understand the demand that reaches them, redesign services around residents, and build the internal capability to keep improving long after a programme has closed.',
			'meta_description'  => 'Halveron helps councils and public bodies redesign services around residents and build their own skills to keep improving.',
			'quote'             => 'Other consultants left us a report. Halveron left us a team of our own people who know how to do this, and they are still doing it.',
			'quote_attribution' => 'Director of Transformation, a county council',
			'summary'           => 'Councils and public bodies face rising demand with less money every year. We help them redesign services around residents, build their own improvement skills and deliver change that lasts.',
		],
		'related'      => [ 'public-sector-service-academy', 'start-with-demand', 'lean-forum-100-members' ],
		'title'        => 'Public sector',
	],
	'healthcare'       => [
		'case_studies' => [ 'change-delivery-council', 'complaints-handling-utility', 'customer-journey-insurer', 'back-office-productivity-bank' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Health services are under sustained pressure from long waiting lists, workforce shortages and rising demand from an ageing population. Much of the strain shows up in administration: referrals that arrive incomplete, appointments booked and rebooked, letters that confuse patients, and phone lines that cannot keep up. Every hour spent on avoidable administration is an hour not spent on care.</p>
				<p>We bring the same methods we use in other service operations, adapted carefully for clinical settings. We map patient pathways from referral to discharge, measuring where patients wait, where information is lost and where staff repeat work. We then work with clinicians, administrators and patients to redesign the steps that cause most delay, from referral triage and outpatient booking to patient letters and discharge planning. Changes are tested in one service first, with clinical leads involved at every stage.</p>
				<p>We are careful about what we claim in healthcare. Our role is to improve the administrative and operational processes around care, not clinical decisions themselves. Within that scope, clients typically see a 20 to 30 per cent fall in missed appointments on redesigned pathways, faster referral processing, and fewer calls from patients chasing information. Staff consistently tell us that the greatest benefit is time returned to patient care.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'wealdmoor-health', 'elmshire-county-council', 'orrin-facilities' ] ),
			'contact'           => $person_ids['daniel-fry'],
			'form_intro'        => 'Tell us about the operational challenge facing your health service, and our healthcare team will be in touch.',
			'image'             => $img['sector-healthcare.jpg'],
			'intro'             => 'Clinicians and administrators in health services lose hours each week to processes that patients never see. We help hospital and community providers simplify booking, referrals and patient communication, so that more staff time goes on care and patients know what happens next.',
			'meta_description'  => 'Halveron helps hospital and community providers simplify booking, referrals and patient communication, so staff spend more time on care.',
			'quote'             => 'Our clinicians were spending hours a week chasing paperwork. Halveron helped our administrators design that work out, and the clinics run on time far more often.',
			'quote_attribution' => 'Director of Operations, a community health partnership',
			'summary'           => 'Health providers spend a large share of clinical time on administration. We help hospital and community services simplify booking, referrals and patient communication, so that staff can spend more time on care.',
		],
		'related'      => [ 'public-sector-service-academy', 'failure-demand-explained', 'coaching-hybrid-teams' ],
		'title'        => 'Healthcare',
	],
	'managed-services' => [
		'case_studies' => [ 'back-office-productivity-bank', 'complaints-handling-utility', 'change-delivery-council', 'customer-journey-insurer' ],
		'meta'         => [
			'body'              => <<<'HTML'
				<p>Outsourcing contracts are won on price and service levels, then delivered in operations that must absorb every change in volume and scope. Margins are thin, and a contract that starts well can become loss-making within a year if demand or complexity rises faster than the price. Clients increasingly expect their providers to bring improvement ideas, not only to process work.</p>
				<p>We work with providers across facilities management, business process outsourcing and contact-centre services. We help design the operation for a new contract, using demand analysis to set staffing and service levels realistically before the bid is final. During transition we help move work, people and systems from the previous provider without disrupting service. In established contracts we find the failure demand and rework that eat into margin, and help providers turn those findings into improvements they can share with their clients.</p>
				<p>Many of our managed-service clients also use our training, building lean and service-design skills in their contract teams so that improvement becomes part of how they deliver. Clients typically see contract productivity improve by 15 to 25 per cent, fewer service-level failures, and stronger cases for renewal built on evidence of the value they have added. We are happy to work alongside a provider and its client together, which often produces the most durable results.</p>
				HTML,
			'clients'           => halveron_seed_pick( $organisation_ids, [ 'orrin-facilities', 'elmshire-county-council', 'wealdmoor-health', 'corvale-railways' ] ),
			'contact'           => $person_ids['marcus-adeyemi'],
			'form_intro'        => 'Tell us about the contract or operational challenge you face, and our managed services team will be in touch.',
			'image'             => $img['sector-managed-services.jpg'],
			'intro'             => 'Managed-service providers must meet tight service levels on fixed prices, often for several clients at once. We help outsourcers design operations that meet their commitments profitably, manage contract transitions safely and show clients the value they deliver.',
			'meta_description'  => 'Halveron helps managed-service providers design operations that meet their commitments, protect margin and win renewals.',
			'quote'             => 'Halveron helped us show our client what we had improved, not just what we had processed. We renewed the contract early and on better terms.',
			'quote_attribution' => 'Managing Director, a UK facilities services provider',
			'summary'           => 'Outsourcers live or die by contract margins and service levels. We help managed-service providers design operations that meet their commitments, protect margin and win renewals.',
		],
		'related'      => [ 'contact-centre-demand-report', 'coaching-hybrid-teams', 'start-with-demand' ],
		'title'        => 'Managed and contract services',
	],
];

$sector_ids      = [];
$created_sectors = 0;
$position        = 0;

foreach ( $sectors as $slug => $sector ) {
	[ $sector_ids[ $slug ], $created ] = halveron_seed_post( 'sector', $slug, [ 'menu_order' => ++$position, 'post_title' => $sector['title'] ], $sector['meta'] );
	$created_sectors += $created;
}

echo "sectors: {$created_sectors} created, " . count( $sector_ids ) . " total\n";

$categories = [
	'news'        => 'News',
	'case-study'  => 'Case study',
	'white-paper' => 'White paper',
	'in-brief'    => 'In brief',
];

$created_categories = 0;

foreach ( $categories as $slug => $name ) {
	if ( ! term_exists( $slug, 'category' ) ) {
		wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
		$created_categories++;
	}
}

update_option( 'default_category', get_term_by( 'slug', 'news', 'category' )->term_id );

echo "categories: {$created_categories} created, " . count( $categories ) . " total\n";

$insights = [
	'future-of-service-2026'        => [
		'category' => 'news',
		'content'  => <<<'HTML'
			<p>On Thursday 24 September, 240 people from 130 organisations spent the day with us at The Assembly Rooms in Finsbury for the Future of Service Conference 2026. They ran contact centres, claims teams, rail control rooms, university admissions offices and council customer services. Most came with the same question: what should we stop doing?</p>
			<p>These are the four things we heard most often.</p>
			<h2>Demand is changing faster than plans</h2>
			<p>Several delegates said their workforce plans were built on last year's contact mix and were already out of date by spring. One energy supplier described a week in which calls about bills doubled after a price letter went out with an unclear table. Nobody had asked the contact centre to read the letter first.</p>
			<p>The lesson was not new, but it was repeated all day: understand why customers get in touch before deciding how many people you need to answer them.</p>
			<h2>Automation helps after the call, less during it</h2>
			<p>One session on artificial intelligence stood out because the speaker, a building society's operations director, was candid about results. Generated call summaries had saved her advisers around 40 seconds a call and were rated accurate by quality reviewers nine times out of ten. A customer-facing chatbot, by contrast, had answered plenty of questions but resolved few: most customers who used it called within two days anyway.</p>
			<p>The room agreed that the strongest early gains sit behind the scenes, in notes, routing and searching for answers, where a mistake is caught by a person before it reaches a customer.</p>
			<blockquote class="pull-quote">
			<p>“We kept counting how many conversations the bot handled. We should have counted how many customers never needed to call us afterwards.”</p>
			</blockquote>
			<h2>Regulation as a design brief</h2>
			<p>A panel of operations leaders from insurance, water and banking discussed how outcome-based rules have changed their work. Their view was more positive than we expected. Rules that ask firms to show customers are treated fairly gave them a reason to fix journeys they had long known were poor. Anjali Mehta has set out our own view on this in our white paper, <em>How to make regulation work for you</em>.</p>
			<h2>People still decide the outcome</h2>
			<p>The afternoon returned to staffing. Frontline turnover above 30% a year was common in the room, and several delegates linked it directly to work that made no sense to the people doing it. The organisations that had cut turnover had done so by removing pointless tasks and giving team leaders time to coach, not by raising pay alone.</p>
			<h2>Next year</h2>
			<p>Thank you to everyone who spoke, asked questions and stayed for the evening. The Future of Service Conference 2027 takes place on Wednesday 3 March 2027, again at The Assembly Rooms, Finsbury. Details and tickets are on the <a href="/conference/">conference page</a>.</p>
			HTML,
		'date'     => '2026-09-30 09:00:00',
		'excerpt'  => 'Operations and customer leaders spent a day at the Future of Service Conference 2026. Here is what they told us about demand, staffing and the limits of automation.',
		'meta'     => [
			'author_profile'   => $person_ids['ruth-calloway'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'customer-experience', 'digital-at-pace' ] ),
			'meta_description' => 'Operations and customer leaders spent a day at the Future of Service Conference 2026. Here is what they told us about demand, staffing and the limits of automation.',
			'sectors'          => halveron_seed_pick( $sector_ids, [] ),
		],
		'related'  => [ 'contact-centre-demand-report', 'regulation-work-for-you', 'start-with-demand' ],
		'title'    => 'What we heard at the Future of Service Conference 2026',
	],
	'start-with-demand'             => [
		'category' => 'in-brief',
		'content'  => <<<'HTML'
			<p>When service levels slip, the usual response is to ask for more people. It feels responsible. It is often the wrong place to start.</p>
			<p>Capacity is what you have. Demand is what customers ask of you, and it arrives in patterns: by hour, by day, by season and by reason. Few organisations measure it properly. They count calls answered and cases closed, which tells you how busy the team was, not what customers wanted.</p>
			<p>Try this for two weeks. Ask frontline staff to note, for every contact, why the customer got in touch, in the customer's words. Then sort the reasons into two piles: demand you exist to serve (a new claim, a change of address) and demand caused by something you did or failed to do (chasing, correcting, asking what a letter means).</p>
			<p>In most of the operations we study, the second pile is between a fifth and a third of the total. That is work you can remove, not staff. Once it has gone, the real capacity question gets much smaller, and much easier to answer.</p>
			<p>Plan for the demand you should have, then staff for it.</p>
			HTML,
		'date'     => '2026-09-28 09:00:00',
		'excerpt'  => 'Most operations plan for the staff they have. Plan instead for the demand that arrives, and you will often find you need less capacity than you thought.',
		'meta'     => [
			'author_profile'   => $person_ids['marcus-adeyemi'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'operations-strategy', 'process-improvement' ] ),
			'meta_description' => 'Most operations plan for the staff they have. Plan instead for the demand that arrives, and you will often find you need less capacity than you thought.',
			'sectors'          => halveron_seed_pick( $sector_ids, [] ),
		],
		'related'  => [ 'failure-demand-explained', 'contact-centre-demand-report', 'back-office-productivity-bank' ],
		'title'    => 'Start with demand, not capacity',
	],
	'regulation-work-for-you'       => [
		'category' => 'white-paper',
		'content'  => <<<'HTML'
			<p>Regulators across the service economy have moved from telling firms what to do to asking them to show what customers experience. Financial services firms must evidence good outcomes for retail customers. Water and energy companies are measured on complaints, vulnerable-customer support and how quickly they put things right. Public bodies face service standards and ombudsman scrutiny.</p>
			<p>Most organisations have responded by adding work: new checks, new reports, new committees. The cost is real and the benefit to customers is often hard to find. In our experience the firms that took a different view spent less on compliance within two years, not more, because the same simplification served both purposes.</p>
			<p>This paper argues for a different response. Outcome-based rules ask the same questions a good operations leader already asks. Does the customer understand what we sent them? Did we fix the problem first time? Who gets stuck, and why? Treated as a design brief rather than a compliance burden, regulation gives you the mandate, the measures and often the budget to remove the work that frustrates customers and staff alike.</p>
			HTML,
		'date'     => '2026-09-23 09:00:00',
		'excerpt'  => 'Most service firms treat regulation as a cost to contain. This paper argues that outcome-based rules give operations leaders a strong case for simpler processes and less rework.',
		'meta'     => [
			'author_profile'   => $person_ids['anjali-mehta'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'customer-experience', 'operating-model-design' ] ),
			'meta_description' => 'Most service firms treat regulation as a cost to contain. This paper argues that outcome-based rules give operations leaders a strong case for simpler processes and less rework.',
			'paper_audience'   => <<<'HTML'
				<p>The paper is written for operations directors, chief customer officers, heads of compliance and risk, and transformation leads in regulated service organisations. It assumes you know your own regulatory regime; it does not explain any single rulebook in detail, and it is not legal or regulatory advice.</p>
				<p>Operations and compliance teams will get most from reading it together. Each section ends with questions to discuss, and the checklist is designed for a two-hour joint workshop.</p>
				HTML,
			'paper_covers'     => [
				[
					'text' => 'Why outcome-based regulation rewards simpler processes, and how adding controls can make outcomes worse.',
				],
				[
					'text' => 'How to use the evidence you already hold, from complaints, call reasons and repeat contact, to show regulators how customers are treated.',
				],
				[
					'text' => 'A five-step method for testing a customer journey against the outcomes your regulator expects, with a worked example from home insurance claims.',
				],
				[
					'text' => 'How to identify customers in vulnerable circumstances through the work itself, not through a separate process bolted on afterwards.',
				],
				[
					'text' => 'What a board and an executive committee should see each month, and what they can safely stop receiving.',
				],
				[ 'text' => 'Three short case examples from banking, insurance and a water utility, anonymised.' ],
				[ 'text' => 'A one-page checklist for operations and compliance teams to work through together.' ],
			],
			'paper_details'    => '28 pages, PDF',
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'banking', 'insurance' ] ),
		],
		'related'  => [ 'customer-journey-insurer', 'complaints-handling-utility', 'failure-demand-explained' ],
		'title'    => 'How to make regulation work for you',
	],
	'public-sector-service-academy' => [
		'category' => 'news',
		'content'  => <<<'HTML'
			<p>We are opening the Halveron Service Academy, a programme for managers who run public services. The first cohort of 20 places begins on Tuesday 26 January 2027 at our training centre in Woodstock, Oxfordshire.</p>
			<p>The academy grew out of our work with councils, housing associations and government agencies over the past few years. Again and again we met capable managers who had been promoted for knowing the service well, then handed budgets, targets and change programmes with little support. Most of them were good at running the service. Few had been taught how to redesign it.</p>
			<h2>How the academy works</h2>
			<p>The programme runs for ten months. Delegates attend six two-day modules, roughly six weeks apart, and between modules they lead an improvement project in their own organisation, chosen with their sponsor before the course begins.</p>
			<p>The modules cover:</p>
			<ul>
			<li>understanding demand and why people contact a public service</li>
			<li>designing services around the people who use them</li>
			<li>measures that help managers learn rather than chase targets</li>
			<li>leading change with staff, unions and elected members</li>
			<li>making the case for investment and tracking the benefits</li>
			<li>coaching teams to keep improving after the project ends</li>
			</ul>
			<p>Each delegate has a Halveron coach for the full ten months. Projects are presented to an invited panel of senior public-sector leaders at the final module in November 2027.</p>
			<h2>Accreditation and cost</h2>
			<p>Delegates who complete the programme and their project are assessed for practitioner certification by the Service Operations Institute.</p>
			<p>A place costs £4,800 plus VAT, which covers all modules, coaching, materials and meals. We are funding four places in full for organisations with fewer than 500 staff, such as smaller district councils, parish-level services and charities delivering public contracts.</p>
			<h2>How to apply</h2>
			<p>Applications are open until Friday 20 November 2026. We ask each applicant for a short description of the service they manage and the problem they would like to work on, plus a supporting note from a sponsor at director level.</p>
			<p>"Public services do not lack committed managers," says Daniel Fry, who leads our public-sector work. "They lack time and permission to step back and redesign the work. The academy gives them both, and a project that pays for the place several times over."</p>
			<p>To apply or ask a question, email <a href="mailto:training@halveron.junaid.guru">training@halveron.junaid.guru</a> or call our training team on +44 (0)1632 960415.</p>
			HTML,
		'date'     => '2026-09-16 09:00:00',
		'excerpt'  => "A ten-month programme for managers who run public services, built around a live improvement project in each delegate's own organisation. The first cohort starts in January 2027.",
		'meta'     => [
			'author_profile'   => $person_ids['daniel-fry'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'learning-and-development', 'organisational-effectiveness' ] ),
			'meta_description' => "A ten-month programme for managers who run public services, built around a live improvement project in each delegate's own organisation. The first cohort starts in January 2027.",
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'public-sector' ] ),
		],
		'related'  => [ 'change-delivery-council', 'coaching-hybrid-teams', 'lean-forum-100-members' ],
		'title'    => 'Halveron opens a service academy for public-sector leaders',
	],
	'customer-journey-insurer'      => [
		'category' => 'case-study',
		'content'  => <<<'HTML'
			<h2>The challenge</h2>
			<p>A mutual insurer with 1.2 million members had seen its satisfaction scores for home and motor claims fall for three years in a row. Members were not unhappy with the settlements. They were unhappy with how long it took and how little they heard along the way.</p>
			<p>The claims operation, around 380 people across two sites, was busy. Calls to the claims line had risen by 15% in two years without any rise in the number of claims. Leaders assumed the answer was faster settlement and had begun to scope a new claims system.</p>
			<p>Before committing to it, the insurer's chief operating officer asked us to find out what members were actually experiencing.</p>
			<h2>What we did</h2>
			<p>We spent ten weeks inside the claims operation with a joint team of six: three Halveron consultants and three experienced claims handlers released from their day jobs.</p>
			<p>We listened to 1,400 recorded calls and sorted each by the member's reason for calling. Almost half of all calls to the claims line were members chasing progress or asking what happened next. Most of the rest were genuine first notifications and questions about cover.</p>
			<p>We then followed 120 claims from first call to settlement. A typical home claim passed through 14 handoffs between teams, loss adjusters and repairers. On average a member was asked for the same information three times, and in most claims there were stretches of more than a week when nobody contacted the member at all. Speed was a problem; silence was a bigger one.</p>
			<p>With the claims teams, we redesigned the journey in three ways:</p>
			<ul>
			<li><strong>One owner for every complex claim.</strong> A named handler now keeps a claim from first call to settlement, with a direct number the member can use.</li>
			<li><strong>Updates before members ask.</strong> A text message or email goes out at each of five agreed milestones, written in plain English and tested with members before launch.</li>
			<li><strong>Information gathered once.</strong> We rebuilt the first-notification script so the details adjusters and repairers need are collected on the first call and shared, not requested again.</li>
			</ul>
			<p>We piloted the changes in one claims centre for twelve weeks, measured them against the other, then supported the rollout across both sites. The new claims system was rescoped around the redesigned journey and its budget reduced.</p>
			<h2>The result</h2>
			<p>Twelve months after rollout, the insurer reported:</p>
			HTML,
		'date'     => '2026-09-09 09:00:00',
		'excerpt'  => 'A mutual insurer wanted to know why its customers kept calling back. We walked the claims journey with them and redesigned the steps that caused most repeat contact.',
		'meta'     => [
			'author_profile'   => $person_ids['anjali-mehta'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'customer-experience', 'process-improvement' ] ),
			'meta_description' => 'A mutual insurer wanted to know why its customers kept calling back. We walked the claims journey with them and redesigned the steps that caused most repeat contact.',
			'outcomes'         => [
				[ 'figure' => '38%', 'label' => 'fewer calls to the claims line from members chasing progress' ],
				[
					'figure' => '22 days',
					'label'  => 'average home-claim cycle time, down from 31, with no change to the claims system',
				],
				[ 'figure' => '27%', 'label' => 'fewer complaints about claims handling' ],
			],
			'outcomes_after'   => <<<'HTML'
				<p>Claims handlers said the work was calmer and more satisfying. Staff turnover in the claims operation fell from 29% to 18% over the same year.</p>
				<blockquote class="pull-quote">
				<p>“We thought we had a speed problem. It turned out we had a silence problem, and that was much cheaper to fix.”</p>
				<cite class="pull-quote__attribution">Head of Claims, the insurer</cite>
				</blockquote>
				HTML,
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'insurance' ] ),
		],
		'related'  => [ 'regulation-work-for-you', 'complaints-handling-utility', 'failure-demand-explained' ],
		'title'    => 'Understanding the customer journey at a mutual insurer',
	],
	'contact-centre-demand-report'  => [
		'category' => 'news',
		'content'  => <<<'HTML'
			<p>Our third annual demand report is out. This year we analysed 18.4 million inbound contacts handled by 31 UK organisations in banking, insurance, utilities, travel, education and the public sector, covering every channel from phone and email to webchat and messaging, for the whole of 2025.</p>
			<h2>Contact is falling, effort is not</h2>
			<p>Total contact volumes fell by 3% compared with the previous year. Average handling time across all channels rose by 7%. Customers are getting in touch slightly less often, but the contacts that remain are longer and more complicated, often because simple questions have moved online and the difficult ones are left for advisers.</p>
			<p>Repeat contact remains stubborn: 19% of customers got in touch again about the same issue within seven days.</p>
			<h2>Failure demand is still a third of the work</h2>
			<p>Across the sample, 29% of contacts were failure demand: customers chasing, correcting an error or asking what something meant. The range was wide, from 14% at one water company to 47% at a travel operator during a disruption-heavy summer. The top quartile were not the organisations with the newest technology. They were the ones that reviewed contact reasons every week and gave someone the job of removing the causes.</p>
			<h2>Digital channels absorb demand rather than remove it</h2>
			<p>Webchat and messaging now account for 29% of contacts, up from 22%. That shift has not reduced the load on phone teams as much as hoped. One in four chats was followed by a phone call from the same customer within 48 hours.</p>
			<p>Chatbots reported an average containment rate of 31%. When we checked which of those customers did not get in touch again within seven days, the figure fell to 12%. Containment is not resolution, and the gap matters when budgets are set on the first number.</p>
			<h2>What the top quartile did differently</h2>
			<p>The organisations with the lowest failure demand and repeat contact shared three habits:</p>
			<ul>
			<li>they measured contact reasons, not just volumes</li>
			<li>they gave operations leaders a say in letters, web content and product changes that drive contact</li>
			<li>they judged digital channels on resolution, not on how many contacts they deflected</li>
			</ul>
			<h2>Get the report</h2>
			<p>The full report includes benchmarks by sector and channel. Participating organisations receive their own results against the sample. To request a copy, email <a href="mailto:hello@halveron.junaid.guru">hello@halveron.junaid.guru</a>.</p>
			HTML,
		'date'     => '2026-08-27 09:00:00',
		'excerpt'  => 'We analysed 18.4 million customer contacts across 31 UK organisations. Contact volumes are falling slightly, but each contact is harder, and digital channels are absorbing demand rather than removing it.',
		'meta'     => [
			'author_profile'   => $person_ids['grace-okonkwo'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'customer-experience', 'operations-strategy' ] ),
			'meta_description' => 'We analysed 18.4 million customer contacts across 31 UK organisations. Contact volumes are falling slightly, but each contact is harder, and digital channels are absorbing demand rather than removing it.',
			'sectors'          => halveron_seed_pick( $sector_ids, [] ),
		],
		'related'  => [ 'start-with-demand', 'failure-demand-explained', 'future-of-service-2026' ],
		'title'    => 'Our 2026 contact-centre demand report',
	],
	'failure-demand-explained'      => [
		'category' => 'in-brief',
		'content'  => <<<'HTML'
			<p>Failure demand is demand caused by a failure to do something, or to do something right, for the customer. The idea comes from systems thinking, and it is the simplest useful lens we know for a service operation.</p>
			<p>Value demand is why you exist: a customer wants to make a claim, open an account or report a fault. Failure demand is everything that follows when the first attempt does not work. "Where is my refund?" "You sent me the wrong form." "What does this letter mean?" "I was told someone would call back."</p>
			<p>Three things make it worth your attention.</p>
			<h2>It is large</h2>
			<p>In most operations we measure, failure demand is between a fifth and a third of all contact. Every one of those contacts has a cost, and most create more work behind them.</p>
			<h2>It is hidden</h2>
			<p>Standard reports count contacts by channel and by product. They rarely ask why the customer got in touch, so failure demand sits inside the averages, looking like ordinary work.</p>
			<h2>It is controllable</h2>
			<p>Failure demand has causes inside your organisation: an unclear letter, a broken handoff, a promise nobody keeps. Fix the cause and the demand disappears for good.</p>
			<p>Start by asking frontline staff to record why customers get in touch, in the customer's words, for two weeks.</p>
			HTML,
		'date'     => '2026-08-19 09:00:00',
		'excerpt'  => 'Failure demand is the work customers create when we fail to do something, or do it wrong. In most service operations it is a large cost, and a controllable one.',
		'meta'     => [
			'author_profile'   => $person_ids['marcus-adeyemi'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'process-improvement' ] ),
			'meta_description' => 'Failure demand is the work customers create when we fail to do something, or do it wrong. In most service operations it is a large cost, and a controllable one.',
			'sectors'          => halveron_seed_pick( $sector_ids, [] ),
		],
		'related'  => [ 'start-with-demand', 'contact-centre-demand-report', 'complaints-handling-utility' ],
		'title'    => 'Failure demand on one page',
	],
	'complaints-handling-utility'   => [
		'category' => 'case-study',
		'content'  => <<<'HTML'
			<h2>The challenge</h2>
			<p>A regional water company serving 2.3 million customers had watched written complaints rise for two years. It was slipping down the industry's published comparison tables, and the regulator's incentive measures meant that every complaint had a direct financial cost as well as a reputational one.</p>
			<p>Complaints took an average of 19 working days to resolve. More than one in five went on to a second stage, and each escalation cost several times as much to handle as the first. A central complaints team of 45 people handled every case; frontline advisers were told to log complaints and pass them on.</p>
			<p>The customer services director asked us to help reduce complaint volumes and speed up resolution without adding staff.</p>
			<h2>What we did</h2>
			<p>We started with 600 closed complaints, read in full by a joint team of Halveron consultants and complaints handlers. We recorded the cause of each complaint, not its category, and the number of times the customer had contacted the company before complaining.</p>
			<p>Three findings shaped the work. First, 58% of complaints followed at least two earlier contacts about the same problem: customers complained because nothing had happened, not because something had gone wrong once. Second, four causes accounted for more than half of all complaints: estimated bills after missed meter readings, unclear payment-plan letters, missed appointments for leak repairs and delays in refunds. Third, most complaints could have been resolved by the first person the customer spoke to, if that person had been allowed to.</p>
			<p>With the company's teams we then:</p>
			<ul>
			<li><strong>Gave frontline advisers authority to resolve.</strong> Advisers can now put things right on the call, including goodwill payments of up to £100, with simple guidance and a weekly review rather than prior approval.</li>
			<li><strong>Moved complaints handlers closer to the causes.</strong> The central team was reorganised around the four main causes, each working directly with the billing, metering or field operations team responsible.</li>
			<li><strong>Ran a weekly root-cause review.</strong> A thirty-minute meeting of operations managers reviews the week's complaints and agrees one fix, with an owner and a date.</li>
			<li><strong>Rewrote the letters.</strong> We rewrote the eight letters that generated most contact, testing each with customers before it went live.</li>
			</ul>
			<h2>The result</h2>
			<p>Over the following twelve months, the company reported:</p>
			HTML,
		'date'     => '2026-08-12 09:00:00',
		'excerpt'  => 'Complaints at a regional water company were rising and taking weeks to close. We helped frontline teams resolve most at first contact and fix the causes behind them.',
		'meta'     => [
			'author_profile'   => $person_ids['anjali-mehta'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'process-improvement', 'customer-experience' ] ),
			'meta_description' => 'Complaints at a regional water company were rising and taking weeks to close. We helped frontline teams resolve most at first contact and fix the causes behind them.',
			'outcomes'         => [
				[ 'figure' => '64%', 'label' => 'of complaints resolved at first contact, up from 21%' ],
				[ 'figure' => '7 days', 'label' => 'average resolution time, down from 19 working days' ],
				[ 'figure' => '31%', 'label' => 'fewer written complaints, with escalations to the second stage down by 45%' ],
			],
			'outcomes_after'   => <<<'HTML'
				<p>The central complaints team is now 32 people. The other 13 moved into frontline and field-scheduling roles as vacancies arose, with no redundancies.</p>
				<blockquote class="pull-quote">
				<p>“Our advisers always knew how to fix most of these. We had simply never let them.”</p>
				<cite class="pull-quote__attribution">Customer Services Director, the water company</cite>
				</blockquote>
				HTML,
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'utilities' ] ),
		],
		'related'  => [ 'failure-demand-explained', 'customer-journey-insurer', 'regulation-work-for-you' ],
		'title'    => 'Improving complaints handling at a water utility',
	],
	'technology-in-travel'          => [
		'category' => 'white-paper',
		'content'  => <<<'HTML'
			<p>Travel operators have spent heavily on technology over the past five years: apps, self-service check-in, automated rebooking, chatbots and new contact-centre platforms. Some of that spending has changed the passenger experience for the better. Some of it has moved work from one team to another, or from the operator to the passenger.</p>
			<p>This review draws on interviews with operations and customer leaders at 24 UK airlines, rail operators, ferry companies and tour operators, and on contact data from eight of them. It asks a practical question: which investments reduced the effort for passengers and staff, and which did not?</p>
			<p>The answer depends less on the technology than on whether the underlying process was sound. Automated rebooking worked well where disruption rules were simple and agreed in advance. Where they were not, it produced options passengers could not use and calls that took longer to resolve.</p>
			<p>The same pattern appeared in contact centres. Operators that judged chatbots on how many conversations they handled reported success; those that checked whether passengers called afterwards found a different picture. The paper sets out the measures we think matter, and shows how four operators have changed what they report to their boards. Interviews took place between January and April 2026.</p>
			HTML,
		'date'     => '2026-07-15 09:00:00',
		'excerpt'  => 'Our sector review asks where automation, self-service and better data have helped travel operators, and where they simply moved the work somewhere else.',
		'meta'     => [
			'author_profile'   => $person_ids['grace-okonkwo'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'digital-at-pace', 'customer-experience' ] ),
			'meta_description' => 'Our sector review asks where automation, self-service and better data have helped travel operators, and where they simply moved the work somewhere else.',
			'paper_audience'   => '<p>The review is written for chief operating officers, customer directors, heads of digital and contact-centre leaders in travel and transport. Much of it applies to any operator that manages bookings, schedules and disruption, including events and hospitality. It does not recommend particular products or suppliers.</p>',
			'paper_covers'     => [
				[
					'text' => 'Where self-service has reduced demand on staff, and where passengers abandon it at the first exception.',
				],
				[ 'text' => 'What the operators with the fewest disruption calls did before, during and after a disrupted day.' ],
				[
					'text' => 'How to judge chatbots and messaging on resolution rather than containment, with figures from four operators.',
				],
				[ 'text' => 'The data that operations teams need in real time, and the reports they can stop producing.' ],
				[
					'text' => 'How to sequence technology investment behind process change, with a twelve-month example from a ferry operator.',
				],
				[ 'text' => 'A short guide to assessing technology suppliers on outcomes, not features.' ],
			],
			'paper_details'    => '32 pages, PDF',
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'travel' ] ),
		],
		'related'  => [ 'contact-centre-demand-report', 'future-of-service-2026', 'start-with-demand' ],
		'title'    => 'Sector review: technology in travel operations',
	],
	'lean-forum-100-members'        => [
		'category' => 'news',
		'content'  => <<<'HTML'
			<p>The Lean Service Forum now has 100 member organisations. Vellmore Building Society, which hosted our meeting in Bristol on 18 June, became the hundredth when it joined the following week.</p>
			<p>The forum began with eleven organisations meeting in a hired room in Oxford. The idea was simple: operations leaders learn most from each other, and most of all from seeing each other's work. Every meeting since has been hosted by a member, and every one has included time on the host's operations floor, not just in its boardroom.</p>
			<h2>Who the members are</h2>
			<p>The forum now includes 23 financial services firms, 18 utilities and energy companies, 17 public bodies, 12 travel and transport operators, 11 healthcare providers, 9 universities and colleges, and 10 organisations from housing, facilities management and retail services.</p>
			<p>Members range from a housing association with 400 staff to a national rail operator with several thousand. What they share is responsibility for service operations, and a willingness to show their work to peers.</p>
			<h2>What has not changed</h2>
			<p>Membership is still free and still by application. We still ask each member to host a meeting when their turn comes, and we still keep the forum free of selling: suppliers and consultancies other than Halveron cannot join, and we do not use meetings to promote our own services.</p>
			<p>The meetings keep the same shape: a site visit, a case session from the host, and a peer clinic where members bring a live problem and leave with suggestions from people who have faced it.</p>
			<h2>Looking ahead</h2>
			<p>Our next meeting is on Thursday 19 November 2026, hosted by Northwyn Rail in Manchester, with a visit to its customer information and control centre. Places are allocated to members first.</p>
			<p>"A hundred members is a milestone, but the number matters less than the honesty in the room," says Ruth Calloway, our managing director. "People come because they can say what has not worked and get a straight answer from someone who has been there."</p>
			<p>If your organisation runs service operations and would like to join, read about <a href="/forum/#membership">membership</a> or contact Daniel Fry at <a href="mailto:forum@halveron.junaid.guru">forum@halveron.junaid.guru</a>.</p>
			HTML,
		'date'     => '2026-07-08 09:00:00',
		'excerpt'  => 'With the building society that hosted our June meeting joining as its hundredth member, the Lean Service Forum now brings together operations leaders from 100 organisations.',
		'meta'     => [
			'author_profile'   => $person_ids['ruth-calloway'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'process-improvement', 'organisational-effectiveness' ] ),
			'meta_description' => 'With the building society that hosted our June meeting joining as its hundredth member, the Lean Service Forum now brings together operations leaders from 100 organisations.',
			'sectors'          => halveron_seed_pick( $sector_ids, [] ),
		],
		'related'  => [ 'public-sector-service-academy', 'future-of-service-2026', 'coaching-hybrid-teams' ],
		'title'    => 'The Lean Service Forum reaches 100 member organisations',
	],
	'back-office-productivity-bank' => [
		'category' => 'case-study',
		'content'  => <<<'HTML'
			<h2>The challenge</h2>
			<p>A regional bank with 420,000 personal and business customers ran its mortgage servicing and payments operations from two sites, with 310 staff between them. Work arrived unevenly, peaked at month end and was allocated by whoever had capacity that morning.</p>
			<p>By the time the bank's operations director contacted us, the backlog of customer requests stood at eleven working days. Overtime had become routine, costing more than £900,000 a year, and two attempts to reduce the backlog with temporary staff had cleared it briefly before it returned. Customers chased, and every chase added more work.</p>
			<p>The bank wanted to clear the backlog for good and to understand what capacity it actually needed.</p>
			<h2>What we did</h2>
			<p>We worked with the bank for six months, starting in one team at each site and then extending the approach across both.</p>
			<p>First, we measured demand. For four weeks, every piece of work was logged by type, arrival time and handling time. It showed that 22% of the work was failure demand, mostly customers chasing requests already in the backlog, and that month-end peaks were predictable to within a few per cent. Two routine tasks that had always been done at month end could safely move earlier, which took the top off the peak.</p>
			<p>Second, we built a simple capacity plan. A spreadsheet model, owned by the bank's planning team, now forecasts each week's demand by work type and compares it with the hours available. Managers can see a shortfall coming two weeks ahead instead of on the day.</p>
			<p>Third, we introduced standard work and daily routines. Teams agreed one standard method for each of the twenty most common tasks and wrote it down. Each team now starts the day with a ten-minute huddle at a planning board to agree priorities and flag problems, and team leaders spend an hour a day coaching at desks.</p>
			<p>Fourth, with the bank's technology team, we removed the most wasteful re-keying. Three small automations, built on tools the bank already licensed, took information from customer forms straight into the servicing system.</p>
			<p>We trained 24 team leaders and managers to run the routines themselves, and handed over the planning model with written guidance.</p>
			<h2>The result</h2>
			<p>Within four months the backlog was cleared and has stayed below two days since. After a full year, the bank reported:</p>
			HTML,
		'date'     => '2026-06-24 09:00:00',
		'excerpt'  => "A regional bank's back office was carrying an eleven-day backlog and heavy overtime. Better planning, standard work and daily team routines cleared it within four months.",
		'meta'     => [
			'author_profile'   => $person_ids['marcus-adeyemi'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'operations-strategy', 'process-improvement' ] ),
			'meta_description' => "A regional bank's back office was carrying an eleven-day backlog and heavy overtime. Better planning, standard work and daily team routines cleared it within four months.",
			'outcomes'         => [
				[ 'figure' => '23%', 'label' => 'more work completed per hour worked, measured across both sites' ],
				[ 'figure' => 'Under 2', 'label' => 'working days of backlog, down from eleven' ],
				[ 'figure' => '£640,000', 'label' => 'a year less spent on overtime' ],
			],
			'outcomes_after'   => <<<'HTML'
				<p>Chasing calls fell with the backlog. Around 40 roles were released through normal staff turnover and moved into customer-facing teams. There were no redundancies.</p>
				<blockquote class="pull-quote">
				<p>“We had always tried to solve this with more people. The answer was knowing what was coming and agreeing how to do it.”</p>
				<cite class="pull-quote__attribution">Head of Operations, the bank</cite>
				</blockquote>
				HTML,
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'banking' ] ),
		],
		'related'  => [ 'start-with-demand', 'change-delivery-council', 'coaching-hybrid-teams' ],
		'title'    => 'Driving back-office productivity at a regional bank',
	],
	'coaching-hybrid-teams'         => [
		'category' => 'in-brief',
		'content'  => <<<'HTML'
			<p>In an office, a good team leader coaches all day without calling it coaching. They hear a difficult call, wait until it ends, and ask how it went. They see someone stuck and pull up a chair.</p>
			<p>Hybrid working removes most of those moments. Many team leaders we train now coach only in scheduled one-to-ones, which are too infrequent and too formal to change how someone handles the next customer.</p>
			<p>Three habits help.</p>
			<h2>Listen in, then talk within the hour</h2>
			<p>Agree with each team member that you will listen to one live or recorded contact a week and talk about it the same day, for ten minutes, on video. Short and soon beats long and late.</p>
			<h2>Ask before you tell</h2>
			<p>Open with "What did you notice?" rather than your own view. People remember what they work out for themselves.</p>
			<h2>Make it visible</h2>
			<p>Keep a shared board, physical or online, of the skills each person is working on. It turns coaching into a joint project rather than a judgement.</p>
			<p>None of this needs new technology. It needs team leaders to protect around five hours a week for it, and their managers to treat that time as the job, not a distraction from it.</p>
			HTML,
		'date'     => '2026-06-10 09:00:00',
		'excerpt'  => 'When half the team works from home, team leaders lose the quick desk-side conversations that did most of their coaching. Here is how to put them back.',
		'meta'     => [
			'author_profile'   => $person_ids['tom-whitlock'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'learning-and-development', 'organisational-effectiveness' ] ),
			'meta_description' => 'When half the team works from home, team leaders lose the quick desk-side conversations that did most of their coaching. Here is how to put them back.',
			'sectors'          => halveron_seed_pick( $sector_ids, [] ),
		],
		'related'  => [ 'back-office-productivity-bank', 'public-sector-service-academy', 'start-with-demand' ],
		'title'    => 'Coaching hybrid operations teams',
	],
	'change-delivery-council'       => [
		'category' => 'case-study',
		'content'  => <<<'HTML'
			<h2>The challenge</h2>
			<p>A county council serving 780,000 residents needed to save £18 million over three years while demand for adult social care and children's services kept rising. Its transformation programme listed 64 change projects across every directorate.</p>
			<p>Most were behind schedule. Only around a third had delivered on time in the previous year, and the council could not say with confidence how much of the planned saving had been achieved. Each directorate used its own approach to project management, some used none, and the small central transformation team spent most of its time chasing updates. Elected members had begun to ask, reasonably, whether the programme would deliver at all.</p>
			<p>The assistant chief executive asked us to help the council deliver change reliably with its own people, rather than relying on consultants for each project.</p>
			<h2>What we did</h2>
			<p>We worked with the council for nine months in three stages.</p>
			<p><strong>Cutting the list.</strong> With the corporate leadership team, we reviewed every project against three questions: what problem does it solve for residents or staff, what will it save, and who will lead it. Projects that could not answer all three were stopped or merged. The portfolio fell from 64 projects to 29, each with a named sponsor and lead.</p>
			<p><strong>Agreeing one method.</strong> We worked with officers from every directorate to design a single change-delivery method for the council: five stages, a one-page project charter, a fortnightly review and a simple benefits log owned by finance. We kept it deliberately light, so a small project could use it in an afternoon and a large one would not outgrow it.</p>
			<p><strong>Building capability.</strong> We trained 46 officers as change practitioners over four two-day modules, each applying the method to a live project between sessions. Practitioners were assessed and certified by the Service Operations Institute. We then coached the leads of the ten largest projects through their first two stages and helped the council set up a change office of four people to support the method once we had gone.</p>
			<h2>The result</h2>
			<p>At the end of the first full year with the new approach, the council reported:</p>
			HTML,
		'date'     => '2026-05-20 09:00:00',
		'excerpt'  => 'A county council had 64 change projects, a savings target and no common way to deliver them. We helped it cut the list, agree one method and train its people.',
		'meta'     => [
			'author_profile'   => $person_ids['daniel-fry'],
			'capabilities'     => halveron_seed_pick( $capability_ids, [ 'business-transformation', 'learning-and-development' ] ),
			'meta_description' => 'A county council had 64 change projects, a savings target and no common way to deliver them. We helped it cut the list, agree one method and train its people.',
			'outcomes'         => [
				[ 'figure' => '78%', 'label' => 'of project milestones delivered on time, up from 35%' ],
				[ 'figure' => '£6.2m', 'label' => 'of savings delivered in the year, against a target of £5.5 million' ],
				[ 'figure' => '46', 'label' => 'officers certified as change practitioners, with 12 more in training' ],
			],
			'outcomes_after'   => <<<'HTML'
				<p>Officers also said the fortnightly reviews had changed the tone of conversations about change: problems surfaced earlier, and projects that were not working were stopped sooner.</p>
				<p>The change office now trains new practitioners itself, using the material we handed over. The council has since used the same method for its highways and waste contracts.</p>
				<blockquote class="pull-quote">
				<p>“For the first time we can say what we are changing, why, and what it has saved, and we can do it ourselves.”</p>
				<cite class="pull-quote__attribution">Assistant Chief Executive, the council</cite>
				</blockquote>
				HTML,
			'sectors'          => halveron_seed_pick( $sector_ids, [ 'public-sector' ] ),
		],
		'related'  => [ 'public-sector-service-academy', 'back-office-productivity-bank', 'coaching-hybrid-teams' ],
		'title'    => 'Improving change-delivery capability in a county council',
	],
];

$post_ids      = [];
$created_posts = 0;

foreach ( $insights as $slug => $insight ) {
	[ $post_ids[ $slug ], $created ] = halveron_seed_post( 'post', $slug, [
		'post_content'  => $insight['content'],
		'post_date'     => $insight['date'],
		'post_date_gmt' => get_gmt_from_date( $insight['date'] ),
		'post_excerpt'  => $insight['excerpt'],
		'post_title'    => $insight['title'],
	], $insight['meta'], $img[ "insight-{$slug}.jpg" ] );

	wp_set_object_terms( $post_ids[ $slug ], $insight['category'], 'category' );
	$created_posts += $created;
}

echo "insights: {$created_posts} created, " . count( $post_ids ) . " total\n";

foreach ( $insights as $slug => $insight ) {
	update_post_meta( $post_ids[ $slug ], 'related', halveron_seed_pick( $post_ids, $insight['related'] ) );
}

foreach ( $capabilities as $slug => $capability ) {
	update_post_meta( $capability_ids[ $slug ], 'related', halveron_seed_pick( $post_ids, $capability['related'] ) );
}

foreach ( $sectors as $slug => $sector ) {
	update_post_meta( $sector_ids[ $slug ], 'case_studies', halveron_seed_pick( $post_ids, $sector['case_studies'] ) );
	update_post_meta( $sector_ids[ $slug ], 'related', halveron_seed_pick( $post_ids, $sector['related'] ) );
}

echo 'relations: ' . ( count( $insights ) + count( $capabilities ) + count( $sectors ) ) . " items linked to insights\n";

$courses = [
	'lean-practitioner'      => [
		'content' => <<<'HTML'
			<p>Lean Practitioner is our core course for people who improve service operations. It teaches lean thinking as it applies to service work: claims, applications, enquiries and cases rather than parts on a production line.</p>
			<p>The course runs in two blocks, three days and then two, about four weeks apart. Between blocks you carry out a short demand study in your own team, and in the second block you use what you found to design and test an improvement. You leave with a project under way, not just a set of notes.</p>
			<p>Delegates are assessed on their project and a short written reflection. Those who pass are certified at practitioner level under the Service Operations Institute's Service Operations Competency Framework.</p>
			HTML,
		'image'   => 'training-classroom.jpg',
		'meta'    => [
			'accreditation'    => 'Accredited by the Service Operations Institute',
			'contact'          => $person_ids['tom-whitlock'],
			'course_type'      => 'open',
			'duration'         => 'Five days',
			'location'         => 'Woodstock, Oxfordshire',
			'meta_description' => 'Five days that teach you to see a service the way your customers do.',
			'modules'          => [
				[
					'text'  => "We start with the purpose of your service from the customer's point of view and the measures that show whether you are achieving it. You will learn why activity measures such as calls answered or cases closed can hide poor service, and what to measure instead.",
					'title' => 'Seeing the service as customers see it',
				],
				[
					'text'  => "How to capture why customers contact you, sort value demand from failure demand, and spot patterns by time of day, channel and reason. You will practise classifying real contacts from other delegates' organisations, then design the two-week demand study you will carry out in your own team between the two blocks.",
					'title' => 'Understanding demand',
				],
				[
					'text'  => "Following a single piece of work from first contact to completion, counting the handoffs, waits and rework along the way. You will measure end-to-end time from the customer's point of view rather than each team's, and see why local targets so often make the whole journey slower.",
					'title' => 'Mapping the flow of work',
				],
				[
					'text'  => 'Using your demand study to design an improvement, then testing it on a small scale with clear before-and-after measures. We cover how to run an experiment without disrupting the service, and how to stop one that is not working. You will plan your own first test before the course ends.',
					'title' => 'Designing and testing improvements',
				],
				[
					'text'  => 'How teams agree a standard method for their most common tasks, make performance visible on a simple board and hold short daily reviews that solve problems rather than assign blame. We visit a working example and discuss what keeps these routines alive once the novelty has worn off.',
					'title' => 'Standard work and daily management',
				],
				[
					'text'  => 'Making the case to your director, involving frontline staff from the start and keeping the improvement going after the project ends. On the final afternoon you present your project to the group, take questions and agree your next steps with a Halveron coach, who follows up with you a month later.',
					'title' => 'Leading improvement',
				],
			],
			'modules_lead'     => 'Lean Practitioner is delivered through these modules:',
			'next_date'        => '2026-11-10',
			'price'            => '£2,450 plus VAT',
			'related'          => halveron_seed_pick( $post_ids, [ 'start-with-demand', 'failure-demand-explained', 'back-office-productivity-bank' ] ),
			'schedule'         => 'Two blocks: 10 to 12 November and 8 to 9 December 2026',
			'summary'          => 'Five days that teach you to see a service the way your customers do. You will learn to measure demand, map value and run improvement experiments, then apply each tool to a live problem you bring from work.',
			'who_for'          => "Team leaders, operations managers, improvement specialists and analysts in any service organisation. You need no previous lean training, but you will need your manager's agreement to run a small study in your own area between the two blocks.",
		],
		'title'   => 'Lean Practitioner',
	],
	'service-excellence'     => [
		'content' => <<<'HTML'
			<p>Service Excellence is for managers who are responsible for how customers experience a service, whether they meet customers face to face, on the phone or online. It is less about service standards and scripts than about designing the work so that good service is the easy path for your team.</p>
			<p>Over three days you will map a real customer journey from your own organisation, find the points where it breaks, and redesign one of them. You will also look at how measures shape behaviour, and how to give frontline staff the authority to put things right for customers without referring upwards.</p>
			<p>Delegates who complete a short post-course assignment receive a certificate accredited by the Centre for Customer Practice.</p>
			HTML,
		'image'   => 'training-workshop.jpg',
		'meta'    => [
			'accreditation'    => 'Accredited by the Centre for Customer Practice',
			'contact'          => $person_ids['tom-whitlock'],
			'course_type'      => 'open',
			'duration'         => 'Three days',
			'location'         => 'Woodstock, Oxfordshire',
			'meta_description' => 'Three days for managers who lead customer-facing teams.',
			'modules'          => [
				[
					'text'  => 'How to find out what customers value in your service, using complaints, contact reasons, survey comments and short conversations with customers. You will compare what customers say they want with what your organisation measures, and see why the two are often further apart than anyone expected.',
					'title' => 'What matters to customers',
				],
				[
					'text'  => 'Building an honest map of a real journey across every channel and team involved, marking where customers wait, repeat themselves or have to chase. You will estimate what each of those moments costs in contacts, complaints and staff time, and choose one to redesign during the course.',
					'title' => 'Mapping a customer journey',
				],
				[
					'text'  => 'Which measures encourage good service and which encourage the wrong behaviour, with examples from contact centres, claims teams and customer relations. We look at handling-time targets, satisfaction scores and first-contact resolution in practice. You will draft a small set of measures for your own team and test them against real scenarios.',
					'title' => 'Measures that help',
				],
				[
					'text'  => 'Designing clear guidance that lets frontline staff resolve problems on the spot, including goodwill payments and sensible exceptions, with light checks after the event instead of approval before it. We cover how to agree limits with finance and risk colleagues, and how to review decisions without undermining the people who made them.',
					'title' => 'Authority to put things right',
				],
				[
					'text'  => 'Practical coaching techniques for team leaders, including listening to contacts alongside team members and giving specific, timely feedback. You will practise turning difficult cases into learning for the whole team, and leave with a simple weekly coaching routine you can start the following Monday.',
					'title' => 'Coaching for service',
				],
			],
			'modules_lead'     => 'Service Excellence is delivered through these modules:',
			'next_date'        => '2026-12-01',
			'price'            => '£1,650 plus VAT',
			'related'          => halveron_seed_pick( $post_ids, [ 'customer-journey-insurer', 'complaints-handling-utility', 'regulation-work-for-you' ] ),
			'schedule'         => 'Three consecutive days, 1 to 3 December 2026',
			'summary'          => 'Three days for managers who lead customer-facing teams. You will learn how to design a service around what matters to customers, set measures that drive the right behaviour, and coach your team to handle the unexpected with confidence.',
			'who_for'          => 'Managers and senior team leaders of customer-facing teams, including contact centres, branches, claims, customer relations and front-of-house services. It suits people who have led a team for at least a year.',
		],
		'title'   => 'Service Excellence',
	],
	'operational-coaching'   => [
		'content' => <<<'HTML'
			<p>Most team leaders are told to coach but are rarely shown how to do it in a busy operation. Operational Coaching is a practical course on coaching in the flow of work: at the desk, after a call, during a daily review or over video with a colleague working from home.</p>
			<p>Much of the two days is spent practising. You will coach and be coached using real situations from your own teams, with feedback from the trainer and from other delegates. You will also plan how to protect time for coaching in your week, which is usually the hardest part.</p>
			<p>The course is accredited by the Coaching Standards Council. Delegates who submit three recorded coaching conversations within eight weeks of the course receive a certificate.</p>
			HTML,
		'image'   => 'training-coaching.jpg',
		'meta'    => [
			'accreditation'    => 'Accredited by the Coaching Standards Council',
			'contact'          => $person_ids['tom-whitlock'],
			'course_type'      => 'open',
			'duration'         => 'Two days',
			'location'         => 'Woodstock, Oxfordshire',
			'meta_description' => 'Two days on coaching people while the work is happening, not just in one-to-ones.',
			'modules'          => [
				[
					'text'  => 'Why short, frequent coaching changes behaviour more than formal reviews, and what we have seen work in service operations. We look at how to fit ten-minute conversations into a working day without losing control of the operation, and how to choose which conversations matter when time is short.',
					'title' => 'Coaching in the flow of work',
				],
				[
					'text'  => 'Question techniques that help team members work out their own improvements, so the learning sticks. You will practise open questions, silence and summarising in pairs, then learn when to switch to direct, specific feedback because the situation or the customer needs it.',
					'title' => 'Asking before telling',
				],
				[
					'text'  => 'Using recorded contacts, case reviews and team measures as the basis for coaching, so conversations are about the work rather than about personality. We cover how to choose the right evidence, how to share it fairly and how to agree one thing to try differently next time.',
					'title' => 'Coaching from evidence',
				],
				[
					'text'  => "How to listen in, review work and hold coaching conversations over video without them feeling like surveillance. We share practical routines from hybrid contact centres and back offices, and look at how to make each person's progress visible when the team is rarely in the same place.",
					'title' => 'Coaching hybrid and remote teams',
				],
				[
					'text'  => 'Planning your week to protect around five hours for coaching, agreeing that time with your own manager and showing what it achieves. You will leave with a written coaching plan for your team and a short record you can use to track progress over the following eight weeks.',
					'title' => 'Making time for it',
				],
			],
			'modules_lead'     => 'Operational Coaching is delivered through these modules:',
			'next_date'        => '2027-03-09',
			'price'            => '£1,150 plus VAT',
			'related'          => halveron_seed_pick( $post_ids, [ 'coaching-hybrid-teams', 'public-sector-service-academy', 'back-office-productivity-bank' ] ),
			'schedule'         => 'Two consecutive days, 9 to 10 March 2027',
			'summary'          => 'Two days on coaching people while the work is happening, not just in one-to-ones. You will practise short, frequent coaching conversations that improve how your team handles customers, including team members who work from home.',
			'who_for'          => 'Team leaders, supervisors and quality coaches in operations of any size, including those who lead hybrid or fully remote teams. Lean Practitioner graduates often take this course next.',
		],
		'title'   => 'Operational Coaching',
	],
	'operations-management'  => [
		'content' => <<<'HTML'
			<p>Operations Management is a custom programme for organisations that want to build a consistent standard of operational management across their teams. Many managers are promoted because they know the work well and are then left to learn management by trial and error. This programme gives them a shared method and a shared language.</p>
			<p>We design the programme with you, using your own demand data, measures and processes. The six modules run roughly every two weeks, and between modules each manager applies what they have learned in their own team, with support from a Halveron coach.</p>
			<p>Managers who complete the programme and a workplace assignment can be assessed for certification at foundation level by the Service Operations Institute.</p>
			HTML,
		'image'   => 'training-workshop.jpg',
		'meta'    => [
			'accreditation'    => 'Can be assessed for Service Operations Institute certification at foundation level',
			'card_line'        => 'Dates and format by arrangement',
			'contact'          => $person_ids['tom-whitlock'],
			'course_type'      => 'custom',
			'duration'         => 'Six modules over twelve weeks',
			'location'         => 'On your premises or online',
			'meta_description' => 'A programme for new and developing operations managers in a single organisation.',
			'modules'          => [
				[
					'text'  => 'Choosing a small set of measures that show how the service is performing for customers, reading them properly and avoiding the targets that distort behaviour. We use your own reports as the starting point and test them against what customers say. Managers leave with a draft dashboard for their own team.',
					'title' => 'Performance and measures',
				],
				[
					'text'  => 'Running a short daily review, allocating work against agreed priorities and responding to the unexpected without abandoning the plan. We use your own volumes, staffing and service levels as the case material, so managers practise on the situations they will face the following morning, not on a textbook example.',
					'title' => 'Planning the day',
				],
				[
					'text'  => 'Forecasting demand by type and time of day, comparing it with the hours available and spotting a shortfall early enough to act on it. Together we build a simple planning tool from your own data, which your managers keep and update after the programme ends.',
					'title' => 'Demand and capacity',
				],
				[
					'text'  => 'Short, frequent coaching conversations that improve how team members handle their work, including those working from home. Managers practise listening to contacts or reviewing cases with a team member, asking before telling, and agreeing one specific thing to try differently. Each manager then coaches in their own team before the next module.',
					'title' => 'Coaching at the desk',
				],
				[
					'text'  => 'A simple, structured way for teams to find the root cause of a recurring problem, test a fix on a small scale and check that it worked. Each manager leads one problem-solving session with their own team between modules and brings the results back to the group.',
					'title' => 'Solving problems as a team',
				],
				[
					'text'  => "Shorter meetings with a clear purpose, a decision and an owner for each action. We also look at how managers spend their own week, and help each one protect time for coaching and improvement so it is not squeezed out by reporting and email. The module ends with a review of each manager's progress.",
					'title' => 'Running meetings that decide things',
				],
			],
			'modules_lead'     => 'Operations Management is delivered through these modules:',
			'related'          => halveron_seed_pick( $post_ids, [ 'back-office-productivity-bank', 'start-with-demand', 'coaching-hybrid-teams' ] ),
			'summary'          => 'A programme for new and developing operations managers in a single organisation. It covers planning, performance, coaching and problem-solving, using your own data and processes as the material for every module.',
			'who_for'          => 'Newly appointed and developing operations managers and senior team leaders, in groups of eight to sixteen from the same organisation. It works well when their own managers attend a short briefing at the start and the final review at the end.',
		],
		'title'   => 'Operations Management',
	],
	'stakeholder-engagement' => [
		'content' => <<<'HTML'
			<p>Most change does not fail on its technical merits. It fails because the people who need to support it were not involved, were involved too late or were never told what would change for them. Stakeholder Engagement is a custom course for teams leading change projects who need to bring colleagues, senior leaders, unions, partners or elected members with them.</p>
			<p>The course is built around delegates' own projects. Each delegate maps the people who matter, identifies what each of them needs to hear and plans the conversations ahead. We use realistic practice, including difficult meetings, with feedback from the trainer and the group.</p>
			<p>The course is credit-rated by Brackenfield Business School, so delegates can count it towards further study.</p>
			HTML,
		'image'   => 'training-classroom.jpg',
		'meta'    => [
			'accreditation'    => 'Credit-rated by Brackenfield Business School',
			'card_line'        => 'Dates and format by arrangement',
			'contact'          => $person_ids['tom-whitlock'],
			'course_type'      => 'custom',
			'duration'         => 'Two days, or four half-day sessions',
			'location'         => 'On your premises or online',
			'meta_description' => 'A practical course for people leading change who need others to agree, fund or adopt it.',
			'modules'          => [
				[
					'text'  => "Identifying everyone who can affect or is affected by your project, what each of them cares about and how much influence they have over the outcome. Delegates build a map of their own project's stakeholders and agree with the group where their effort is needed first in the coming months.",
					'title' => 'Mapping the people who matter',
				],
				[
					'text'  => 'Explaining a change in terms each audience understands, from a board paper to a five-minute team briefing. We look at how to describe what will change for each group, and how to be honest about what will be harder before it gets easier. Delegates draft and test a short message for one audience.',
					'title' => 'Making the case',
				],
				[
					'text'  => 'Handling resistance, disagreement and bad news calmly and fairly. Delegates practise in realistic scenarios drawn from their own projects, including meetings with senior leaders, staff representatives and partner organisations, with feedback from the trainer and the group on what helped and what made the conversation harder.',
					'title' => 'Difficult conversations',
				],
				[
					'text'  => 'Planning regular contact through the life of a project, spotting the early signs that support is fading and acting before it turns into opposition. We cover how to involve people in the design itself, so the change becomes theirs as well as yours, and how to hand over relationships when the project team moves on.',
					'title' => 'Keeping support over time',
				],
			],
			'modules_lead'     => 'Stakeholder Engagement is delivered through these modules:',
			'related'          => halveron_seed_pick( $post_ids, [ 'change-delivery-council', 'regulation-work-for-you', 'public-sector-service-academy' ] ),
			'summary'          => 'A practical course for people leading change who need others to agree, fund or adopt it. Delegates map the people who matter to their own project and plan how to win and keep their support.',
			'who_for'          => 'Project and programme leads, change practitioners, transformation teams and managers about to lead a significant change, in groups of six to fourteen from the same organisation.',
		],
		'title'   => 'Stakeholder Engagement',
	],
];

$course_ids      = [];
$created_courses = 0;
$position        = 0;

foreach ( $courses as $slug => $course ) {
	[ $course_ids[ $slug ], $created ] = halveron_seed_post( 'course', $slug, [ 'menu_order' => ++$position, 'post_content' => $course['content'], 'post_title' => $course['title'] ], $course['meta'], $img[ $course['image'] ] );
	$created_courses += $created;
}

echo "courses: {$created_courses} created, " . count( $course_ids ) . " total\n";

$events = [
	'forum-november-2026' => [
		'content' => '',
		'meta'    => [
			'agenda'            => [
				[ 'item' => 'Arrival, coffee and introductions', 'time' => '09:30' ],
				[ 'item' => "Welcome from Northwyn Rail's director of customer operations, and a members' round of news", 'time' => '10:00' ],
				[ 'item' => 'Tour of the control centre and customer information desk, in two groups', 'time' => '10:30' ],
				[ 'item' => 'Case session: telling passengers the truth early, and what Northwyn changed after a winter of disruption', 'time' => '12:00' ],
				[ 'item' => 'Lunch', 'time' => '13:00' ],
				[ 'item' => 'Peer clinic in small groups, with a summary of actions before we close at 15:00', 'time' => '13:45' ],
			],
			'event_date'        => '2026-11-19',
			'host_organisation' => $organisation_ids['northwyn-rail'],
			'meta_description'  => "Members visit Northwyn Rail's control centre to see how it keeps passengers informed when the timetable fails, followed by a case session and peer clinic.",
			'registration'      => '<p>Places are for forum members. To register, email <a href="mailto:forum@halveron.junaid.guru">forum@halveron.junaid.guru</a> by Thursday 5 November 2026 with the names of up to two attendees.</p>',
			'summary'           => "Members visit Northwyn Rail's control centre to see how it keeps passengers informed when the timetable fails, followed by a case session and peer clinic.",
			'time'              => '09:30 to 15:00',
			'venue'             => "Northwyn Rail's customer information and control centre, Manchester: a modern office building a short walk from Manchester Piccadilly station. Full directions are sent to registered members.",
		],
		'title'   => 'Lean Service Forum at Northwyn Rail',
	],
	'forum-june-2026'     => [
		'content' => <<<'HTML'
			<p>Thirty-four members from 27 organisations joined us at Vellmore Building Society's operations centre in Bristol. It was the society's first time as host, and a week later it became the forum's hundredth member.</p>
			<p>The morning began on the mortgage operations floor. Two years ago a typical application passed through eleven handoffs between brokers, underwriters, valuers and administrators, and brokers chased the society on almost half of all cases. Vellmore's operations director explained how her team mapped the journey with brokers in the room, then reorganised underwriters and administrators into small teams that own an application from start to offer. Handoffs fell to four, and the average time from application to offer dropped from 19 days to 11.</p>
			<p>Members were most interested in what had not worked. An early attempt to automate broker updates had produced more calls, not fewer, because the messages described internal stages that meant nothing to brokers. The society rewrote them around the questions brokers actually asked.</p>
			<p>In the afternoon peer clinic, members brought six live problems. The one that drew the longest discussion came from a university admissions team facing a surge in clearing enquiries, and three members who run seasonal peaks offered to visit.</p>
			<p>Thank you to Vellmore Building Society for hosting, and to everyone who brought a problem to the clinic.</p>
			HTML,
		'meta'    => [
			'event_date'        => '2026-06-18',
			'host_organisation' => $organisation_ids['vellmore-building-society'],
			'meta_description'  => 'Thirty-four members spent the day at Vellmore Building Society in Bristol, seeing how the society cut handoffs in its mortgage applications from eleven to four.',
			'summary'           => 'Thirty-four members spent the day at Vellmore Building Society in Bristol, seeing how the society cut handoffs in its mortgage applications from eleven to four.',
			'venue'             => "Vellmore Building Society's mortgage and savings operations centre, on a business park on the edge of Bristol.",
		],
		'title'   => 'Lean Service Forum at Vellmore Building Society',
	],
	'forum-march-2026'    => [
		'content' => <<<'HTML'
			<p>Forty members from 31 organisations spent the day at Calderbrook Water's contact and field-scheduling centre in Leeds.</p>
			<p>The morning ran as two parallel sessions. One group sat with call handlers as they booked appointments for leak investigations and meter repairs. The other spent time with the field-scheduling team, who plan the work of around 300 engineers. The groups then swapped.</p>
			<p>The point of the exercise became clear at the case session. Until last year the two teams worked in separate buildings and reported to different directors. Call handlers booked appointments in slots the schedulers had not planned for, and schedulers moved jobs without telling the customer. Missed or rearranged appointments were one of the two main causes of complaints.</p>
			<p>Calderbrook moved the teams onto one floor, gave them a shared morning review and agreed a single measure: appointments kept as promised. Over nine months missed appointments fell by a third, and calls from customers asking where their engineer was fell by 40%.</p>
			<p>Several members admitted to the same split in their own organisations, between contact centres and branch, claims or field teams. The peer clinic that followed focused on how to make the case for joint planning when the two teams answer to different parts of the business.</p>
			<p>Thank you to Calderbrook Water and its teams for opening their doors.</p>
			HTML,
		'meta'    => [
			'event_date'        => '2026-03-12',
			'host_organisation' => $organisation_ids['calderbrook-water'],
			'meta_description'  => 'Members visited Calderbrook Water in Leeds to see how its contact centre and field-scheduling teams now plan together, cutting missed appointments by a third.',
			'summary'           => 'Members visited Calderbrook Water in Leeds to see how its contact centre and field-scheduling teams now plan together, cutting missed appointments by a third.',
			'venue'             => "Calderbrook Water's customer contact and field-scheduling centre in Leeds.",
		],
		'title'   => 'Lean Service Forum at Calderbrook Water',
	],
];

$event_ids      = [];
$created_events = 0;

foreach ( $events as $slug => $event ) {
	[ $event_ids[ $slug ], $created ] = halveron_seed_post( 'event', $slug, [ 'post_content' => $event['content'], 'post_title' => $event['title'] ], $event['meta'], $img[ "event-{$slug}.jpg" ] );
	$created_events += $created;
}

echo "events: {$created_events} created, " . count( $event_ids ) . " total\n";

$jobs = [
	'senior-consultant-operations'         => [
		'meta'  => [
			'job_type'         => 'permanent',
			'location'         => 'UK-wide, home-based with regular client travel',
			'responsibilities' => [
				[ 'text' => 'Lead demand, flow and capacity analysis with joint client and Halveron teams' ],
				[ 'text' => 'Design and test new processes, roles and daily management routines' ],
				[ 'text' => 'Coach client managers and team leaders through the change' ],
				[ 'text' => 'Write and present findings and recommendations to senior client audiences' ],
				[ 'text' => 'Mentor consultants and contribute to proposals and our published thinking' ],
			],
			'summary'          => 'Lead workstreams on operations and process-improvement projects for clients in financial services, utilities and the public sector. You will run demand studies, design new ways of working with client teams and coach their managers to sustain them. You will report to a principal consultant and work directly with client operations directors, typically on one or two projects at a time.',
			'you_bring'        => [
				[ 'text' => 'At least eight years in service operations, including several in a management or improvement role' ],
				[ 'text' => 'Practical experience of lean, systems thinking or a similar improvement method' ],
				[ 'text' => 'Confidence with operational data and simple capacity models' ],
				[ 'text' => 'Clear writing and the ability to facilitate a room of senior managers' ],
				[ 'text' => 'A full UK driving licence or a willingness to travel extensively by rail' ],
			],
		],
		'title' => 'Senior Consultant, Operations',
	],
	'consultant-customer-experience'       => [
		'meta'  => [
			'job_type'         => 'permanent',
			'location'         => 'Woodstock, Oxfordshire, or home-based in the UK, with client travel',
			'responsibilities' => [
				[ 'text' => 'Analyse recorded contacts, complaints and journey data to find the causes of repeat contact' ],
				[ 'text' => 'Map end-to-end customer journeys with client teams across channels' ],
				[ 'text' => 'Help design and test improvements, including letters, scripts and handoffs' ],
				[ 'text' => 'Prepare clear reports and short presentations for client managers' ],
				[ 'text' => 'Support our training team on Service Excellence courses' ],
			],
			'summary'          => "Work in Anjali Mehta's customer experience team on journey mapping, complaints and contact-reduction projects. You will listen to customer contacts, analyse why customers get in touch, and help client teams redesign the journeys that cause repeat contact. Most projects are in insurance, banking, utilities and travel, and last between three and nine months.",
			'you_bring'        => [
				[ 'text' => 'Three to five years in customer service, customer experience or operations' ],
				[ 'text' => 'Experience of analysing customer feedback or contact data' ],
				[ 'text' => 'Plain, careful writing and a good ear for what customers actually say' ],
				[ 'text' => 'Comfort working on an operations floor alongside frontline staff' ],
				[ 'text' => 'Curiosity about why processes break, and patience in fixing them' ],
			],
		],
		'title' => 'Consultant, Customer Experience',
	],
	'senior-consultant-digital-operations' => [
		'meta'  => [
			'job_type'         => 'permanent',
			'location'         => 'UK-wide, home-based with regular client travel',
			'responsibilities' => [
				[ 'text' => 'Lead short discovery phases that combine process analysis and user research' ],
				[ 'text' => 'Prototype and test digital services with customers and frontline staff' ],
				[ 'text' => 'Write clear requirements and work closely with client developers and suppliers' ],
				[ 'text' => 'Measure digital channels on resolution and effort, not just usage' ],
				[ 'text' => 'Help shape our view of technology in service operations through client work and white papers' ],
			],
			'summary'          => "Join Grace Okonkwo's digital team to help clients redesign work before they automate it. You will lead discovery work, prototype digital services with real customers and work with client technology teams and our technology partners to deliver change in weeks rather than years. Projects span contact-centre technology, self-service and workflow automation.",
			'you_bring'        => [
				[
					'text' => 'At least six years in digital delivery, service design or business analysis in a service organisation',
				],
				[ 'text' => 'Experience of agile delivery and of testing services with real users' ],
				[ 'text' => 'Working knowledge of contact-centre platforms, workflow tools or low-code automation' ],
				[ 'text' => 'The judgement to recommend not building something when a process change will do' ],
				[ 'text' => 'Confidence explaining technical choices to non-technical leaders' ],
			],
		],
		'title' => 'Senior Consultant, Digital Operations',
	],
	'associate-consultant-public-sector'   => [
		'meta'  => [
			'job_type'         => 'associate',
			'location'         => 'UK-wide, flexible',
			'responsibilities' => [
				[ 'text' => 'Lead or support improvement and change-delivery projects in public services' ],
				[ 'text' => 'Coach academy delegates through their workplace projects' ],
				[ 'text' => 'Facilitate workshops with officers, staff representatives and elected members' ],
				[ 'text' => 'Train client staff as change practitioners using our methods' ],
				[ 'text' => 'Share what you learn with the wider Halveron team' ],
			],
			'summary'          => "Work with Daniel Fry's public-sector team as an associate on projects for councils, housing associations and government agencies, and as a tutor and coach on the Halveron Service Academy. Associates work on agreed assignments, typically between 40 and 100 days a year, and are paid a day rate. We look for a lasting relationship, not one-off engagements.",
			'you_bring'        => [
				[ 'text' => 'Senior experience leading service delivery or transformation in the public sector' ],
				[ 'text' => 'Practical experience of demand-led or lean approaches in public services' ],
				[ 'text' => 'Credibility with chief officers and elected members' ],
				[ 'text' => 'Coaching experience, ideally with a recognised qualification' ],
				[ 'text' => 'Your own limited company or self-employed status, and professional indemnity insurance' ],
			],
		],
		'title' => 'Associate Consultant, Public Sector',
	],
	'trainer-and-learning-designer'        => [
		'meta'  => [
			'job_type'         => 'permanent',
			'location'         => 'Woodstock, Oxfordshire, at least three days a week, with some travel to clients',
			'responsibilities' => [
				[ 'text' => 'Deliver Lean Practitioner, Service Excellence and Operational Coaching courses' ],
				[ 'text' => 'Design custom programmes with client learning and operations teams' ],
				[ 'text' => 'Assess delegate projects and manage accreditation records' ],
				[ 'text' => 'Develop course materials, exercises and online learning' ],
				[ 'text' => 'Collect and report on delegate outcomes three months after each course' ],
			],
			'summary'          => "Join Tom Whitlock's learning and development team to design and deliver our accredited courses. You will teach open courses at our Woodstock training centre and online, design custom programmes with clients, and keep our materials aligned with the standards of our accrediting bodies. You will also support assessment and certification for delegates.",
			'you_bring'        => [
				[ 'text' => 'At least five years designing and delivering training for adults in a workplace setting' ],
				[ 'text' => 'Experience of service operations, ideally as a manager or improvement specialist' ],
				[ 'text' => 'A recognised training or coaching qualification' ],
				[ 'text' => 'The ability to make technical methods practical and engaging' ],
				[ 'text' => 'Careful organisation and attention to detail in assessment and records' ],
			],
		],
		'title' => 'Trainer and Learning Designer',
	],
	'graduate-analyst-2027'                => [
		'meta'  => [
			'job_type'         => 'graduate',
			'location'         => 'Woodstock, Oxfordshire, and client sites across the UK',
			'note'             => 'Graduate applications close on Friday 15 January 2027. Interviews and assessment days take place in February 2027.',
			'responsibilities' => [
				[ 'text' => 'Collect and analyse demand, process and performance data on client projects' ],
				[ 'text' => 'Observe and document how work is done on operations floors' ],
				[ 'text' => 'Help prepare workshops, reports and presentations' ],
				[ 'text' => 'Complete our foundation programme and practitioner certification' ],
				[ 'text' => 'Contribute to our research, including the annual contact-centre demand report' ],
			],
			'summary'          => 'Begin a consulting career on our two-year graduate programme, starting in September 2027. You will join client teams from your first month, analysing demand and process data, supporting workshops and writing up findings. You will complete our foundation programme, work towards practitioner certification and rotate across at least three sectors, with a development manager throughout.',
			'you_bring'        => [
				[ 'text' => 'A degree, or expected degree, at 2:1 or above in any subject, finishing by summer 2027' ],
				[ 'text' => 'Confidence with numbers and spreadsheets' ],
				[ 'text' => 'Clear writing and an interest in how organisations work' ],
				[ 'text' => 'Some experience of customer-facing or operational work, paid or voluntary' ],
				[ 'text' => 'Willingness to travel and to stay away from home during parts of projects' ],
			],
		],
		'title' => 'Graduate Analyst, September 2027 intake',
	],
];

$job_ids      = [];
$created_jobs = 0;
$position     = 0;

foreach ( $jobs as $slug => $job ) {
	[ $job_ids[ $slug ], $created ] = halveron_seed_post( 'job', $slug, [ 'menu_order' => ++$position, 'post_title' => $job['title'] ], $job['meta'] );
	$created_jobs += $created;
}

echo "jobs: {$created_jobs} created, " . count( $job_ids ) . " total\n";

$options = [
	'address'                          => "Halveron Consulting Ltd\nThe Granary\n9 Mill Lane\nWoodstock\nOxfordshire\nOX20 0XX",
	'capabilities_archive_description' => 'Eight ways Halveron helps UK service organisations improve operations and customer experience, from operating model design to digital services and training.',
	'capabilities_archive_intro'       => 'Halveron is a consultancy that does one thing: we help service organisations run their operations well. Our consultants have managed contact centres, claims teams, back offices and frontline services themselves, so we know the difference between a plan that reads well and one that works on a wet Monday in January. We bring the same few disciplines to every engagement: start with customer demand, design the work before the technology, and leave your people able to carry on without us.',
	'capabilities_archive_title'       => 'Our capabilities',
	'capabilities_wheel_description'   => 'A wheel of eight capabilities around one method: business transformation, operations strategy and implementation, operating model design, organisational effectiveness, multichannel customer experience, process improvement, digital at pace, and learning and development.',
	'capabilities_wheel_hub'           => "The Halveron\nmethod",
	'capabilities_wheel_title'         => 'The Halveron method',
	'careers_email'                    => 'careers@halveron.junaid.guru',
	'company_number'                   => '00000000',
	'courses_archive_description'      => 'We run three accredited open courses through the year and two programmes designed for a single organisation.',
	'courses_archive_intro'            => 'We run three accredited open courses through the year and two programmes designed for a single organisation. Every course is practical: delegates bring a real problem from work and leave with a plan to solve it. Choose a course to see the modules, dates and price, or contact us to arrange training for your team.',
	'courses_archive_tabs'             => [
		[ 'current' => false, 'label' => 'About', 'url' => '/training/#about' ],
		[ 'current' => true, 'label' => 'Open training', 'url' => '/courses/#open-training' ],
		[ 'current' => false, 'label' => 'Custom training', 'url' => '/courses/#custom-training' ],
	],
	'courses_archive_title'            => 'Courses',
	'courses_enquiries_heading'        => 'Learning and development enquiries',
	'courses_enquiries_text'           => 'Tom Whitlock and the training team can help with dates, group bookings, accessibility needs and courses for your own organisation.',
	'download_heading'                 => 'Download the paper',
	'download_intro'                   => 'Tell us a little about yourself and we will send {title} to your inbox.',
	'download_note'                    => 'This is a demonstration form. Please do not enter real personal details.',
	'download_privacy'                 => '<p>We use these details to send you the paper and, if you ask, related insights. We never share them, and you can unsubscribe at any time. Read our <a href="/privacy/">privacy notice</a>.</p>',
	'email'                            => 'hello@halveron.junaid.guru',
	'events_email'                     => 'events@halveron.junaid.guru',
	'footer_note'                      => 'This site sets no cookies.',
	'footer_notice'                    => 'Halveron Consulting is a fictional firm created for this demonstration website. People, clients, case studies and events are invented; any resemblance to real organisations is coincidental.',
	'form_note'                        => 'This is a demonstration website for a fictional firm. Please do not enter real personal information. Messages sent from this form are not stored or read.',
	'forum_email'                      => 'forum@halveron.junaid.guru',
	'forum_panel_button_label'         => 'Visit the Forum',
	'forum_panel_heading'              => 'Halveron Forum',
	'forum_panel_text'                 => "The Lean Service Forum brings operations leaders from more than 100 organisations together four times a year to compare notes and visit each other's operations. Members share what has worked, what has not, and why.",
	'insight_disclaimer'               => "This article reflects the author's view on the date of publication. It is general information, not advice for your organisation.",
	'legal_name'                       => 'Halveron Consulting Ltd',
	'newsletter_heading'               => 'Sign up for the Halveron newsletter',
	'newsletter_hint'                  => 'This is a demonstration site. Your email address is not sent or stored.',
	'newsletter_text'                  => 'A short monthly note on service operations: what we are seeing with clients, new research, and dates for courses and forum meetings.',
	'phone'                            => '+44 (0)1632 960410',
	'phone_href'                       => '+441632960410',
	'search_description'               => 'Search every page, article, course and event on the Halveron Consulting website.',
	'sectors_archive_description'      => 'Halveron works with banks, insurers, utilities, travel operators, universities, public bodies, health providers and outsourcers across the UK.',
	'sectors_archive_intro'            => "We work only in service organisations, where the product is the experience and most of the cost is people's time. Across eight sectors the pressures differ, from regulators and weather to admissions and timetables, but the underlying problems are familiar: demand nobody planned for, work that loops back, and customers who have to ask twice. That shared pattern lets us bring lessons from one sector to another.",
	'sectors_archive_title'            => 'Our sectors',
	'social'                           => [
		[ 'icon' => 'linkedin', 'label' => 'Halveron on LinkedIn', 'url' => 'https://www.linkedin.com/' ],
		[ 'icon' => 'x', 'label' => 'Halveron on X', 'url' => 'https://x.com/' ],
		[ 'icon' => 'youtube', 'label' => 'Halveron on YouTube', 'url' => 'https://www.youtube.com/' ],
	],
	'training_email'                   => 'training@halveron.junaid.guru',
	'training_phone'                   => '+44 (0)1632 960415',
	'training_phone_href'              => '+441632960415',
];

foreach ( $options as $name => $value ) {
	update_option( $name, $value );
}

echo 'options: ' . count( $options ) . " set\n";

$pages = [
	'home'                  => [
		'meta'      => [
			'home_capabilities_default' => $capability_ids['operating-model-design'],
			'home_capabilities_heading' => 'Discover our capabilities',
			'home_clients_button_label' => 'See what we have achieved for them',
			'home_clients_button_url'   => '/thinking/#case-studies',
			'home_clients_heading'      => 'Some of the organisations we work with',
			'home_custom_button_label'  => 'Talk to us about custom training',
			'home_custom_button_url'    => '/training/#custom-training',
			'home_custom_text'          => 'We also design programmes for a single organisation, built around your processes, language and goals. Courses can run on your premises, online or as coaching alongside live work.',
			'home_feature_cards'        => [
				[
					'button_label' => 'Explore process improvement',
					'button_url'   => '/capabilities/process-improvement/',
					'image'        => $img['cap-process-improvement.jpg'],
					'text'         => 'Up to a third of the work in a typical service operation exists only to correct earlier mistakes. We help you find it, measure it and design it out for good.',
					'title'        => 'Lower costs by removing the work nobody wanted',
				],
				[
					'button_label' => 'Find out how we did it',
					'button_url'   => '/thinking/complaints-handling-utility/',
					'image'        => $img['insight-complaints-handling-utility.jpg'],
					'text'         => 'Complaints were rising and taking weeks to close. We worked with frontline teams to fix the causes at source and resolve most cases at first contact. Read the full case study.',
					'title'        => 'Fewer complaints, resolved sooner, at a water utility',
				],
				[
					'button_label' => 'Download the white paper',
					'button_url'   => '/thinking/technology-in-travel/#download',
					'image'        => $img['insight-technology-in-travel.jpg'],
					'text'         => 'Our sector review looks at where automation, self-service and better data have helped travel operators, and where they simply moved the work somewhere else. Download the white paper free.',
					'title'        => 'What technology really changes in travel operations',
				],
			],
			'home_hero_button_label'    => 'See how we work',
			'home_hero_button_url'      => '/capabilities/',
			'home_hero_line_1'          => 'Serve well.',
			'home_hero_line_2'          => 'Run well.',
			'home_hero_text'            => 'At Halveron we start where your customers start: with the reasons they contact you.',
			'home_intro_heading'        => 'We help service organisations do the right work, once, and do it well.',
			'home_intro_text'           => 'Halveron is an operations and customer-experience consultancy for banks, insurers, utilities, travel operators, universities, public bodies and health providers. We study the demand that reaches you, find the work that adds nothing for your customers, and redesign how teams, processes and technology fit together. Then we stay long enough to make the change stick, training your own people so the improvement carries on after we leave.',
			'home_thinking_heading'     => 'Halveron Thinking',
			'home_thinking_posts'       => halveron_seed_pick( $post_ids, [ 'future-of-service-2026', 'customer-journey-insurer', 'regulation-work-for-you' ] ),
			'home_training_courses'     => halveron_seed_pick( $course_ids, [ 'lean-practitioner', 'service-excellence' ] ),
			'home_training_heading'     => 'Halveron training courses',
			'home_training_intro'       => 'Our practitioner courses are accredited by the Service Operations Institute and the Centre for Customer Practice, and are taught by consultants who use the methods with clients every week. Delegates work on problems from their own organisations, so they return with a plan as well as a certificate. Courses run in Oxfordshire and online.',
			'meta_description'          => 'Halveron Consulting helps UK service organisations run simpler operations and give customers a better experience, through consulting, accredited training and a peer forum for operations leaders.',
		],
		'thumbnail' => 'hero-home.jpg',
		'title'     => 'Home',
	],
	'thinking'              => [
		'meta'  => [
			'intro'             => 'What we learn on client work, written down while it is still useful. Our news covers the firm, the Lean Service Forum and our conference. Case studies show what changed for real organisations, with the names removed. White papers go deeper on one question, and the In brief notes fit a single idea on a page.',
			'meta_description'  => 'What we learn on client work, written down while it is still useful. Our news covers the firm, the Lean Service Forum and our conference.',
			'thinking_featured' => halveron_seed_pick( $post_ids, [ 'future-of-service-2026', 'customer-journey-insurer', 'regulation-work-for-you', 'start-with-demand' ] ),
		],
		'title' => 'Our Thinking',
	],
	'news'                  => [
		'meta'  => [
			'intro'            => 'Every article, case study, white paper and In brief note we have published, newest first. Use the tabs to show one type at a time, or read straight down the list.',
			'meta_description' => 'Every article, case study, white paper and In brief note we have published, newest first.',
		],
		'title' => 'News and insights',
	],
	'about'                 => [
		'meta'  => [
			'about_advisory_heading'   => 'Advisory panel',
			'about_advisory_intro'     => 'Our advisory panel brings independent experience from research, financial services and digital operations. Panel members challenge our thinking, review our methods and speak at the Future of Service Conference each year.',
			'about_csr_button_label'   => 'Ask about our community work',
			'about_csr_button_url'     => '/contact/',
			'about_csr_heading'        => 'Corporate social responsibility',
			'about_csr_text'           => '<p>Each consultant at Halveron gives four working days a year to charities and community organisations, using the same skills our clients pay for. In the last year that came to more than 250 days, spent helping food banks organise their volunteers, a hospice redesign its referral process and two advice charities cut waiting times for their helplines. We offer free places on our open courses to staff from small charities, and our head office runs on renewable electricity. We publish our carbon footprint each year and are working to halve business travel emissions by 2030. If you lead a charity that could use our help, we would like to hear from you.</p>',
			'about_intro_column_1'     => '<p>Halveron was founded in 2009 by a small group of operations managers who had spent their careers running service businesses and wanted to help others run them better. Our aim is simple: service that works for customers and costs less to deliver. Today we are a team of 70 consultants, tutors and associates, working from our head office in Woodstock, Oxfordshire, and on site with clients across the UK. We have completed more than 400 engagements, trained over 9,000 delegates and accredited around 1,800 practitioners through our courses, which are recognised by the Service Operations Institute and the Centre for Customer Practice. We remain independent and owned by the people who work here.</p>',
			'about_intro_column_2'     => '<p>We recruit people who have done the job before they advise on it. Most of our consultants have led contact centres, claims teams, back offices or public services, and every one of them can sit beside a frontline colleague and understand the work. We look for curiosity, plain speaking and the patience to let clients find answers for themselves. We work across banking, insurance, utilities, travel, education, the public sector, healthcare and managed services, and we are a corporate member of the Service Operations Institute and the Association of Service Improvement Practitioners. Clients tell us we are straightforward to work with, and we work hard to keep it that way.</p>',
			'about_intro_heading'      => 'Who we are and how we work',
			'about_management_heading' => 'Management team',
			'about_management_intro'   => 'These are the people who lead our consulting, training and client work. Hover over or focus on each portrait to read a line on how they work.',
			'about_values'             => [
				[
					'text'  => 'Every piece of work begins with what customers actually ask for and why. Demand tells us more about an operation than any organisation chart, and it keeps everyone honest about what matters.',
					'title' => 'Start with demand',
				],
				[
					'text'  => 'We tell clients what we see, including things they may not want to hear. Clear language, honest numbers and no jargon make for better decisions and fewer surprises later.',
					'title' => 'Say it plainly',
				],
				[
					'text'  => 'We do the work with your people, not to them. Change designed by the teams who live with it lasts longer and costs less to sustain once we have gone.',
					'title' => 'Work alongside',
				],
				[
					'text'  => 'We measure success by what remains when we leave: better service, lower cost and people inside the organisation who can keep improving it without us.',
					'title' => 'Leave it better',
				],
			],
			'about_values_heading'     => 'Our values',
			'meta_description'         => 'Halveron is an independent operations and customer-experience consultancy based in Oxfordshire, working with service organisations across the UK since 2009.',
		],
		'title' => 'About us',
	],
	'contact'               => [
		'meta'  => [
			'contact_address_note'       => 'This address is fictional, like the firm.',
			'contact_directions'         => [
				[
					'text'  => <<<'HTML'
						<p>Woodstock is on the A44 north of Oxford. Approximate distances by road:</p>
						<ul>
						<li>Oxford, 8 miles</li>
						<li>Witney, 9 miles</li>
						<li>Bicester, 13 miles</li>
						<li>Banbury, 21 miles</li>
						<li>London, 65 miles</li>
						</ul>
						<p>There is free parking for visitors behind the building. Please let us know if you need an accessible space.</p>
						HTML,
					'title' => 'By car',
				],
				[
					'text'  => '<p>The nearest station is Hanborough, about 70 minutes from London Paddington. From Hanborough it is a ten-minute taxi ride to Woodstock. Oxford Parkway, with trains from London Marylebone, is about fifteen minutes away by taxi. We are happy to collect visitors from Hanborough by arrangement.</p>',
					'title' => 'By train',
				],
			],
			'contact_directions_heading' => 'How to find us',
			'contact_form_intro'         => 'Please complete the form and we will reply within two working days.',
			'contact_map'                => $img['map-woodstock.jpg'],
			'contact_office_heading'     => 'Head office',
			'contact_thank_you_heading'  => 'Thank you for your message',
			'contact_thank_you_text'     => 'On a live site we would now reply within two working days. As this is a demonstration, nothing has been sent or stored.',
			'intro'                      => 'Whether you have a specific problem in mind or want to talk through an idea, the quickest way to reach us is by phone or email. We aim to reply to every enquiry within two working days.',
			'meta_description'           => 'Contact Halveron Consulting by phone, email or the form on this page. Our head office is in Woodstock, Oxfordshire.',
		],
		'title' => 'Contact us',
	],
	'training'              => [
		'meta'      => [
			'meta_description'             => 'Practical, accredited training for the people who run service operations, taught by consultants who use the methods every week.',
			'news_heading'                 => 'Latest training news',
			'news_posts'                   => halveron_seed_pick( $post_ids, [ 'public-sector-service-academy', 'coaching-hybrid-teams', 'change-delivery-council' ] ),
			'section_tabs'                 => [
				[ 'label' => 'About', 'url' => '#about' ],
				[ 'label' => 'Open training', 'url' => '#open-training' ],
				[ 'label' => 'Custom training', 'url' => '#custom-training' ],
			],
			'section_title'                => 'Halveron Learning & Development',
			'statement'                    => 'Practical, accredited training for the people who run service operations, taught by consultants who use the methods every week.',
			'training_clients_heading'     => 'Organisations we have trained',
			'training_courses_heading'     => 'Halveron training courses',
			'training_courses_intro'       => 'Our open courses follow the Service Operations Competency Framework, set by the Service Operations Institute, which describes what a practitioner, a manager and a coach in a service operation should be able to do and how each level is assessed. Our service design content is accredited by the Centre for Customer Practice and our coaching course by the Coaching Standards Council. Every course is taught by a consultant who uses the methods with clients, and courses run in Oxfordshire and online.',
			'training_custom_button_label' => 'Talk to us about custom training',
			'training_custom_button_url'   => 'mailto:' . get_option( 'training_email' ),
			'training_custom_list'         => [
				[ 'text' => 'Operations management' ],
				[ 'text' => 'Stakeholder engagement' ],
				[ 'text' => 'Lean in the office' ],
				[ 'text' => 'Coaching for team leaders' ],
			],
			'training_custom_text'         => "We also design programmes for a single organisation, built around your processes, language and goals. Courses can run on your premises, online or as coaching alongside live work, and most can be assessed for accreditation. We usually start with a conversation about what your managers need to do differently in six months' time.",
			'training_facts_heading'       => 'Our training facts',
			'training_facts_intro'         => 'We measure every course against what delegates do afterwards, not only how they rated the day. These figures come from our open and in-house courses, and from feedback collected three months after each course ends.',
			'training_image'               => $img['training-workshop.jpg'],
			'training_image_caption'       => "We run courses in Oxfordshire, online and on clients' premises across the UK.",
			'training_open_text'           => 'Open courses are scheduled through the year for delegates from any organisation. Mixing sectors is part of the value: a claims manager and a council housing officer often find they share the same problem. Places are limited to fourteen per course.',
			'training_partners_heading'    => 'Accrediting and academic partners',
			'training_quote'               => "I went back to work with a demand study half finished and my director's backing to complete it. Within a month we had halved our list of overdue callbacks.",
			'training_quote_cite'          => 'Claims team leader, Lean Practitioner delegate, 2026',
			'training_stats'               => [
				[ 'figure' => '94%', 'label' => 'of 2025 delegates said the course objectives were fully met' ],
				[ 'figure' => '2,600', 'label' => 'delegates trained on open and in-house courses since 2021' ],
				[ 'figure' => '86%', 'label' => 'average assessment score for Lean Practitioner delegates in 2025' ],
				[ 'figure' => '140', 'label' => 'in-house programmes delivered for client organisations since 2021' ],
			],
		],
		'thumbnail' => 'training-hero.jpg',
		'title'     => 'Learning & Development',
	],
	'careers'               => [
		'meta'      => [
			'careers_aside_button_label' => 'Contact our careers team',
			'careers_aside_heading'      => 'Start the conversation',
			'careers_aside_subheading'   => 'Interested in joining us?',
			'careers_aside_text'         => <<<'HTML'
				<p>We are growing steadily and are always glad to hear from people who could strengthen our delivery teams. Most of the people we hire fall into one of three groups:</p>
				<ul>
				<li>Experienced operations and improvement specialists looking for a long-term consulting career</li>
				<li>Operations managers ready to move into consulting after several years leading service teams</li>
				<li>Independent consultants looking for a lasting associate relationship</li>
				</ul>
				HTML,
			'careers_looking_heading'    => "What we're looking for",
			'careers_looking_text'       => '<p>We hire people who have run service operations, not only advised on them. You will understand operations management, ideally in the service sector, and be comfortable with numbers: demand, capacity, cost and time. You will be able to sit with a frontline team in the morning and a board in the afternoon and be taken seriously by both. Above all, you will be curious about why work happens the way it does, and patient enough to help people change it themselves.</p>',
			'careers_schools_heading'    => 'Business schools and graduates',
			'careers_schools_link_label' => 'Email our careers team',
			'careers_schools_text'       => "<p>We recruit a small graduate intake each September, usually four to six analysts, from business, engineering, operations management and social science degrees. Graduates join client teams from their first month, complete our foundation programme and work towards practitioner certification within two years. We also take a few master's students each summer for ten-week placements on live projects. If your school or society would like us to run a case-study workshop or careers talk, email our careers team.</p>",
			'careers_voices'             => [
				[
					'person' => $person_ids['ruth-calloway'],
					'quote'  => 'I came here from running operations, and I still spend two days a week with clients. That is deliberate. A firm that sells practical advice should be run by people who still do the work.',
				],
				[
					'person' => $person_ids['anjali-mehta'],
					'quote'  => 'What keeps me here is seeing change hold. A year after a project I call the team leaders we worked with. More often than not, they have kept improving without us.',
				],
				[
					'person' => $person_ids['grace-okonkwo'],
					'quote'  => 'I came from a technology background and expected to build systems. Here I spend as much time with frontline teams as with developers, and the digital work is better for it.',
				],
			],
			'careers_voices_heading'     => 'Hear from our people',
			'careers_work_columns'       => [
				[
					'text'  => 'Our work is about how service organisations operate: how demand arrives, how work flows, how teams are organised and how customers experience the result. That covers operating model design, process improvement, customer experience, digital change and the training that makes improvement last. Our clients are banks, insurers, utilities, travel operators, universities, public bodies and health providers. We take on fewer projects than many firms of our size, and we judge ourselves by what our clients can still do once we have left, not by the length of the engagement.',
					'title' => 'Business focus',
				],
				[
					'text'  => 'Most of our projects are delivered alongside client teams, not handed to them. We work in joint teams with frontline staff and managers, coach the people who will run the new way of working, and share our methods openly. Consultants often move between client work and teaching on our accredited courses, which keeps both honest. You will be expected to analyse data, facilitate workshops, write clearly for senior audiences and spend time on an operations floor, sometimes all in the same week.',
					'title' => 'Delivery capabilities',
				],
				[
					'text'  => 'Our head office is in Woodstock, Oxfordshire, where we also run our training centre, but most of our work happens at client sites across the UK. We do not expect you to live near Oxford. Many of our consultants work from home between client visits, and we plan travel so that most weeks include at least one day at home. Client work does mean regular overnight stays during busy phases of a project, and we are open about that at interview.',
					'title' => 'Working locations',
				],
				[
					'text'  => 'Every consultant has a development manager: a senior colleague outside their project team who meets them monthly and agrees their development plan. New consultants complete our foundation programme in their first six months, covering our methods, client conduct and facilitation, and work towards practitioner certification with the Service Operations Institute. Experienced hires spend their first weeks learning how we work before leading client teams. Everyone has ten paid development days a year to spend on courses, conferences or research of their choosing.',
					'title' => 'Consultant development',
				],
			],
			'careers_work_heading'       => 'What it is like to work here',
			'meta_description'           => 'We hire people who have run service operations, not only advised on them.',
			'news_heading'               => 'Latest careers news',
			'news_posts'                 => halveron_seed_pick( $post_ids, [ 'public-sector-service-academy', 'lean-forum-100-members', 'coaching-hybrid-teams' ] ),
			'section_tabs'               => [
				[ 'label' => 'Working at Halveron', 'url' => '/careers/#working-here' ],
				[ 'label' => 'Current opportunities', 'url' => '/careers/opportunities/' ],
				[ 'label' => 'Business schools', 'url' => '/careers/#business-schools' ],
			],
			'section_title'              => 'Halveron Careers',
			'statement'                  => '‘We tell clients what we see, then stay until the work is done.’',
		],
		'thumbnail' => 'training-workshop.jpg',
		'title'     => 'Careers',
	],
	'careers/opportunities' => [
		'meta'   => [
			'meta_description'    => 'We are recruiting for six roles across consulting, training and our graduate programme.',
			'opportunities_intro' => <<<'HTML'
				<p>We are recruiting for six roles across consulting, training and our graduate programme. Each one involves working closely with clients and with each other, and each offers a clear route to accreditation and promotion. If none of them fits but you think you should work with us, write to us anyway.</p>
				<p>To apply, email your CV and a short covering note to <a href="mailto:careers@halveron.junaid.guru">careers@halveron.junaid.guru</a>, with the role title in the subject line. We reply to every application within ten working days.</p>
				HTML,
			'opportunities_lead'  => 'We have the following opportunities:',
		],
		'parent' => 'careers',
		'title'  => 'Current opportunities',
	],
	'forum'                 => [
		'meta'      => [
			'forum_about_heading'           => 'Lean Service Forum',
			'forum_about_text'              => <<<'HTML'
				<p>The Lean Service Forum is a peer network for people who run service operations. Members are operations directors, heads of customer service and improvement leads from banks, insurers, utilities, rail and ferry operators, universities, councils and health providers.</p>
				<p>The forum meets three times a year at a member's premises, and members gather a fourth time at our Future of Service Conference. Every meeting includes a visit to the host's operations, a case session in which the host explains what they changed and what it cost, and a peer clinic where members bring a live problem and leave with suggestions from people who have faced it. Nothing said at a meeting is attributed outside the room, so people can speak frankly. Halveron organises the forum and pays for it, and we do not use it to sell.</p>
				HTML,
			'forum_contact'                 => $person_ids['daniel-fry'],
			'forum_contact_heading'         => 'Contact us',
			'forum_contact_text'            => 'If you are interested in the Lean Service Forum, please contact Daniel Fry.',
			'forum_members_heading'         => 'Lean Service Forum members',
			'forum_members_intro'           => 'Six of the more than 100 organisations in the forum.',
			'forum_membership_button_label' => 'Apply to join the forum',
			'forum_membership_heading'      => 'Apply for membership',
			'forum_membership_subject'      => 'Lean Service Forum membership',
			'forum_membership_text'         => "<p>Membership is free and open to UK organisations that run service operations. Each member nominates up to two people, usually an operations director and an improvement lead, and agrees to host a meeting if asked. Consultancies and technology suppliers cannot join. To apply, email Daniel Fry with your organisation's name, a short description of your operations and the names of the people who would attend. We review applications before each meeting.</p>",
			'forum_next_event'              => $event_ids['forum-november-2026'],
			'forum_next_heading'            => 'Next event',
			'forum_next_text'               => 'On Thursday 19 November 2026, Northwyn Rail hosts members in Manchester. We will tour its customer information and control centre and hear how it keeps passengers informed when the timetable fails. Places are for members and are allocated in order of registration.',
			'forum_past_events'             => halveron_seed_pick( $event_ids, [ 'forum-june-2026', 'forum-march-2026' ] ),
			'forum_past_heading'            => 'Past events',
			'meta_description'              => 'The Lean Service Forum is a peer network for people who run service operations.',
			'news_heading'                  => 'Latest forum news',
			'news_posts'                    => halveron_seed_pick( $post_ids, [ 'lean-forum-100-members', 'future-of-service-2026', 'start-with-demand' ] ),
			'section_tabs'                  => [
				[ 'label' => 'About the Forum', 'url' => '#about' ],
				[ 'label' => 'Next event', 'url' => '#next-event' ],
				[ 'label' => 'Past events', 'url' => '#past-events' ],
				[ 'label' => 'Apply for membership', 'url' => '#membership' ],
			],
			'section_title'                 => 'Halveron Forum',
			'statement'                     => 'Next meeting: Thursday 19 November 2026, Manchester',
		],
		'thumbnail' => 'forum-hero.jpg',
		'title'     => 'Forum',
	],
	'conference'            => [
		'meta'      => [
			'conference_book_label'           => 'Book tickets',
			'conference_booking_text'         => '<p>To book, email <a href="mailto:events@halveron.junaid.guru">events@halveron.junaid.guru</a> with the name, role and organisation of each delegate. We will confirm your places and send an invoice.</p>',
			'conference_callout_text'         => 'Join operations and customer leaders from across the UK service sector for a day of practical sessions, honest conversation and time with peers who do the same job.',
			'conference_contact'              => $person_ids['anjali-mehta'],
			'conference_contact_button_label' => 'Email the organisers',
			'conference_contact_heading'      => 'Contact the organisers',
			'conference_contact_text'         => 'For questions about the programme, accessibility or group bookings, contact Anjali Mehta, who leads the conference programme.',
			'conference_date'                 => '2027-03-03',
			'conference_location'             => 'The Assembly Rooms, Finsbury, London',
			'conference_name'                 => 'Future of Service Conference 2027',
			'conference_past_heading'         => 'Past conferences',
			'conference_past_image'           => $img['conference-last-year.jpg'],
			'conference_past_link'            => $post_ids['future-of-service-2026'],
			'conference_past_link_label'      => 'Read what we heard at the 2026 conference',
			'conference_past_text'            => <<<'HTML'
				<p>On Thursday 24 September 2026, 240 operations and customer leaders from 130 organisations joined us at The Assembly Rooms in Finsbury. The day opened with a question: what should service organisations stop doing?</p>
				<p>A building society's operations director gave a candid account of artificial intelligence in her contact centre. Generated call summaries had saved advisers around 40 seconds a call; a customer-facing chatbot had handled many conversations but resolved few. A panel from insurance, water and banking argued that outcome-based regulation had given them a mandate to fix journeys they had long known were poor. The afternoon turned to people, and to the link between pointless work and frontline turnover.</p>
				<p>From 2027 the conference moves to the spring, so the next one is only five months away.</p>
				HTML,
			'conference_past_title'           => 'Future of Service Conference 2026',
			'conference_speakers'             => [
				[
					'person'  => $person_ids['margaret-ellison'],
					'session' => 'What the evidence says about demand',
					'text'    => 'Margaret will set out what research tells us about failure demand, channel shift and the link between staff turnover and customer outcomes. She will also be candid about where the evidence is thinner than the confident claims made for it, and suggest three questions every operations leader should ask before trusting a benchmark.',
				],
				[
					'person'  => $person_ids['helen-ashby'],
					'session' => 'Showing your working: customer outcomes in practice',
					'text'    => 'Regulators now ask firms to show that customers are treated well, not simply to say so. Helen will describe how lenders and insurers have built that evidence into everyday operations, which approaches added cost without changing anything for customers, and what water, energy and public services can borrow from them.',
				],
				[
					'person'  => $person_ids['rafael-moreno'],
					'session' => 'Automation customers do not notice',
					'text'    => 'The automation that works is often the kind customers never see: faster answers, fewer handoffs, no repeated questions. Rafael will show where automation has reduced effort for customers and staff in contact centres and back offices, where it has simply moved the work elsewhere, and how to tell the difference before you buy.',
				],
			],
			'conference_speakers_heading'     => 'Speakers include',
			'conference_theme_heading'        => 'What does it take to run a service people trust?',
			'conference_theme_text'           => '<p>Trust in a service is built in ordinary moments: a letter that makes sense, a promised call that comes, a problem fixed the first time. It is lost the same way. In 2027 we will ask what it takes to earn that trust when budgets are tight, regulators ask for evidence and automation is changing what customers expect. Our speakers bring research, financial services and digital operations experience to the question, and practitioners from across the service sector will run workshops on demand, staffing and technology. Expect plain speaking, real numbers and time to talk with peers who face the same problems.</p>',
			'conference_tickets_heading'      => 'Tickets available now',
			'conference_tickets_list'         => [
				[ 'text' => '£295 plus VAT per person' ],
				[ 'text' => 'All keynote and panel sessions' ],
				[ 'text' => 'Two practitioner workshops of your choice' ],
				[ 'text' => 'Lunch, refreshments and the evening reception' ],
				[ 'text' => 'A delegate pack with session slides and notes' ],
			],
			'conference_tickets_text'         => 'We have 280 places this year, up from 240 in 2026, when the conference was full three weeks before the day. We recommend booking early.',
			'conference_venue_heading'        => 'The venue',
			'conference_venue_image'          => $img['conference-venue.jpg'],
			'conference_venue_text'           => '<p>The Assembly Rooms is a converted Victorian printworks in Finsbury, a ten-minute walk from Old Street and Moorgate stations. The main hall seats 300 under its original iron roof, and four smaller rooms host the workshops. The building is step-free throughout, with a hearing loop in every room, accessible toilets on each floor and a quiet room for anyone who needs a break. There is no parking on site, so we recommend public transport.</p>',
			'meta_description'                => 'Future of Service Conference 2027: Wednesday 3 March 2027 at The Assembly Rooms, Finsbury, London.',
			'section_tabs'                    => [
				[ 'label' => 'About the Conference', 'url' => '#about' ],
				[ 'label' => 'Next conference', 'url' => '#next-conference' ],
				[ 'label' => 'Past conferences', 'url' => '#past-conferences' ],
				[ 'label' => 'Contact the organisers', 'url' => '#contact' ],
			],
			'section_title'                   => 'Halveron Conference',
		],
		'thumbnail' => 'conference-hero.jpg',
		'title'     => 'Conference',
	],
	'privacy'               => [
		'content' => <<<'HTML'
			<h2>Who is responsible for your data</h2>
			<p>Halveron Consulting Ltd is the controller of personal information collected through this website. We are registered in England and Wales, company number 00000000, at The Granary, 9 Mill Lane, Woodstock, Oxfordshire OX20 0XX. You can contact us about privacy at <a href="mailto:hello@halveron.junaid.guru">hello@halveron.junaid.guru</a> or on <a href="tel:+441632960410">+44&nbsp;(0)1632&nbsp;960410</a>.</p>
			<h2>What we collect</h2>
			<p>We collect only what you choose to give us:</p>
			<ul>
			<li>your name, email address, company and phone number, and the message you write, when you use a contact form;</li>
			<li>your email address, when you sign up for our newsletter;</li>
			<li>your name, company, email address, phone number and areas of interest, when you download a white paper;</li>
			<li>the details you give us when you book a course, apply for a job or register for a forum meeting or our conference.</li>
			</ul>
			<p>We do not use analytics or advertising tools, and we do not build profiles of visitors.</p>
			<h2>Why we use it, and on what basis</h2>
			<p>We use your information to reply to your enquiry, to provide a course or event you have booked, to consider a job application, and to send the newsletter you asked for. The law allows us to do this on the following bases:</p>
			<ul>
			<li><strong>Contract</strong>, when we need the information to provide a course, event place or service you have asked for.</li>
			<li><strong>Consent</strong>, for the newsletter and white-paper follow-up. You can withdraw consent at any time using the link in every email or by contacting us.</li>
			<li><strong>Legitimate interests</strong>, when we reply to an enquiry or keep a record of our correspondence with clients. We only rely on this where our interest does not override your rights.</li>
			<li><strong>Legal obligation</strong>, where we must keep records for tax, accounting or employment law.</li>
			</ul>
			<h2>Who we share it with</h2>
			<p>We do not sell personal information. We share it only with service providers who help us run our business, such as our email, hosting and course-booking providers, under contracts that require them to protect it and use it only on our instructions. If a course is externally accredited, we share the details needed to register you with the accrediting body. We may also disclose information where the law requires it.</p>
			<p>Where a provider stores data outside the UK, we make sure appropriate safeguards are in place.</p>
			<h2>How long we keep it</h2>
			<p>We keep enquiries for two years after our last contact with you, newsletter details until you unsubscribe, course and accreditation records for six years, and unsuccessful job applications for six months. After that we delete the information or make it anonymous.</p>
			<h2>Fonts</h2>
			<p>This website loads its typeface from Google Fonts. When your browser requests the font, Google receives your IP address and basic browser information, as it would for any web request. Google's own privacy policy applies to that request. No other third-party services are loaded.</p>
			<h2>Your rights</h2>
			<p>You have the right to ask for a copy of the personal information we hold about you, to have it corrected or deleted, to restrict or object to how we use it, and to ask us to transfer it to another organisation. Where we rely on consent, you can withdraw it at any time. To use any of these rights, contact us using the details above. We will respond within one month.</p>
			<h2>Complaints</h2>
			<p>If you are unhappy with how we have handled your information, please tell us first so that we can try to put it right. You also have the right to complain to the Information Commissioner's Office, the UK regulator for data protection, at ico.org.uk.</p>
			<h2>Changes to this notice</h2>
			<p>We will update this notice when our practices change, and show the date of the latest version at the top of the page.</p>
			HTML,
		'meta'    => [
			'last_updated'     => '2026-10-01',
			'meta_description' => 'How Halveron Consulting would handle personal information, and why this demonstration website collects none.',
			'notice'           => 'Halveron Consulting is a fictional firm and this is a demonstration website. The forms on this site do not send, store or process anything you type into them. The rest of this notice describes how a real firm of this kind would handle personal information. Please do not enter real personal information anywhere on this site.',
			'updated_label'    => 'updated',
		],
		'title'   => 'Privacy notice',
	],
	'cookies'               => [
		'content' => <<<'HTML'
			<h2>Our cookies</h2>
			<p>This website sets no cookies of its own. We do not use analytics, advertising or social-media tracking, so there is no cookie banner and nothing to accept or decline.</p>
			<h2>Fonts</h2>
			<p>Our typeface is loaded from Google Fonts. Google does not set cookies when serving fonts, but your browser does send it a standard request, as described in our privacy notice.</p>
			<h2>Links to other sites</h2>
			<p>Links to LinkedIn, X and YouTube take you to those sites, which set their own cookies under their own policies. Nothing from those sites is loaded until you follow the link.</p>
			<h2>Managing cookies</h2>
			<p>You can block or delete cookies at any time in your browser's settings. Doing so will not affect how this website works.</p>
			HTML,
		'meta'    => [
			'last_updated'     => '2026-10-01',
			'meta_description' => 'This website sets no cookies of its own.',
			'notice'           => '',
			'updated_label'    => 'updated',
		],
		'title'   => 'Cookie notice',
	],
	'terms'                 => [
		'content' => <<<'HTML'
			<h2>About these terms</h2>
			<p>These terms apply to everyone who uses this website. By using the site you accept them. If you do not accept them, please do not use the site. The site is operated by Halveron Consulting Ltd, registered in England and Wales, company number 00000000, at The Granary, 9 Mill Lane, Woodstock, Oxfordshire OX20 0XX.</p>
			<h2>Not professional advice</h2>
			<p>The articles, case studies, white papers and other content on this site are for general information only. They are not professional, legal, financial or regulatory advice, and they do not take account of the circumstances of any particular organisation. You should take advice suited to your own situation before acting on anything you read here. Figures quoted in case studies and elsewhere describe typical results in particular circumstances; they are not forecasts or promises of what any organisation will achieve.</p>
			<h2>Using the site</h2>
			<p>You may view, download and print pages from this site for your own information or for use within your organisation, as long as you keep any copyright notices. You must not:</p>
			<ul>
			<li>reproduce or republish material from the site for commercial purposes without our written permission;</li>
			<li>use the site in any way that breaks the law or harms others;</li>
			<li>try to gain unauthorised access to the site or the systems that host it;</li>
			<li>introduce viruses or other harmful material.</li>
			</ul>
			<h2>Intellectual property</h2>
			<p>The text, graphics, logos and design of this site belong to Halveron Consulting Ltd or are used with permission. The names and logos of client, partner and member organisations shown on this site are invented for the demonstration.</p>
			<h2>Accuracy and availability</h2>
			<p>We take care to keep the content of this site accurate and up to date, but we cannot guarantee that it is complete or free from errors. We may change, suspend or withdraw the site, or any part of it, without notice.</p>
			<h2>Links to other websites</h2>
			<p>Where the site links to other websites, those links are provided for convenience. We have no control over those sites and accept no responsibility for their content or for any loss that may arise from your use of them.</p>
			<h2>Our liability</h2>
			<p>Nothing in these terms limits our liability for death or personal injury caused by our negligence, for fraud, or for anything else that cannot be limited by law. Otherwise, we are not liable for any loss arising from your use of this site or from reliance on its content, including indirect or consequential loss and loss of profit, business or data.</p>
			<h2>Governing law</h2>
			<p>These terms are governed by the law of England and Wales, and the courts of England and Wales have exclusive jurisdiction over any dispute arising from them.</p>
			<h2>Contact</h2>
			<p>Questions about these terms can be sent to <a href="mailto:hello@halveron.junaid.guru">hello@halveron.junaid.guru</a>.</p>
			HTML,
		'meta'    => [
			'last_updated'     => '2026-10-01',
			'meta_description' => 'The terms that apply when you use the Halveron Consulting demonstration website.',
			'notice'           => 'Halveron Consulting is a fictional firm created for this demonstration website. People, clients, case studies and events are invented; any resemblance to real organisations is coincidental. Courses cannot be booked, events cannot be attended and jobs cannot be applied for. These terms are written as they would be for a live site and apply to your use of this one.',
			'updated_label'    => 'updated',
		],
		'title'   => 'Terms of use',
	],
	'accessibility'         => [
		'content' => <<<'HTML'
			<h2>Our commitment</h2>
			<p>We want everyone who visits this site to be able to read it, find their way around it and contact us, whatever device or assistive technology they use. This statement applies to every page on this demonstration website.</p>
			<h2>Conformance status</h2>
			<p>This website has been built to meet the Web Content Accessibility Guidelines (WCAG) 2.2 at Level AA. Every page passes the automated checks described below. Because we have not yet completed testing with screen readers, we describe the site as partially conformant until that testing is done; we will update this statement when it is.</p>
			<h2>What you can expect</h2>
			<ul>
			<li>Text and interface colours meet a contrast ratio of at least 4.5:1, and large text and interface components at least 3:1.</li>
			<li>Every page can be used with a keyboard alone, with a clear focus outline on every link, button and form field.</li>
			<li>Each page has one main heading, a logical heading order, landmarks and a link to skip to the main content.</li>
			<li>Images have text alternatives, and decorative images are hidden from assistive technology.</li>
			<li>Layouts work on screens from 320 pixels wide without scrolling sideways, and text can be enlarged to 200%.</li>
			<li>Carousels never move on their own, and the site respects your device's reduced-motion setting.</li>
			<li>Tabs, carousels, accordions and menus work without JavaScript.</li>
			<li>Form fields have visible labels, and hints are linked to their fields.</li>
			</ul>
			<h2>How we tested</h2>
			<p>Every page is checked with automated tools: the HTML is validated, and each page is tested against WCAG 2.2 Level AA with an automated accessibility checker. We also test by hand with a keyboard in Google Chrome, and review every page at desktop and mobile widths.</p>
			<h2>Known limitations</h2>
			<ul>
			<li>Screen-reader testing has not yet been completed.</li>
			<li>The site search needs JavaScript. Without it, the search page lists every page on the site instead.</li>
			<li>The forms on this demonstration site do not send messages.</li>
			</ul>
			<h2>Reporting a problem</h2>
			<p>If you have difficulty using any part of this site, or need information in a different format, email <a href="mailto:hello@halveron.junaid.guru">hello@halveron.junaid.guru</a> with "Accessibility" in the subject line, or call <a href="tel:+441632960410">+44&nbsp;(0)1632&nbsp;960410</a>. Tell us the page address and what went wrong. We aim to reply within five working days.</p>
			<h2>Enforcement</h2>
			<p>If you are not happy with our response, you can contact the Equality Advisory and Support Service (EASS).</p>
			HTML,
		'meta'    => [
			'last_updated'     => '2026-10-01',
			'meta_description' => 'How we have built this website to meet WCAG 2.2 at Level AA, how we test it, and how to report a problem.',
			'notice'           => '',
			'updated_label'    => 'reviewed',
		],
		'title'   => 'Accessibility statement',
	],
	'sitemap'               => [
		'meta'  => [
			'intro'            => 'Every page on this website, grouped by section.',
			'meta_description' => 'Every page on the Halveron Consulting website.',
		],
		'title' => 'Sitemap',
	],
];

$page_ids      = [];
$created_pages = 0;

foreach ( $pages as $path => $page ) {
	$thumbnail = $img[ $page['thumbnail'] ?? '' ] ?? 0;

	[ $page_ids[ basename( $path ) ], $created ] = halveron_seed_post( 'page', $path, [
		'post_content' => $page['content'] ?? '',
		'post_parent'  => $page_ids[ $page['parent'] ?? '' ] ?? 0,
		'post_title'   => $page['title'],
	], $page['meta'], $thumbnail );

	if ( ! $thumbnail ) {
		delete_post_thumbnail( $page_ids[ basename( $path ) ] );
	}

	$created_pages += $created;
}

echo "pages: {$created_pages} created, " . count( $page_ids ) . " total\n";

$menus = [
	'primary'   => [
		'items' => [
			[ 'menu-item-object' => 'capability', 'menu-item-title' => 'Our Capabilities', 'menu-item-type' => 'post_type_archive' ],
			[ 'menu-item-object' => 'sector', 'menu-item-title' => 'Our Sectors', 'menu-item-type' => 'post_type_archive' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['thinking'], 'menu-item-title' => 'Our Thinking', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['about'], 'menu-item-title' => 'About Us', 'menu-item-type' => 'post_type' ],
		],
		'name'  => 'Primary',
	],
	'secondary' => [
		'items' => [
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['training'], 'menu-item-title' => 'Training', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['careers'], 'menu-item-title' => 'Careers', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['forum'], 'menu-item-title' => 'Forum', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['conference'], 'menu-item-title' => 'Conference', 'menu-item-type' => 'post_type' ],
		],
		'name'  => 'Secondary',
	],
	'footer-1'  => [
		'items' => [
			[ 'menu-item-object' => 'capability', 'menu-item-title' => 'Our Capabilities', 'menu-item-type' => 'post_type_archive' ],
			[ 'menu-item-object' => 'sector', 'menu-item-title' => 'Our Sectors', 'menu-item-type' => 'post_type_archive' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['thinking'], 'menu-item-title' => 'Our Thinking', 'menu-item-type' => 'post_type' ],
		],
		'name'  => 'Consulting',
	],
	'footer-2'  => [
		'items' => [
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['about'], 'menu-item-title' => 'About Us', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['careers'], 'menu-item-title' => 'Careers', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['news'], 'menu-item-title' => 'News', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['contact'], 'menu-item-title' => 'Contact', 'menu-item-type' => 'post_type' ],
		],
		'name'  => 'Company',
	],
	'footer-3'  => [
		'items' => [
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['training'], 'menu-item-title' => 'Training', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'course', 'menu-item-title' => 'Courses', 'menu-item-type' => 'post_type_archive' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['forum'], 'menu-item-title' => 'Forum', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['conference'], 'menu-item-title' => 'Conference', 'menu-item-type' => 'post_type' ],
		],
		'name'  => 'Learning and events',
	],
	'legal'     => [
		'items' => [
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['sitemap'], 'menu-item-title' => 'Sitemap', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['cookies'], 'menu-item-title' => 'Cookies', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['privacy'], 'menu-item-title' => 'Privacy', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['terms'], 'menu-item-title' => 'Terms of use', 'menu-item-type' => 'post_type' ],
			[ 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['accessibility'], 'menu-item-title' => 'Accessibility', 'menu-item-type' => 'post_type' ],
		],
		'name'  => 'Legal',
	],
];

$locations     = get_theme_mod( 'nav_menu_locations', [] );
$created_items = 0;

foreach ( $menus as $location => $menu ) {
	[ $locations[ $location ], $created ] = halveron_seed_menu( $menu['name'], $menu['items'] );
	$created_items += $created;
}

set_theme_mod( 'nav_menu_locations', $locations );

echo "menus: {$created_items} created, " . count( $menus ) . " menus assigned to locations\n";

update_option( 'page_for_posts', $page_ids['news'] );
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'show_on_front', 'page' );
update_option( 'category_base', '' );

$GLOBALS['wp_rewrite']->set_permalink_structure( '/thinking/%postname%/' );
flush_rewrite_rules( false );

echo "reading: front page, posts page and permalinks set, rewrite rules flushed\n";

foreach ( [ [ 'post', 'hello-world' ], [ 'page', 'sample-page' ], [ 'page', 'privacy-policy' ] ] as [ $default_type, $default_slug ] ) {
	foreach ( get_posts( [ 'name' => $default_slug, 'numberposts' => 1, 'post_status' => [ 'draft', 'publish' ], 'post_type' => $default_type ] ) as $default_post ) {
		wp_delete_post( $default_post->ID, true );
	}
}

update_option( 'wp_page_for_privacy_policy', 0 );

$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );

if ( $uncategorized ) {
	wp_delete_term( $uncategorized->term_id, 'category' );
}

echo "defaults: removed\n";
echo "SEED COMPLETE\n";

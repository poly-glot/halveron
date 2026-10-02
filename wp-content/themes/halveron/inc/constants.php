<?php

const HALVERON_ARCHIVE_TITLES = array(
	'capability' => array( 'capabilities_archive_title', 'Our capabilities' ),
	'course'     => array( 'courses_archive_title', 'Courses' ),
	'sector'     => array( 'sectors_archive_title', 'Our sectors' ),
);

const HALVERON_ARCHIVE_DESCRIPTIONS = array(
	'capability' => 'capabilities_archive_description',
	'course'     => 'courses_archive_description',
	'sector'     => 'sectors_archive_description',
);

const HALVERON_INSIGHT_TYPES = array(
	'case-study'  => array(
		'filter'  => 'case-studies',
		'link'    => 'Find out how we did it',
		'section' => 'Case studies',
		'tag'     => 'Case study',
	),
	'in-brief'    => array(
		'filter'  => 'in-brief',
		'link'    => 'Read full article',
		'section' => 'In brief',
		'tag'     => 'In brief',
	),
	'news'        => array(
		'filter'  => 'news',
		'link'    => 'Read full article',
		'section' => 'News',
		'tag'     => 'News',
	),
	'white-paper' => array(
		'filter'  => 'white-papers',
		'link'    => 'Download white paper',
		'section' => 'White papers',
		'tag'     => 'White paper',
	),
);

const HALVERON_THINKING_ROWS = array(
	'news'        => array(
		'count'  => 3,
		'hidden' => ' news',
		'id'     => 'news-title',
		'title'  => 'Latest news',
	),
	'in-brief'    => array(
		'count'  => 3,
		'hidden' => ' In brief notes',
		'id'     => 'in-brief-title',
		'title'  => 'In brief',
	),
	'white-paper' => array(
		'count'  => -1,
		'hidden' => ' white papers',
		'id'     => 'white-papers-title',
		'title'  => 'White papers',
	),
	'case-study'  => array(
		'count'  => 4,
		'hidden' => ' case studies',
		'id'     => 'case-studies-title',
		'title'  => 'Latest case studies',
	),
);

const HALVERON_NEWS_FILTERS = array(
	'all'          => 'All',
	'news'         => 'News',
	'case-studies' => 'Case studies',
	'white-papers' => 'White papers',
	'in-brief'     => 'In brief',
);

const HALVERON_RESULT_TYPES = array(
	'capability' => 'Capability',
	'course'     => 'Course',
	'event'      => 'Event',
	'page'       => 'Page',
	'sector'     => 'Sector',
);

const HALVERON_UPDATED_LABELS = array(
	'reviewed' => 'Last reviewed',
	'updated'  => 'Last updated',
);

const HALVERON_JOB_TYPES = array(
	'associate' => 'Associate',
	'graduate'  => 'Graduate',
	'permanent' => 'Permanent',
);

const HALVERON_COURSE_GROUPS = array(
	'open'   => array(
		'anchor' => 'open-training',
		'title'  => 'Open training',
	),
	'custom' => array(
		'anchor' => 'custom-training',
		'title'  => 'Custom training',
	),
);

const HALVERON_LOGO_PREFIXES = array(
	'forum_member' => 'member-',
);

const HALVERON_HOME_INDEX = array(
	'introduction' => 'Introduction',
	'capabilities' => 'Our capabilities',
	'training'     => 'Training courses',
	'clients'      => 'Our clients',
	'thinking'     => 'Halveron Thinking',
);

const HALVERON_STAT_TINTS = array( 'stat', 'stat stat--purple', 'stat stat--cyan', 'stat stat--charcoal' );

const HALVERON_WHEEL = array(
	array(
		'd'       => 'M200 8A192 192 0 0 1 335.8 64.2L253.7 146.3A76 76 0 0 0 200 124Z',
		'label'   => 'wheel__label',
		'segment' => 'wheel__segment wheel__segment--purple',
		'x'       => 252,
		'y'       => 78.9,
	),
	array(
		'd'       => 'M335.8 64.2A192 192 0 0 1 392 200L276 200A76 76 0 0 0 253.7 146.3Z',
		'label'   => 'wheel__label',
		'segment' => 'wheel__segment wheel__segment--navy',
		'x'       => 325.6,
		'y'       => 152.5,
	),
	array(
		'd'       => 'M392 200A192 192 0 0 1 335.8 335.8L253.7 253.7A76 76 0 0 0 276 200Z',
		'label'   => 'wheel__label',
		'segment' => 'wheel__segment wheel__segment--action',
		'x'       => 325.6,
		'y'       => 256.5,
	),
	array(
		'd'       => 'M335.8 335.8A192 192 0 0 1 200 392L200 276A76 76 0 0 0 253.7 253.7Z',
		'label'   => 'wheel__label wheel__label--dark',
		'segment' => 'wheel__segment wheel__segment--cyan',
		'x'       => 252,
		'y'       => 330.1,
	),
	array(
		'd'       => 'M200 392A192 192 0 0 1 64.2 335.8L146.3 253.7A76 76 0 0 0 200 276Z',
		'label'   => 'wheel__label',
		'segment' => 'wheel__segment wheel__segment--purple',
		'x'       => 148,
		'y'       => 330.1,
	),
	array(
		'd'       => 'M64.2 335.8A192 192 0 0 1 8 200L124 200A76 76 0 0 0 146.3 253.7Z',
		'label'   => 'wheel__label',
		'segment' => 'wheel__segment wheel__segment--navy',
		'x'       => 74.4,
		'y'       => 256.5,
	),
	array(
		'd'       => 'M8 200A192 192 0 0 1 64.2 64.2L146.3 146.3A76 76 0 0 0 124 200Z',
		'label'   => 'wheel__label',
		'segment' => 'wheel__segment wheel__segment--action',
		'x'       => 74.4,
		'y'       => 152.5,
	),
	array(
		'd'       => 'M64.2 64.2A192 192 0 0 1 200 8L200 124A76 76 0 0 0 146.3 146.3Z',
		'label'   => 'wheel__label wheel__label--dark',
		'segment' => 'wheel__segment wheel__segment--cyan',
		'x'       => 148,
		'y'       => 78.9,
	),
);

const HALVERON_FLOW_SPOKES = array(
	'M340 235L340 70',
	'M340 235L482.9 152.5',
	'M340 235L482.9 317.5',
	'M340 235L340 400',
	'M340 235L197.1 317.5',
	'M340 235L197.1 152.5',
);

const HALVERON_FLOW_NODES = array(
	array(
		'label'   => 'flow-diagram__label',
		'label_x' => 340,
		'label_y' => 34,
		'x'       => 340,
		'y'       => 70,
	),
	array(
		'label'   => 'flow-diagram__label flow-diagram__label--start',
		'label_x' => 518.9,
		'label_y' => 158,
		'x'       => 482.9,
		'y'       => 152.5,
	),
	array(
		'label'   => 'flow-diagram__label flow-diagram__label--start',
		'label_x' => 518.9,
		'label_y' => 323,
		'x'       => 482.9,
		'y'       => 317.5,
	),
	array(
		'label'   => 'flow-diagram__label',
		'label_x' => 340,
		'label_y' => 448,
		'x'       => 340,
		'y'       => 400,
	),
	array(
		'label'   => 'flow-diagram__label flow-diagram__label--end',
		'label_x' => 161.1,
		'label_y' => 323,
		'x'       => 197.1,
		'y'       => 317.5,
	),
	array(
		'label'   => 'flow-diagram__label flow-diagram__label--end',
		'label_x' => 161.1,
		'label_y' => 158,
		'x'       => 197.1,
		'y'       => 152.5,
	),
);

const HALVERON_ENQUIRY_FORMS = array(
	'contact'  => array(
		'anchor' => 'enquiry-title',
		'label'  => 'Contact page',
	),
	'download' => array(
		'anchor' => 'download',
		'label'  => 'White-paper download',
	),
	'sector'   => array(
		'anchor' => 'enquiry-title',
		'label'  => 'Sector page',
	),
);

const HALVERON_ENQUIRY_SOURCES = array( 'page', 'post', 'sector' );

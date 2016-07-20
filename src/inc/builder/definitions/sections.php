<?php
/**
 * @package Make
 */

// Bail if this isn't being included inside of a MAKE_Builder_SetupInterface.
if ( ! isset( $this ) || ! $this instanceof MAKE_Builder_SetupInterface ) {
	return;
}

// Setting definition presets
$settings_title = array(
	'title'            => array(
		'default'     => '',
		'sanitize'    => '',
		'sanitize_ui' => '',
	),
);
$settings_content = array(
	'content' => array(
		'default' => '',
		'sanitize' => '',
	),
);
$settings_background = array(
	'background-image' => array(
		'default'  => '',
		'sanitize' => '',
	),
	'darken'           => array(
		'default'  => false,
		'sanitize' => 'wp_validate_boolean'
	),
	'background-style' => array(
		'default'    => 'tile',
		'sanitize'   => '',
		'choice_set' => 'builder-section-background-style'
	),
	'background-color' => array(
		'default'  => '',
		'sanitize' => 'maybe_hash_hex_color'
	),
);

// Columns section
$this->register_section_type(
	'text',
	array(
		'label'       => esc_html__( 'Columns', 'make' ),
		'description' => esc_html__( 'Create rearrangeable columns of content and images.', 'make' ),
		'icon_url'    => $this->scripts()->get_css_directory_uri() . '/builder/sections/images/text.png',
		'priority'    => 10,
		'settings'    => array_merge(
			$settings_title,
			array(
				'columns-number'   => array(
					'default'    => 3,
					'sanitize'   => '',
					'choice_set' => '1-4'
				),
			),
			$settings_background
		),
		'ui'          => array(),
	)
);

// Column
$this->register_section_type(
	'text-column',
	array(
		'label'       => esc_html__( 'Column', 'make' ),
		'settings'    => array_merge(
			$settings_title,
			array(
				'image-link' => array(
					'default'           => '',
					'sanitize'          => 'esc_url',
					'sanitize_database' => 'esc_url_raw'
				),
				'image-id' => array(
					'default' => 0,
					'sanitize' => '',
				),
			),
			$settings_content
		),
		'ui'          => array(),
	)
);

// Banner section
$this->register_section_type(
	'banner',
	array(
		'label'       => esc_html__( 'Banner', 'make' ),
		'description' => esc_html__( 'Display multiple types of content in a banner or a slider.', 'make' ),
		'icon_url'    => $this->scripts()->get_css_directory_uri() . '/builder/sections/images/banner.png',
		'priority'    => 30,
		'settings'    => array(),
		'ui'          => array(),
	)
);

// Banner slide
$this->register_section_type(
	'banner-slide',
	array(
		'label'       => esc_html__( 'Banner Slide', 'make' ),
		'settings'    => array(),
		'ui'          => array(),
	)
);

// Gallery section
$this->register_section_type(
	'gallery',
	array(
		'label'       => esc_html__( 'gallery', 'make' ),
		'description' => esc_html__( 'Display your images in various grid combinations.', 'make' ),
		'icon_url'    => $this->scripts()->get_css_directory_uri() . '/builder/sections/images/gallery.png',
		'priority'    => 40,
		'settings'    => array(),
		'ui'          => array(),
	)
);

// Gallery item
$this->register_section_type(
	'gallery-item',
	array(
		'label'       => esc_html__( 'Gallery Item', 'make' ),
		'settings'    => array(),
		'ui'          => array(),
	)
);
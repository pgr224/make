<?php
/**
 * @package Make
 */

if ( ! class_exists( 'MAKE_Builder_Sections_Banner_Definition' ) ) :
/**
 * Section definition for Columns
 *
 * Class MAKE_Builder_Sections_Banner_Definition
 */
class MAKE_Builder_Sections_Banner_Definition {
	/**
	 * The one instance of MAKE_Builder_Sections_Banner_Definition.
	 *
	 * @var   MAKE_Builder_Sections_Banner_Definition
	 */
	private static $instance;

	/**
	 * Register the text section.
	 *
	 * Note that in 1.4.0, the "text" section was renamed to "columns". In order to provide good back compatibility,
	 * only the section label is changed to "Columns". All other internal references for this section will remain as
	 * "text".
	 *
	 * @return void
	 */
	public static function register() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_filter( 'make_section_choices', array( $this, 'section_choices' ), 10, 3 );
		add_filter( 'make_section_defaults', array( $this, 'section_defaults' ) );
		add_filter( 'make_get_section_json', array ( $this, 'get_section_json' ), 10, 1 );
		add_filter( 'make_builder_js_dependencies', array( $this, 'add_js_dependencies' ) );

		ttfmake_add_section(
			'banner',
			__( 'Banner', 'make' ),
			Make()->scripts()->get_css_directory_uri() . '/builder/sections/images/banner.png',
			__( 'Display multiple types of content in a banner or a slider.', 'make' ),
			array( $this, 'save' ),
			array (
				'banner' => 'sections/banner/builder-template',
				'banner-slide' => 'sections/banner/builder-template-slide'
			),
			'sections/banner/frontend-template',
			300,
			get_template_directory() . '/inc/builder/',
			$this->get_settings(),
			array( 'slide' => $this->get_banner_slide_settings() )
		);
	}

	public function get_settings() {
		return array(
			array(
				'type'    => 'divider',
				'label'   => __( 'General', 'make' ),
				'name'    => 'divider-general',
				'class'   => 'ttfmake-configuration-divider open',
			),
			array(
				'type'  => 'section_title',
				'name'  => 'title',
				'label' => __( 'Enter section title', 'make' ),
				'class' => 'ttfmake-configuration-title ttfmake-section-header-title-input',
				'default' => ttfmake_get_section_default( 'title', 'banner' ),
			),
			array(
				'type'    => 'text',
				'label'   => __( 'Section height (px)', 'make' ),
				'name'    => 'height',
				'default' => ttfmake_get_section_default( 'height', 'banner' ),
			),
			array(
				'type'        => 'select',
				'label'       => __( 'Responsive behavior', 'make' ),
				'name'        => 'responsive',
				'default' => ttfmake_get_section_default( 'responsive', 'banner' ),
				'description' => __( 'Choose how the Banner will respond to varying screen widths. Default is ideal for large amounts of written content, while Aspect is better for showing your images.', 'make' ),
				'options'     => ttfmake_get_section_choices( 'responsive', 'banner' ),
			),
			array(
				'type'  => 'divider',
				'label' => __( 'Background', 'make' ),
				'name'  => 'divider-background',
				'class' => 'ttfmake-configuration-divider',
			),
			array(
				'type'  => 'image',
				'name'  => 'background-image',
				'label' => __( 'Background image', 'make' ),
				'class' => 'ttfmake-configuration-media',
				'default' => ttfmake_get_section_default( 'background-image', 'banner' ),
			),
			array(
				'type'  => 'select',
				'name'  => 'background-position',
				'label' => __( 'Position', 'make' ),
				'class' => 'ttfmake-configuration-media-related',
				'default' => ttfmake_get_section_default( 'background-position', 'banner' ),
				'options' => ttfmake_get_section_choices( 'background-position', 'banner' ),
			),
			array(
				'type'    => 'select',
				'name'    => 'background-style',
				'label'   => __( 'Display', 'make' ),
				'class'   => 'ttfmake-configuration-media-related',
				'default' => ttfmake_get_section_default( 'background-style', 'banner' ),
				'options' => ttfmake_get_section_choices( 'background-style', 'banner' ),
			),
			array(
				'type'    => 'checkbox',
				'label'   => __( 'Darken', 'make' ),
				'name'    => 'darken',
				'default' => ttfmake_get_section_default( 'darken', 'banner' ),
			),
			array(
				'type'    => 'color',
				'label'   => __( 'Background color', 'make' ),
				'name'    => 'background-color',
				'class'   => 'ttfmake-gallery-background-color ttfmake-configuration-color-picker',
				'default' => ttfmake_get_section_default( 'background-color', 'banner' ),
			),
			array(
				'type'    => 'divider',
				'label'   => __( 'Slideshow', 'make' ),
				'name'    => 'divider-slideshow',
				'class'   => 'ttfmake-configuration-divider',
			),
			array(
				'type'    => 'checkbox',
				'label'   => __( 'Navigation arrows', 'make' ),
				'name'    => 'arrows',
				'default' => ttfmake_get_section_default( 'arrows', 'banner' ),
			),
			array(
				'type'    => 'checkbox',
				'label'   => __( 'Navigation dots', 'make' ),
				'name'    => 'dots',
				'default' => ttfmake_get_section_default( 'dots', 'banner' ),
			),
			array(
				'type'    => 'checkbox',
				'label'   => __( 'Autoplay slideshow', 'make' ),
				'name'    => 'autoplay',
				'default' => ttfmake_get_section_default( 'autoplay', 'banner' ),
			),
			array(
				'type'    => 'select',
				'label'   => __( 'Speed', 'make' ),
				'name'    => 'delay',
				'default' => ttfmake_get_section_default( 'delay', 'banner' ),
				'options' => ttfmake_get_section_choices( 'delay', 'banner' )
			),
			array(
				'type'    => 'select',
				'label'   => __( 'Transition effect', 'make' ),
				'name'    => 'transition',
				'default' => ttfmake_get_section_default( 'transition', 'banner' ),
				'options' => ttfmake_get_section_choices( 'transition', 'banner' ),
			),
		);
	}

	public function get_banner_slide_settings() {
		$inputs = array(
			array(
				'type'    => 'select',
				'name'    => 'alignment',
				'label'   => __( 'Content position', 'make' ),
				'default' => ttfmake_get_section_default( 'alignment', 'banner-slide' ),
				'options' => array(
					'none'  => __( 'None', 'make' ),
					'left'  => __( 'Left', 'make' ),
					'right' => __( 'Right', 'make' ),
				),
			),
			array(
				'type'    => 'checkbox',
				'label'   => __( 'Darken', 'make' ),
				'name'    => 'darken',
				'default' => ttfmake_get_section_default( 'darken', 'banner-slide' )
			),
			array(
				'type'    => 'color',
				'label'   => __( 'Background color', 'make' ),
				'name'    => 'background-color',
				'class'   => 'ttfmake-gallery-background-color ttfmake-configuration-color-picker',
				'default' => ttfmake_get_section_default( 'background-color', 'banner-slide' )
			),
		);

		/**
		 * Filter the definitions of the Banner slide configuration inputs.
		 *
		 * @since 1.4.0.
		 *
		 * @param array    $inputs    The input definition array.
		 */
		$inputs = apply_filters( 'make_banner_slide_configuration', $inputs );

		// Sort the config in case 3rd party code added another input
		ksort( $inputs, SORT_NUMERIC );

		return $inputs;
	}

	/**
	 * Add new section choices.
	 *
	 * @since 1.8.8.
	 *
	 * @hooked filter make_section_choices
	 *
	 * @param array  $choices         The existing choices.
	 * @param string $key             The key for the section setting.
	 * @param string $section_type    The section type.
	 *
	 * @return array                  The choices for the particular section_type / key combo.
	 */
	public function section_choices( $choices, $key, $section_type ) {
		if ( count( $choices ) > 1 || ! in_array( $section_type, array( 'banner' ) ) ) {
			return $choices;
		}

		$choice_id = "$section_type-$key";

		switch ( $choice_id ) {
			case 'banner-delay':
				$choices = array(
					'9000' => __( 'Slow', 'make' ),
					'6000' => __( 'Default', 'make' ),
					'3000' => __( 'Fast', 'make' ),
				);
				break;

			case 'banner-transition':
				$choices = array(
					'scrollHorz' => __( 'Slide horizontal', 'make' ),
					'fade' => __( 'Fade', 'make' ),
					'none' => __( 'None', 'make' ),
				);
				break;

			case 'banner-responsive' :
				$choices = array(
					'balanced' => __( 'Default', 'make' ),
					'aspect'   => __( 'Aspect', 'make' ),
				);
				break;

			case 'banner-background-style':
				$choices = array(
					'tile'  => __( 'Tile', 'make' ),
					'cover' => __( 'Cover', 'make' ),
					'contain' => __( 'Contain', 'make' ),
				);
				break;

			case 'banner-background-position' :
				$choices = array(
					'center-top'  => __( 'Top', 'make' ),
					'center-center' => __( 'Center', 'make' ),
					'center-bottom' => __( 'Bottom', 'make' ),
					'left-center'  => __( 'Left', 'make' ),
					'right-center' => __( 'Right', 'make' )
				);
				break;
		}

		return $choices;
	}

	/**
	 * Get default values for banner section
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'title' => '',
			'arrows' => 1,
			'dots' => 1,
			'autoplay' => 1,
			'delay' => 6000,
			'transition' => 'scrollHorz',
			'height' => 600,
			'responsive' => 'balanced',
			'background-image' => '',
			'background-position' => 'center-center',
			'darken' => 0,
			'background-style' => 'cover',
			'background-color' => '',
		);
	}

	/**
	 * Get default values for banner slide
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function get_slide_defaults() {
		return array(
			'alignment' => 'none',
			'darken' => 0,
			'background-color' => '',
			'content' => '',
			'image-id' => '',
		);
	}

	/**
	 * Extract the setting defaults and add them to Make's section defaults system.
	 *
	 * @since 1.6.0.
	 *
	 * @hooked filter make_section_defaults
	 *
	 * @param array $defaults    The existing array of section defaults.
	 *
	 * @return array             The modified array of section defaults.
	 */
	public function section_defaults( $defaults ) {
		$defaults['banner'] = $this->get_defaults();
		$defaults['banner-slide'] = $this->get_slide_defaults();

		return $defaults;
	}

	/**
	 * Filter the json representation of this section.
	 *
	 * @since 1.8.0.
	 *
	 * @hooked filter make_get_section_json
	 *
	 * @param array $defaults    The array of data for this section.
	 *
	 * @return array             The modified array to be jsonified.
	 */
	public function get_section_json( $data ) {
		if ( $data['section-type'] == 'banner' ) {
			$data = wp_parse_args( $data, $this->get_defaults() );
			$image = ttfmake_get_image_src( $data['background-image'], 'large' );

			if ( isset( $image[0] ) ) {
				$data['background-image-url'] = $image[0];
			}

			if ( isset( $data['banner-slides'] ) && is_array( $data['banner-slides'] ) ) {
				foreach ( $data['banner-slides'] as $s => $slide ) {
					$slide = wp_parse_args( $slide, $this->get_slide_defaults() );

					// Handle legacy data layout
					$id = isset( $slide['id'] ) ? $slide['id']: $s;
					$data['banner-slides'][$s]['id'] = $id;
					$slide_image = ttfmake_get_image_src( $slide['image-id'], 'large' );

					if ( isset( $slide_image[0] ) ) {
						$data['banner-slides'][$s]['image-url'] = $slide_image[0];
					}
				}

				if ( isset( $data['banner-slide-order'] ) ) {
					$ordered_items = array();

					foreach ( $data['banner-slide-order'] as $item_id ) {
						array_push( $ordered_items, $data['banner-slides'][$item_id] );
					}

					$data['banner-slides'] = $ordered_items;
					unset( $data['banner-slide-order'] );
				}

				/*
				 * Back compatibility code for changing negative phrased checkboxes.
				 *
				 * @since 1.8.8.
				 */
				if ( isset( $data['hide-dots'] ) ) {
					$data['dots'] = ( absint( $data['hide-dots'] ) == 1 ) ? 0 : 1;
				}

				if ( isset( $data['hide-arrows'] ) ) {
					$data['arrows'] = ( absint( $data['hide-arrows'] ) == 1 ) ? 0 : 1;
				}

				/*
				 * Back compatibility for speed (time between slides). Set it to default value when
				 * the original value is other than one found in available section choices.
				 *
				 * @since 1.8.8.
				 */
				 if( isset( $data['delay'] ) && !in_array( $data['delay'], ttfmake_get_section_choices( 'delay', 'banner' ) ) ) {
					 $data['delay'] = ttfmake_get_section_default( 'delay', 'banner' );
				 }
			}
		}

		return $data;
	}

	/**
	 * Save the data for the section.
	 *
	 * @param  array    $data    The data from the $_POST array for the section.
	 * @return array             The cleaned data.
	 */
	public function save( $data ) {
		$data = wp_parse_args( $data, $this->get_defaults() );

		$clean_data = array();
		$clean_data['title']       = $clean_data['label'] = ( isset( $data['title'] ) ) ? apply_filters( 'title_save_pre', $data['title'] ) : '';
		$clean_data['arrows'] = ( isset( $data['arrows'] ) && 1 === (int) $data['arrows'] ) ? 1 : 0;
		$clean_data['dots']   = ( isset( $data['dots'] ) && 1 === (int) $data['dots'] ) ? 1 : 0;
		$clean_data['autoplay']    = ( isset( $data['autoplay'] ) && 1 === (int) $data['autoplay'] ) ? 1 : 0;

		if ( isset( $data['transition'] ) && in_array( $data['transition'], array( 'fade', 'scrollHorz', 'none' ) ) ) {
			$clean_data['transition'] = $data['transition'];
		}

		if ( isset( $data['delay'] ) ) {
			$clean_data['delay'] = absint( $data['delay'] );
		}

		if ( isset( $data['height'] ) ) {
			$clean_data['height'] = absint( $data['height'] );
		}

		if ( isset( $data['responsive'] ) ) {
			$clean_data['responsive'] = ttfmake_sanitize_section_choice( $data['responsive'], 'responsive', $data['section-type'] );
		}

		if ( isset( $data['background-image'] ) ) {
			$clean_data['background-image'] = ttfmake_sanitize_image_id( $data['background-image'] );
		}

		if ( isset( $data['darken'] ) && (int) $data['darken'] == 1 ) {
			$clean_data['darken'] = 1;
		} else {
			$clean_data['darken'] = 0;
		}

		if ( isset( $data['background-color'] ) ) {
			$clean_data['background-color'] = maybe_hash_hex_color( $data['background-color'] );
		}

		if ( isset( $data['background-style'] ) ) {
			$clean_data['background-style'] = ttfmake_sanitize_section_choice( $data['background-style'], 'background-style', $data['section-type'] );
		}

		if ( isset( $data['background-position'] ) ) {
			$clean_data['background-position'] = ttfmake_sanitize_section_choice( $data['background-position'], 'background-position', $data['section-type'] );
		}

		if ( isset( $data['banner-slides'] ) && is_array( $data['banner-slides'] ) ) {
			$clean_data['banner-slides'] = array();

			foreach ( $data['banner-slides'] as $s => $slide ) {
				// Handle legacy data layout
				$id = isset( $slide['id'] ) ? $slide['id']: $s;

				$clean_slide_data = array( 'id' => $id );

				if ( isset( $slide['content'] ) ) {
					$clean_slide_data['content'] = sanitize_post_field( 'post_content', $slide['content'], ( get_post() ) ? get_the_ID() : 0, 'db' );
				}

				if ( isset( $slide['background-color'] ) ) {
					$clean_slide_data['background-color'] = maybe_hash_hex_color( $slide['background-color'] );
				}

				$clean_slide_data['darken'] = ( isset( $slide['darken'] ) && 1 === (int) $slide['darken'] ) ? 1 : 0;

				if ( isset( $slide['image-id'] ) ) {
					$clean_slide_data['image-id'] = ttfmake_sanitize_image_id( $slide['image-id'] );
				}

				$clean_slide_data['alignment'] = ( isset( $slide['alignment'] ) && in_array( $slide['alignment'], array( 'none', 'left', 'right' ) ) ) ? $slide['alignment'] : 'none';

				if ( isset( $slide['state'] ) ) {
					$clean_slide_data['state'] = ( in_array( $slide['state'], array( 'open', 'closed' ) ) ) ? $slide['state'] : 'open';
				}

				array_push( $clean_data['banner-slides'], $clean_slide_data );
			}
		}

		return $clean_data;
	}

	/**
	 * Add JS dependencies for the section
	 *
	 * @return array
	 */
	public function add_js_dependencies( $deps ) {
		if ( ! is_array( $deps ) ) {
			$deps = array();
		}

		wp_register_script(
			'builder-models-banner',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/models/banner.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'builder-models-banner-slide',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/models/banner-slide.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'builder-views-banner-slide',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/banner-slide.js',
			array( 'builder-views-item' ),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'builder-views-banner',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/banner.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		return array_merge( $deps, array(
			'builder-models-banner',
			'builder-models-banner-slide',
			'builder-views-banner-slide',
			'builder-views-banner'
		) );
	}
}
endif;

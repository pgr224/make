<?php
/**
 * @package Make
 */

if ( ! class_exists( 'TTFMAKE_Section_Definitions' ) ) :
/**
 * Collector for builder sections.
 *
 * @since 1.0.0.
 *
 * Class TTFMAKE_Section_Definitions
 */
class TTFMAKE_Section_Definitions {
	/**
	 * The one instance of TTFMAKE_Section_Definitions.
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMAKE_Section_Definitions
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTFMAKE_Section_Definitions instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_Section_Definitions
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the sections.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_Section_Definitions
	 */
	public function __construct() {
		// Register all of the sections via the section API
		$this->register_text_section();
		$this->register_banner_section();
		$this->register_gallery_section();

		// Add the section JS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Add additional templating
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
	}

	/**
	 * Register the text section.
	 *
	 * Note that in 1.4.0, the "text" section was renamed to "columns". In order to provide good back compatibility,
	 * only the section label is changed to "Columns". All other internal references for this section will remain as
	 * "text".
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function register_text_section() {
		ttfmake_add_section(
			'text',
			__( 'Columns', 'make' ),
			Make()->scripts()->get_css_directory_uri() . '/builder/sections/images/text.png',
			__( 'Create rearrangeable columns of content and images.', 'make' ),
			array( $this, 'save_text' ),
			'sections/builder-templates/text',
			'sections/front-end-templates/text',
			100,
			get_template_directory() . '/inc/builder/',
			array(
				100 => array(
					'type'  => 'section_title',
					'name'  => 'title',
					'label' => __( 'Enter section title', 'make' ),
					'class' => 'ttfmake-configuration-title ttfmake-section-header-title-input',
				),
				200 => array(
					'type'    => 'select',
					'name'    => 'columns-number',
					'class'   => 'ttfmake-text-columns',
					'label'   => __( 'Columns', 'make' ),
					'default' => 3,
					'options' => array(
						1 => 1,
						2 => 2,
						3 => 3,
						4 => 4,
					),
				),
				300 => array(
					'type'  => 'image',
					'name'  => 'background-image',
					'label' => __( 'Background image', 'make' ),
					'class' => 'ttfmake-configuration-media'
				),
				400 => array(
					'type'    => 'checkbox',
					'label'   => __( 'Darken background to improve readability', 'make' ),
					'name'    => 'darken',
					'default' => 0,
				),
				500 => array(
					'type'    => 'select',
					'name'    => 'background-style',
					'label'   => __( 'Background style', 'make' ),
					'default' => 'tile',
					'options' => array(
						'tile'  => __( 'Tile', 'make' ),
						'cover' => __( 'Cover', 'make' ),
					),
				),
				600 => array(
					'type'    => 'color',
					'label'   => __( 'Background color', 'make' ),
					'name'    => 'background-color',
					'class'   => 'ttfmake-text-background-color ttfmake-configuration-color-picker',
					'default' => '{{ get("background-color") }}'
				),
			)
		);

		add_filter( 'make_get_section_data', array ( $this, 'get_text_section_data' ), 1 );
	}

	public function get_text_section_data( $ordered_data ) {
		foreach ( $ordered_data as $section_id => $section ) {
			if ( isset( $section['background-image'] ) ) {
				$image_id = intval( $section['background-image'] );
				$image = ttfmake_get_image_src( $image_id, 'large' );
				$ordered_data[$section_id]['background-image-url'] = $image[0];
			}

			if ( isset( $section['columns'] ) && is_array( $section['columns'] ) ) {
				foreach ( $section['columns'] as $column_id => $column ) {
					if ( isset( $column['image-id'] ) ) {
						$image_id = $column['image-id'];
						$image = ttfmake_get_image_src( $image_id, 'large' );
						$ordered_data[$section_id]['columns'][$column_id]['image-url'] = $image[0];
					}
				}
			}
		}

		return $ordered_data;
	}

	/**
	 * Save the data for the text section.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $data    The data from the $_POST array for the section.
	 * @return array             The cleaned data.
	 */
	public function save_text( $data ) {
		$clean_data = array();

		if ( isset( $data['columns-number'] ) ) {
			if ( in_array( $data['columns-number'], range( 1, 4 ) ) ) {
				$clean_data['columns-number'] = $data['columns-number'];
			}
		}

		$clean_data['title'] = $clean_data['label'] = ( isset( $data['title'] ) ) ? apply_filters( 'title_save_pre', $data['title'] ) : '';

		if ( isset( $data['columns-order'] ) ) {
			$clean_data['columns-order'] = array_map( array( 'TTFMAKE_Builder_Save', 'clean_section_id' ), explode( ',', $data['columns-order'] ) );
		}

		if ( isset( $data['background-image'] ) ) {
			$clean_data['background-image'] = ttfmake_sanitize_image_id( $data['background-image'] );
		}

		if ( isset( $data['darken'] ) && $data['darken'] == 1 ) {
			$clean_data['darken'] = 1;
		} else {
			$clean_data['darken'] = 0;
		}

		if ( isset( $data['background-color'] ) ) {
			$clean_data['background-color'] = maybe_hash_hex_color( $data['background-color'] );
		}

		if ( isset( $data['background-style'] ) ) {
			if ( in_array( $data['background-style'], array( 'tile', 'cover' ) ) ) {
				$clean_data['background-style'] = $data['background-style'];
			}
		}

		if ( isset( $data['columns'] ) && is_array( $data['columns'] ) ) {
			foreach ( $data['columns'] as $id => $item ) {
				if ( isset( $item['title'] ) ) {
					$clean_data['columns'][ $id ]['title'] = apply_filters( 'title_save_pre', $item['title'] );
				}

				if ( isset( $item['image-link'] ) ) {
					$clean_data['columns'][ $id ]['image-link'] = esc_url_raw( $item['image-link'] );
				}

				if ( isset( $item['image-id'] ) ) {
					$clean_data['columns'][ $id ]['image-id'] = ttfmake_sanitize_image_id( $item['image-id'] );
				}

				if ( isset( $item['content'] ) ) {
					$clean_data['columns'][ $id ]['content'] = sanitize_post_field( 'post_content', $item['content'], ( get_post() ) ? get_the_ID() : 0, 'db' );
				}
			}
		}

		return $clean_data;
	}

	/**
	 * Register the banner section.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function register_banner_section() {
		ttfmake_add_section(
			'banner',
			__( 'Banner', 'make' ),
			Make()->scripts()->get_css_directory_uri() . '/builder/sections/images/banner.png',
			__( 'Display multiple types of content in a banner or a slider.', 'make' ),
			array( $this, 'save_banner' ),
			array ( 'sections/builder-templates/banner', 'sections/builder-templates/banner-slide' ),
			'sections/front-end-templates/banner',
			300,
			get_template_directory() . '/inc/builder/',
			array(
				100 => array(
					'type'  => 'section_title',
					'name'  => 'title',
					'label' => __( 'Enter section title', 'make' ),
					'class' => 'ttfmake-configuration-title ttfmake-section-header-title-input',
				),
				200 => array(
					'type'    => 'checkbox',
					'label'   => __( 'Hide navigation arrows', 'make' ),
					'name'    => 'hide-arrows',
					'default' => 0
				),
				300 => array(
					'type'    => 'checkbox',
					'label'   => __( 'Hide navigation dots', 'make' ),
					'name'    => 'hide-dots',
					'default' => 0
				),
				400 => array(
					'type'    => 'checkbox',
					'label'   => __( 'Autoplay slideshow', 'make' ),
					'name'    => 'autoplay',
					'default' => 1
				),
				500 => array(
					'type'    => 'text',
					'label'   => __( 'Time between slides (ms)', 'make' ),
					'name'    => 'delay',
					'default' => 6000
				),
				600 => array(
					'type'    => 'select',
					'label'   => __( 'Transition effect', 'make' ),
					'name'    => 'transition',
					'default' => 'scrollHorz',
					'options' => array(
						'scrollHorz' => __( 'Slide horizontal', 'make' ),
						'fade'       => __( 'Fade', 'make' ),
						'none'       => __( 'None', 'make' ),
					)
				),
				700 => array(
					'type'    => 'text',
					'label'   => __( 'Section height (px)', 'make' ),
					'name'    => 'height',
					'default' => 600
				),
				800 => array(
					'type'        => 'select',
					'label'       => __( 'Responsive behavior', 'make' ),
					'name'        => 'responsive',
					'default'     => 'balanced',
					'description' => __( 'Choose how the Banner will respond to varying screen widths. Default is ideal for large amounts of written content, while Aspect is better for showing your images.', 'make' ),
					'options'     => array(
						'balanced' => __( 'Default', 'make' ),
						'aspect'   => __( 'Aspect', 'make' ),
					)
				),
				900 => array(
					'type'  => 'image',
					'name'  => 'background-image',
					'label' => __( 'Background image', 'make' ),
					'class' => 'ttfmake-configuration-media'
				),
				1000 => array(
					'type'    => 'checkbox',
					'label'   => __( 'Darken background to improve readability', 'make' ),
					'name'    => 'darken',
					'default' => 0,
				),
				1100 => array(
					'type'    => 'select',
					'name'    => 'background-style',
					'label'   => __( 'Background style', 'make' ),
					'default' => 'tile',
					'options' => array(
						'tile'  => __( 'Tile', 'make' ),
						'cover' => __( 'Cover', 'make' ),
					),
				),
				1200 => array(
					'type'    => 'color',
					'label'   => __( 'Background color', 'make' ),
					'name'    => 'background-color',
					'class'   => 'ttfmake-gallery-background-color ttfmake-configuration-color-picker',
					'default' => '',
				),

				'item' => $this->get_banner_slide_defaults(),
			)
		);

		add_filter( 'make_get_section_data', array ( $this, 'get_banner_section_data' ), 1 );
	}

	private function get_banner_slide_defaults() {
		$inputs = array(
			100 => array(
				'type'    => 'select',
				'name'    => 'alignment',
				'label'   => __( 'Content position', 'make' ),
				'default' => 'none',
				'options' => array(
					'none'  => __( 'None', 'make' ),
					'left'  => __( 'Left', 'make' ),
					'right' => __( 'Right', 'make' ),
				),
			),
			200 => array(
				'type'    => 'checkbox',
				'label'   => __( 'Darken background to improve readability', 'make' ),
				'name'    => 'darken',
				'default' => 0
			),
			300 => array(
				'type'    => 'color',
				'label'   => __( 'Background color', 'make' ),
				'name'    => 'background-color',
				'class'   => 'ttfmake-gallery-background-color ttfmake-configuration-color-picker',
				'default' => '',
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
	 * Save the data for the banner section.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $data    The data from the $_POST array for the section.
	 * @return array             The cleaned data.
	 */
	public function save_banner( $data ) {
		$clean_data = array();

		$clean_data['title']       = $clean_data['label'] = ( isset( $data['title'] ) ) ? apply_filters( 'title_save_pre', $data['title'] ) : '';
		$clean_data['hide-arrows'] = ( isset( $data['hide-arrows'] ) && 1 === (int) $data['hide-arrows'] ) ? 1 : 0;
		$clean_data['hide-dots']   = ( isset( $data['hide-dots'] ) && 1 === (int) $data['hide-dots'] ) ? 1 : 0;
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

		if ( isset( $data['responsive'] ) && in_array( $data['responsive'], array( 'aspect', 'balanced' ) ) ) {
			$clean_data['responsive'] = $data['responsive'];
		}

		if ( isset( $data['banner-slide-order'] ) ) {
			$clean_data['banner-slide-order'] = array_map( array( 'TTFMAKE_Builder_Save', 'clean_section_id' ), explode( ',', $data['banner-slide-order'] ) );
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
			if ( in_array( $data['background-style'], array( 'tile', 'cover' ) ) ) {
				$clean_data['background-style'] = $data['background-style'];
			}
		}

		if ( isset( $data['banner-slides'] ) && is_array( $data['banner-slides'] ) ) {
			foreach ( $data['banner-slides'] as $slide ) {
				$id = $slide['id'];

				if ( empty ( $id ) ) {
					continue;
				}

				$clean_data['banner-slides'][ $id ]['id'] = $id;

				if ( isset( $slide['content'] ) ) {
					$clean_data['banner-slides'][ $id ]['content'] = sanitize_post_field( 'post_content', $slide['content'], ( get_post() ) ? get_the_ID() : 0, 'db' );
				}

				if ( isset( $slide['background-color'] ) ) {
					$clean_data['banner-slides'][ $id ]['background-color'] = maybe_hash_hex_color( $slide['background-color'] );
				}

				$clean_data['banner-slides'][ $id ]['darken'] = ( isset( $slide['darken'] ) && 1 === (int) $slide['darken'] ) ? 1 : 0;

				if ( isset( $slide['image-id'] ) ) {
					$clean_data['banner-slides'][ $id ]['image-id'] = ttfmake_sanitize_image_id( $slide['image-id'] );
				}

				$clean_data['banner-slides'][ $id ]['alignment'] = ( isset( $slide['alignment'] ) && in_array( $slide['alignment'], array( 'none', 'left', 'right' ) ) ) ? $slide['alignment'] : 'none';

				if ( isset( $slide['state'] ) ) {
					$clean_data['banner-slides'][ $id ]['state'] = ( in_array( $slide['state'], array( 'open', 'closed' ) ) ) ? $slide['state'] : 'open';
				}
			}
		}

		return $clean_data;
	}

	public function get_banner_section_data( $ordered_data ) {
		foreach ( $ordered_data as $section_id => $section ) {
			if ( isset( $section['background-image'] ) ) {
				$image_id = intval( $section['background-image'] );
				$image = ttfmake_get_image_src( $image_id, 'large' );
				$ordered_data[$section_id]['background-image-url'] = $image[0];
			}

			if ( isset( $section['banner-slides'] ) && is_array( $section['banner-slides'] ) ) {
				foreach ( $section['banner-slides'] as $slide_id => $slide ) {
					if ( isset( $slide['image-id'] ) ) {
						$image_id = $slide['image-id'];
						$image = ttfmake_get_image_src( $image_id, 'large' );
						$ordered_data[$section_id]['banner-slides'][$slide_id]['image-url'] = $image[0];
					}
				}
			}
		}

		return $ordered_data;
	}

	/**
	 * Register the gallery section.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function register_gallery_section() {
		ttfmake_add_section(
			'gallery',
			__( 'Gallery', 'make' ),
			Make()->scripts()->get_css_directory_uri() . '/builder/sections/images/gallery.png',
			__( 'Display your images in various grid combinations.', 'make' ),
			array( $this, 'save_gallery' ),
			'sections/builder-templates/gallery',
			'sections/front-end-templates/gallery',
			400,
			get_template_directory() . '/inc/builder/',
			array(
				100 => array(
					'type'  => 'section_title',
					'name'  => 'title',
					'label' => __( 'Enter section title', 'make' ),
					'class' => 'ttfmake-configuration-title ttfmake-section-header-title-input',
				),
				200 => array(
					'type'    => 'select',
					'name'    => 'columns',
					'label'   => __( 'Columns', 'make' ),
					'class'   => 'ttfmake-gallery-columns',
					'default' => 3,
					'options' => array(
						1 => 1,
						2 => 2,
						3 => 3,
						4 => 4,
					)
				),
				300 => array(
					'type'    => 'select',
					'name'    => 'aspect',
					'label'   => __( 'Aspect ratio', 'make' ),
					'default' => 'square',
					'options' => array(
						'square'    => __( 'Square', 'make' ),
						'landscape' => __( 'Landscape', 'make' ),
						'portrait'  => __( 'Portrait', 'make' ),
						'none'      => __( 'None', 'make' ),
					)
				),
				400 => array(
					'type'    => 'select',
					'name'    => 'captions',
					'label'   => __( 'Caption style', 'make' ),
					'default' => 'reveal',
					'options' => array(
						'reveal'  => __( 'Reveal', 'make' ),
						'overlay' => __( 'Overlay', 'make' ),
						'none'    => __( 'None', 'make' ),
					)
				),
				500 => array(
					'type'    => 'select',
					'name'    => 'caption-color',
					'label'   => __( 'Caption color', 'make' ),
					'default' => 'light',
					'options' => array(
						'light'  => __( 'Light', 'make' ),
						'dark' => __( 'Dark', 'make' ),
					)
				),
				600 => array(
					'type'  => 'image',
					'name'  => 'background-image',
					'label' => __( 'Background image', 'make' ),
					'class' => 'ttfmake-configuration-media'
				),
				700 => array(
					'type'    => 'checkbox',
					'label'   => __( 'Darken background to improve readability', 'make' ),
					'name'    => 'darken',
					'default' => 0,
				),
				800 => array(
					'type'    => 'select',
					'name'    => 'background-style',
					'label'   => __( 'Background style', 'make' ),
					'default' => 'tile',
					'options' => array(
						'tile'  => __( 'Tile', 'make' ),
						'cover' => __( 'Cover', 'make' ),
					),
				),
				900 => array(
					'type'    => 'color',
					'label'   => __( 'Background color', 'make' ),
					'name'    => 'background-color',
					'class'   => 'ttfmake-gallery-background-color ttfmake-configuration-color-picker',
					'default' => '{{ get("background-color") }}',
				),
			)
		);
	}

	/**
	 * Save the data for the gallery section.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $data    The data from the $_POST array for the section.
	 * @return array             The cleaned data.
	 */
	public function save_gallery( $data ) {
		$clean_data = array();

		if ( isset( $data['columns'] ) ) {
			if ( in_array( $data['columns'], range( 1, 4 ) ) ) {
				$clean_data['columns'] = $data['columns'];
			}
		}

		if ( isset( $data['caption-color'] ) ) {
			if ( in_array( $data['caption-color'], array( 'light', 'dark' ) ) ) {
				$clean_data['caption-color'] = $data['caption-color'];
			}
		}

		if ( isset( $data['captions'] ) ) {
			if ( in_array( $data['captions'], array( 'none', 'overlay', 'reveal' ) ) ) {
				$clean_data['captions'] = $data['captions'];
			}
		}

		if ( isset( $data['aspect'] ) ) {
			if ( in_array( $data['aspect'], array( 'none', 'landscape', 'portrait', 'square' ) ) ) {
				$clean_data['aspect'] = $data['aspect'];
			}
		}

		if ( isset( $data['background-image']['image-id'] ) ) {
			$clean_data['background-image'] = ttfmake_sanitize_image_id( $data['background-image']['image-id'] );
		}

		if ( isset( $data['title'] ) ) {
			$clean_data['title'] = $clean_data['label'] = apply_filters( 'title_save_pre', $data['title'] );
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
			if ( in_array( $data['background-style'], array( 'tile', 'cover' ) ) ) {
				$clean_data['background-style'] = $data['background-style'];
			}
		}

		if ( isset( $data['gallery-item-order'] ) ) {
			$clean_data['gallery-item-order'] = array_map( array( 'TTFMAKE_Builder_Save', 'clean_section_id' ), explode( ',', $data['gallery-item-order'] ) );
		}

		if ( isset( $data['gallery-items'] ) && is_array( $data['gallery-items'] ) ) {
			foreach ( $data['gallery-items'] as $id => $item ) {
				if ( isset( $item['title'] ) ) {
					$clean_data['gallery-items'][ $id ]['title'] = apply_filters( 'title_save_pre', $item['title'] );
				}

				if ( isset( $item['link'] ) ) {
					$clean_data['gallery-items'][ $id ]['link'] = esc_url_raw( $item['link'] );
				}

				if ( isset( $item['description'] ) ) {
					$clean_data['gallery-items'][ $id ]['description'] = sanitize_post_field( 'post_content', $item['description'], ( get_post() ) ? get_the_ID() : 0, 'db' );
				}

				if ( isset( $item['image-id'] ) ) {
					$clean_data['gallery-items'][ $id ]['image-id'] = ttfmake_sanitize_image_id( $item['image-id'] );
				}
			}
		}

		return $clean_data;
	}

	/**
	 * Enqueue the JS and CSS for the admin.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $hook_suffix    The suffix for the screen.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		// Only load resources if they are needed on the current page
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) || ! ttfmake_post_type_supports_builder( get_post_type() ) ) {
			return;
		}

		wp_register_script(
			'ttfmake-sections/js/models/gallery-item.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/models/gallery-item.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/views/gallery-item.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/gallery-item.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/views/gallery.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/gallery.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/models/text.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/models/text.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/views/text.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/text.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/models/banner.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/models/banner.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/models/banner-slide.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/models/banner-slide.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/views/banner-slide.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/banner-slide.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/views/banner.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/banner.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-sections/js/views/item.js',
			Make()->scripts()->get_js_directory_uri() . '/builder/sections/views/item.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		// Add additional dependencies to the Builder JS
		add_filter( 'make_builder_js_dependencies', array( $this, 'add_js_dependencies' ) );

		// Add the section CSS
		wp_enqueue_style(
			'ttfmake-sections/css/sections.css',
			Make()->scripts()->get_css_directory_uri() . '/builder/sections/sections.css',
			array(),
			TTFMAKE_VERSION,
			'all'
		);
	}

	/**
	 * Append more JS to the list of JS deps.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $deps    The current deps.
	 * @return array             The modified deps.
	 */
	public function add_js_dependencies( $deps ) {
		if ( ! is_array( $deps ) ) {
			$deps = array();
		}

		return array_merge( $deps, array(
			'ttfmake-sections/js/views/item.js',
			'ttfmake-sections/js/models/gallery-item.js',
			'ttfmake-sections/js/models/banner.js',
			'ttfmake-sections/js/models/banner-slide.js',
			'ttfmake-sections/js/views/gallery-item.js',
			'ttfmake-sections/js/views/gallery.js',
			'ttfmake-sections/js/models/text.js',
			'ttfmake-sections/js/views/text.js',
			'ttfmake-sections/js/views/banner-slide.js',
			'ttfmake-sections/js/views/banner.js',
		) );
	}

	/**
	 * Print out the JS section templates
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function print_templates() {
		global $hook_suffix, $typenow, $ttfmake_is_js_template;
		$ttfmake_is_js_template = true;

		// Only show when adding/editing pages
		if ( ! ttfmake_post_type_supports_builder( $typenow ) || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) )) {
			return;
		}

		/**
		 * Array of items to print as JS templates in the footer of the Builder screen.
		 *
		 * Each item is represented as an associative array and includes the following items:
		 * - id                  The ID of the template
		 * - builder_template    The relative path to the PHP template
		 * - path                The path to the base directory
		 *
		 * @since 1.6.0.
		 *
		 * @param array    $templates    The
		 */
		$templates = apply_filters( 'make_builder_js_templates', array(
			array(
				'id' => 'gallery-item',
				'builder_template' => 'sections/builder-templates/gallery-item',
				'path' => 'inc/builder/',
			),
			array(
				'id' => 'banner-slide',
				'builder_template' => 'sections/builder-templates/banner-slide',
				'path' => 'inc/builder/',
			),
		) );

		// Print the templates
		foreach ( $templates as $template ) : ?>
		<script type="text/html" id="tmpl-ttfmake-<?php echo $template['id']; ?>">
			<?php
			ob_start();
			ttfmake_get_builder_base()->load_section( $template, array() );
			$html = ob_get_clean();
			$html = str_replace(
				array(
					'temp',
				),
				array(
					'{{{ id }}}',
				),
				$html
			);
			echo $html;
			?>
		</script>
		<?php endforeach;
		unset( $GLOBALS['ttfmake_is_js_template'] );
	}

	/**
	 * An array of defaults for all the Builder section settings
	 *
	 * @since  1.0.4.
	 *
	 * @return array    The section defaults.
	 */
	public function get_section_defaults() {
		// Note that this function does not do anything yet. It is part of an API refresh that is happening over time.
		$defaults = array();

		/**
		 * Filter the section defaults.
		 *
		 * @since 1.2.3.
		 *
		 * @param array    $defaults    The default section data
		 */
		return apply_filters( 'make_section_defaults', $defaults );
	}

	/**
	 * Define the choices for section setting dropdowns.
	 *
	 * @since  1.0.4.
	 *
	 * @param  string    $key             The key for the section setting.
	 * @param  string    $section_type    The section type.
 	 * @return array                      The array of choices for the section setting.
	 */
	public function get_choices( $key, $section_type ) {
		$choices = array( 0 );

		$choice_id = "$section_type-$key";

		// Note that this function does not do anything yet. It is part of an API refresh that is happening over time.
		switch ( $choice_id ) {}

		/**
		 * Filter the section choices.
		 *
		 * @since 1.2.3.
		 *
		 * @param array    $choices         The default section choices.
		 * @param string   $key             The key for the data.
		 * @param string   $section_type    The type of section this relates to.
		 */
		return apply_filters( 'make_section_choices', $choices, $key, $section_type );
	}
}
endif;

if ( ! function_exists( 'ttfmake_get_section_default' ) ) :
/**
 * Return the default value for a particular section setting.
 *
 * @since 1.0.4.
 *
 * @param  string    $key             The key for the section setting.
 * @param  string    $section_type    The section type.
 * @return mixed                      Default value if found; false if not found.
 */
function ttfmake_get_section_default( $key, $section_type ) {
	$defaults = ttfmake_get_section_definitions()->get_section_defaults();
	$id       = "$section_type-$key";
	$value    = ( isset( $defaults[ $id ] ) ) ? $defaults[ $id ] : false;

	/**
	 * Filter the default section data that is received.
	 *
	 * @since 1.2.3.
	 *
	 * @param mixed     $value           The section value.
	 * @param string    $key             The key to get data for.
	 * @param string    $section_type    The type of section the data is for.
	 */
	return apply_filters( 'make_get_section_default', $value, $key, $section_type );
}
endif;

if ( ! function_exists( 'ttfmake_get_section_choices' ) ) :
/**
 * Wrapper function for TTFMAKE_Section_Definitions->get_choices
 *
 * @since 1.0.4.
 *
 * @param  string    $key             The key for the section setting.
 * @param  string    $section_type    The section type.
 * @return array                      The array of choices for the section setting.
 */
function ttfmake_get_section_choices( $key, $section_type ) {
	return ttfmake_get_section_definitions()->get_choices( $key, $section_type );
}
endif;

if ( ! function_exists( 'ttfmake_sanitize_section_choice' ) ) :
/**
 * Sanitize a value from a list of allowed values.
 *
 * @since 1.0.4.
 *
 * @param  string|int $value The current value of the section setting.
 * @param  string        $key             The key for the section setting.
 * @param  string        $section_type    The section type.
 * @return mixed                          The sanitized value.
 */
function ttfmake_sanitize_section_choice( $value, $key, $section_type ) {
	$choices         = ttfmake_get_section_choices( $key, $section_type );
	$allowed_choices = array_keys( $choices );

	if ( ! in_array( $value, $allowed_choices ) ) {
		$value = ttfmake_get_section_default( $key, $section_type );
	}

	/**
	 * Allow developers to alter a section choice during the sanitization process.
	 *
	 * @since 1.2.3.
	 *
	 * @param mixed     $value           The value for the section choice.
	 * @param string    $key             The key for the section choice.
	 * @param string    $section_type    The section type.
	 */
	return apply_filters( 'make_sanitize_section_choice', $value, $key, $section_type );
}
endif;

/**
 * Instantiate or return the one TTFMAKE_Section_Definitions instance.
 *
 * @since  1.0.0.
 *
 * @return TTFMAKE_Section_Definitions
 */
function ttfmake_get_section_definitions() {
	return TTFMAKE_Section_Definitions::instance();
}

// Kick off the section definitions immediately
if ( is_admin() ) {
	add_action( 'after_setup_theme', 'ttfmake_get_section_definitions', 11 );
}

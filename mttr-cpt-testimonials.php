<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: Matter Kit - Testimonials
 * Description: This plugin adds a custom post type for testimonials.
 * Version: 1.0.0
 * Author: Matter Solutions
 * Author URI: https://www.mttr.io
 */


add_action( 'plugins_loaded', array( 'mttr_custom_post_type_mttr_testimonials', 'init' ) );


class mttr_custom_post_type_mttr_testimonials {


	protected static $instance = NULL;
	public static $slug = 'mttr_testimonials';
	public static $singular_name = 'Testimonial';


    public static function getInstance() {

        NULL === self::$instance and self::$instance = new self;
        return self::$instance;

    }


    public static function init() {

    	// Register the post type
    	add_action( 'init', array( self::getInstance(), 'register_post_type' ), 3 );

    	// Register the Taxonomy
    	add_action( 'init', array( self::getInstance(), 'register_taxonomy' ), 3 );

    	// Remove the flexible content, it's not needed
    	add_filter( 'mttr_flex_layouts_locations_post_types_array', array( self::getInstance(), 'unhook_flexible_content' ) );

    	// Filter the ordering for auto grid items
    	add_filter( 'mttr_latest_posts_' . self::$slug, array( self::getInstance(), 'filter_grid_ordering' ) );

    	// Add Gforms options if wanted
    	add_filter( 'gform_pre_render', array( self::getInstance(), 'gravity_forms_testimonials' ) );
		add_filter( 'gform_pre_validation', array( self::getInstance(), 'gravity_forms_testimonials' ) );
		add_filter( 'gform_pre_submission_filter', array( self::getInstance(), 'gravity_forms_testimonials' ) );
		add_filter( 'gform_admin_pre_render', array( self::getInstance(), 'gravity_forms_testimonials' ) );

    }


    function register_taxonomy() {

    	register_taxonomy(
			self::$slug . '_category',
			self::$slug,
			array(
				'label' => __( self::$singular_name . ' Categories' ),
				'rewrite' => array(
					'slug' => 'testimonials',
					'with_front' => false
				),
				'hierarchical' => true,
			)
		);

    }


    function register_post_type() {

    	if ( function_exists( 'mttr_generate_cpt_labels' ) ) {

    		$labels = mttr_generate_cpt_labels( 'Testimonial' );

    	} else {

    		$labels = array(

    			'name' => __( 'Testimonials' ),
				'singular_name' => __( 'Testimonial' ),

			);

    	}

		register_post_type( self::$slug,

			// CPT Options
			array(

				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'has_archive' => false,
				'description'=> '',
				'rewrite' => array(
					'slug' => 'testimonials',
					'with_front' => false,
				),
				'exclude_from_search' => true,
				'capability_type'     => 'page',
				'menu_icon'           => 'dashicons-testimonial',
				'supports' => array(

		            'title',
		            'editor',
		            'page-attributes',
		            'thumbnail',

		        ),

			)

		);

	}



	function filter_grid_ordering( $args ) {

		if ( $args['post_type'] == self::$slug ) {

			$args['orderby'] = 'menu_order title';
			$args['order'] = 'ASC';

		}

		return $args;

	}




	function unhook_flexible_content( $post_types ) {

		unset( $post_types[self::$slug] );

		return $post_types;

	}



	function image_size() {

		add_image_size( 'mttr_avatar', 90, 90, true );

	}


	// Add the class 'mttr-cpt-testimonials' to a checkbox, radio or dropdown field and it will automatically populate with the testimonials

	function gravity_forms_testimonials( $form ) {

	    foreach ( $form['fields'] as &$field ) {

	        if ( $field->type != 'select' && $field->type != 'radio' && $field->type != 'checkbox' && strpos( $field->cssClass, 'mttr-cpt-testimonials' ) === false ) {
	            continue;
	        }

	        // you can add additional parameters here to alter the posts that are retrieved
	        // more info: [http://codex.WordPress.org/Template_Tags/get_posts](http://codex.WordPress.org/Template_Tags/get_posts)
	        $posts = get_posts( 'numberposts=-1&post_status=publish&post_type=' . self::$slug . '&order=ASC&orderby=title' );

	        if ( is_array( $posts ) && !empty( $posts ) ) {

		        $choices = array();

		        foreach ( $posts as $post ) {

		            $choices[] = array( 'text' => $post->post_title, 'value' => $post->post_title );

		        }

		        // update 'Select a Post' to whatever you'd like the instructive option to be
		        $field->choices = $choices;

		    }

	    }

	    return $form;
	}


}
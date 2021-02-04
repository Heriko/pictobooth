<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Jet_Engine_Dynamic_Function_Tag extends Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'jet-dynamic-function';
	}

	public function get_title() {
		return __( 'Dynamic Function', 'jet-engine' );
	}

	public function get_group() {
		return Jet_Engine_Dynamic_Tags_Module::JET_GROUP;
	}

	public function get_categories() {
		return array(
			Jet_Engine_Dynamic_Tags_Module::TEXT_CATEGORY,
			Jet_Engine_Dynamic_Tags_Module::NUMBER_CATEGORY,
			Jet_Engine_Dynamic_Tags_Module::URL_CATEGORY,
			Jet_Engine_Dynamic_Tags_Module::POST_META_CATEGORY,
		);
	}

	public function is_settings_required() {
		return true;
	}

	protected function _register_controls() {

		$this->add_control(
			'function_name',
			array(
				'label'   => __( 'Function', 'jet-engine' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'options' => jet_engine()->dynamic_functions->functions_list(),
			)
		);

		$this->add_control(
			'data_source',
			array(
				'label'   => __( 'Data Source', 'jet-engine' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'options' => array(
					'post_meta' => __( 'Post Meta', 'jet-engine' ),
					'term_meta' => __( 'Term Meta', 'jet-engine' ),
					'user_meta' => __( 'User Meta', 'jet-engine' ),
				),
			)
		);

		$this->add_control(
			'field_name',
			array(
				'label'       => __( 'Field Name', 'jet-engine' ),
				'type'        => Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->add_control(
			'data_context',
			array(
				'label'   => __( 'Data Context', 'jet-engine' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'default' => 'all_posts',
				'options' => array(
					'all_posts' => __( 'All posts', 'jet-engine' ),
					'current_term' => __( 'Posts from current term', 'jet-engine' ),
					'current_user' => __( 'Posts by current user', 'jet-engine' ),
					'queried_user' => __( 'Posts by queried user', 'jet-engine' ),
				),
				'condition' => array(
					'data_source' => 'post_meta',
				),
			)
		);

		$this->add_control(
			'data_context_tax',
			array(
				'label'  => __( 'Taxonomy', 'jet-engine' ),
				'type'   => Elementor\Controls_Manager::SELECT,
				'groups' => $this->get_taxonomies_for_options(),
				'condition' => array(
					'data_source' => 'post_meta',
					'data_context' => 'current_term',
				),
			)
		);

		$this->add_control(
			'data_context_tax_term',
			array(
				'label'  => __( 'Set term ID/slug', 'jet-engine' ),
				'type'   => Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Leave empty to get term dynamically. Use prefix <b>slug::</b> to set term by slug instead of ID, for example - slug::term-slug', 'jet-engine' ),
				'condition' => array(
					'data_source' => 'post_meta',
					'data_context' => 'current_term',
				),
			)
		);

		$this->add_control(
			'data_context_user_id',
			array(
				'label'  => __( 'Set user ID/login/email', 'jet-engine' ),
				'type'   => Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Leave empty to get user ID dynamically. Use prefixes <b>login::</b> or <b>email::</b> to set user by login or email instead of ID, for example - email::admin@demolink.org', 'jet-engine' ),
				'condition' => array(
					'data_source' => 'post_meta',
					'data_context' => 'queried_user',
				),
			)
		);

		$this->add_control(
			'data_post_status',
			array(
				'label'   => __( 'Query by posts with status', 'jet-engine' ),
				'description' => __( 'Leave empy to search anywhere', 'jet-engine' ),
				'type'    => Elementor\Controls_Manager::SELECT2,
				'default' => '',
				'label_block' => true,
				'multiple'    => true,
				'options' => array(
					'publish'    => __( 'Publish', 'jet-engine' ),
					'pending'    => __( 'Pending', 'jet-engine' ),
					'draft'      => __( 'Draft', 'jet-engine' ),
					'auto-draft' => __( 'Auto draft', 'jet-engine' ),
					'future'     => __( 'Future', 'jet-engine' ),
					'private'    => __( 'Private', 'jet-engine' ),
					'trash'      => __( 'Trash', 'jet-engine' ),
					'any'        => __( 'Any', 'jet-engine' ),
				),
				'condition'   => array(
					'data_source' => 'post_meta',
				),
			)
		);

		$this->add_control(
			'data_post_types',
			array(
				'label'       => esc_html__( 'Query by posts with types', 'jet-engine' ),
				'description' => __( 'Leave empy to search anywhere', 'jet-engine' ),
				'type'        => Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => jet_engine()->listings->get_post_types_for_options(),
				'condition'   => array(
					'data_source' => 'post_meta',
				),
			)
		);

		jet_engine()->dynamic_functions->register_custom_settings( $this );

	}

	/**
	 * Returns all taxonomies list for options
	 *
	 * @return [type] [description]
	 */
	public function get_taxonomies_for_options() {

		$result     = array();
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {

			if ( empty( $taxonomy->object_type ) || ! is_array( $taxonomy->object_type ) ) {
				continue;
			}

			foreach ( $taxonomy->object_type as $object ) {
				if ( empty( $result[ $object ] ) ) {
					$post_type = get_post_type_object( $object );

					if ( ! $post_type ) {
						continue;
					}

					$result[ $object ] = array(
						'label'   => $post_type->labels->name,
						'options' => array(),
					);
				}

				$result[ $object ]['options'][ $taxonomy->name ] = $taxonomy->labels->name;

			};
		}

		return $result;

	}

	public function render() {

		$function_name = $this->get_settings( 'function_name' );
		$source   = $this->get_settings( 'data_source' );
		$field_name    = $this->get_settings( 'field_name' );

		if ( ! $source ) {
			$source = 'post_meta';
		}

		$data_source = array( 'source' => $source );

		if ( empty( $function_name ) ) {
			return;
		}

		if ( 'post_meta' === $source ) {
			$data_context = $this->get_settings( 'data_context' );
			$data_context_tax = $this->get_settings( 'data_context_tax' );
			
			$data_source['context'] = $data_context ? $data_context : 'all_posts';
			$data_source['context_tax'] = $data_context_tax;
			$data_source['context_tax_term'] = $this->get_settings( 'data_context_tax_term' );
			$data_source['context_user_id'] = $this->get_settings( 'data_context_user_id' );
			$data_source['post_status'] = $this->get_settings( 'data_post_status' );
			$data_source['post_types'] = $this->get_settings( 'data_post_types' );
		}

		$custom_settings = jet_engine()->dynamic_functions->get_custom_settings( $function_name, $this );

		echo jet_engine()->dynamic_functions->call_function( $function_name, $data_source, $field_name, $custom_settings );
	}

}

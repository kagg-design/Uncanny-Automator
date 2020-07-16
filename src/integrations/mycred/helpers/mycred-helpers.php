<?php

namespace Uncanny_Automator;

/**
 * Class Mycred_Helpers
 * @package Uncanny_Automator
 */
class Mycred_Helpers {

	/**
	 * Mycred_Helpers constructor.
	 */
	public function __construct() {
	}

	/**
	 * @var Mycred_Helpers
	 */
	public $options;

	/**
	 * @var \Uncanny_Automator_Pro\Mycred_Pro_Helpers
	 */
	public $pro;

	/**
	 * @param Mycred_Helpers $options
	 */
	public function setOptions( Mycred_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param \Uncanny_Automator_Pro\Mycred_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Mycred_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function list_mycred_points_types( $label = null, $option_code = 'MYCREDPOINTSTYPES', $args = [] ) {

		if ( ! $label ) {
			$label = __( 'Point type', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$include_all  = key_exists( 'include_all', $args ) ? $args['include_all'] : false;

		$options = [];

		if ( $include_all ) {
			$options['ua-all-mycred-points'] = __( 'All point types', 'uncanny-automator' );
		}

		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			$posts = mycred_get_types();

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					$options[ $key ] = $post;
				}
			}
		}
		$type = 'select';

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
		];

		return apply_filters( 'uap_option_list_mycred_points_types', $option );
	}

	/**
	 * @param null $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function list_mycred_rank_types( $label = null, $option_code = 'MYCREDRANKTYPES', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Ranks', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$include_all  = key_exists( 'include_all', $args ) ? $args['include_all'] : false;
		$options      = [];

		global $uncanny_automator;

		if ( $include_all ) {
			$options['ua-all-mycred-ranks'] = __( 'All ranks', 'uncanny-automator' );
		}

		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			$posts = get_posts( [
				'post_type'      => 'mycred_rank',
				'posts_per_page' => 9999,
				'post_status'    => 'publish'
			] );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					if ( $post->post_type === 'mycred_rank' ) {
						$options[ $post->ID ] = $post->post_title;
					}
				}
			}
		}
		$type = 'select';

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
		];

		return apply_filters( 'uap_option_list_mycred_rank_types', $option );
	}

	public function list_mycred_badges( $label = null, $option_code = 'MYCREDBADGETYPES', $args = [] ) {
		if ( ! $label ) {
			$label = __( 'Badges', 'uncanny-automator' );
		}

		$token        = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_ajax      = key_exists( 'is_ajax', $args ) ? $args['is_ajax'] : false;
		$target_field = key_exists( 'target_field', $args ) ? $args['target_field'] : '';
		$end_point    = key_exists( 'endpoint', $args ) ? $args['endpoint'] : '';
		$include_all  = key_exists( 'include_all', $args ) ? $args['include_all'] : false;
		$options      = [];

		if ( $include_all ) {
			$options['ua-all-mycred-badges'] = __( 'All badges', 'uncanny-automator' );
		}

		global $uncanny_automator;
		if ( $uncanny_automator->helpers->recipe->load_helpers ) {
			$posts = get_posts( [
				'post_type'      => 'mycred_badge',
				'posts_per_page' => 9999,
				'post_status'    => 'publish'
			] );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					if ( $post->post_type === 'mycred_badge' ) {
						$options[ $post->ID ] = $post->post_title;
					}
				}
			}
		}
		$type = 'select';

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => $type,
			'required'        => true,
			'supports_tokens' => $token,
			'is_ajax'         => $is_ajax,
			'fill_values_in'  => $target_field,
			'endpoint'        => $end_point,
			'options'         => $options,
		];

		return apply_filters( 'uap_option_list_mycred_badges', $option );
	}

}
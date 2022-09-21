<?php

namespace Uncanny_Automator;

use Uncanny_Automator_Pro\Wpsp_Pro_Helpers;

/**
 * Class Wpsp_Helpers
 *
 * @package Uncanny_Automator
 */
class Wpsp_Helpers {

	/**
	 * @var Wpsp_Helpers
	 */
	public $options;

	/**
	 * @var Wpsp_Pro_Helpers
	 */
	public $pro;

	/**
	 * @var bool
	 */
	public $load_options;

	/**
	 * Wpsp_Helpers constructor.
	 */
	public function __construct() {

		$this->load_options = true;
	}

	/**
	 * @param Wpsp_Helpers $options
	 */
	public function setOptions( Wpsp_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param Wpsp_Pro_Helpers $pro
	 */
	public function setPro( Wpsp_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * @param string $label
	 * @param string $option_code
	 * @param array $args
	 *
	 * @return mixed
	 */

	public function list_wp_simpay_forms( $label = null, $option_code = 'WPSIMPAYFORMS', $args = array() ) {

		if ( ! $label ) {
			$label = esc_attr__( 'Form', 'uncanny-automator' );
		}

		$token  = key_exists( 'token', $args ) ? $args['token'] : false;
		$is_any = key_exists( 'is_any', $args ) ? $args['is_any'] : false;

		if ( function_exists( 'simpay_get_form_list_options' ) ) {
			$options = simpay_get_form_list_options();
		} else {
			$forms = get_posts(
				array(
					'post_type'      => 'simple-pay',
					'posts_per_page' => 9999,
					'fields'         => 'ids',
				)
			);

			foreach ( $forms as $form_id ) {
				$options[ $form_id ] = get_the_title( $form_id );
			}
		}

		if ( true === $is_any ) {
			$options = array( '-1' => __( 'Any form', 'uncanny-automator' ) ) + $options;
		}

		$option = array(
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			'supports_tokens' => $token,
			'options'         => $options,
			'relevant_tokens' => array(
				$option_code         => esc_attr__( 'Form title', 'uncanny-automator' ),
				$option_code . '_ID' => esc_attr__( 'Form ID', 'uncanny-automator' ),
			),
		);

		return apply_filters( 'uap_option_list_wp_simpay_forms', $option );
	}
}

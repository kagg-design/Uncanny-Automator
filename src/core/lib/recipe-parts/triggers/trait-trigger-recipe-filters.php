<?php
namespace Uncanny_Automator\Recipe;

/**
 * Trait Trigger_Recipe_Filters
 *
 * @todo Add documentation for each method.
 * @todo Add greater than and less than method.
 *
 * @package Uncanny_Automator\Recipe\Trigger_Recipe_Filters
 */
trait Trigger_Recipe_Filters {

	protected $recipes = array();

	protected $conditions_format = array();

	protected $match_conditions = array();

	protected $where_conditions = array();

	protected $actual_where_values = array();

	protected $compare = array();

	protected $default_callable_format = 'sanitize_text_field';

	protected $default_notation = '=';

	protected $log = array();

	public function get_recipes() {

		return $this->recipes;

	}

	public function get_conditions_format( $index = null ) {

		if ( empty( $this->conditions_format ) ) {

			throw new \InvalidArgumentException( 'Empty format. Pass array to $this->format( array $args ).' );

		}

		if ( is_numeric( $index ) ) {

			return $this->conditions_format[ $index ];

		}

		return $this->conditions_format;

	}

	public function get_match_conditions() {

		return $this->match_conditions;

	}

	public function get_where_conditions() {

		return $this->where_conditions;

	}

	public function get_actual_where_values() {

		return $this->actual_where_values;

	}

	public function push_log( $mixed = '' ) {
		array_push( $this->log, $mixed );
	}

	public function get_compare( $index = null ) {

		// If compare is not set, set equality sign to first index.
		// This would automatically make all compare have equal sign.
		if ( empty( $this->compare ) ) {

			$this->compare( array( '=' ) );

		}

		if ( is_numeric( $index ) && ! empty( $this->compare[ $index ] ) ) {

			return $this->compare[ $index ];

		}

		return $this->compare;

	}

	public function find_all( $recipes ) {

		$this->recipes = $recipes;

		return $this;

	}

	public function where( $conditions = array() ) {

		$this->where_conditions = $conditions;

		return $this;

	}

	public function equals( $conditions = array() ) {

		$this->match_conditions = $conditions;

		return $this;

	}

	public function match( $conditions ) {

		$this->equals( $conditions );

		return $this;

	}

	public function compare( $compare = array() ) {

		$n_where = count( $this->get_where_conditions() );

		for ( $i = 0; $i < $n_where; $i++ ) {

			// Set default format to default callable format.
			if ( empty( $compare[ $i ] ) ) {

				$compare[ $i ] = $this->default_notation;

			}
		}

		$this->compare = $compare;

		return $this;

	}

	public function format( $conditions_format = array() ) {

		$n_where = count( $this->get_where_conditions() );

		for ( $i = 0; $i < $n_where; $i++ ) {

			// Set default format to default callable format.
			if ( ! isset( $conditions_format[ $i ] ) ) {

				$conditions_format[ $i ] = $this->default_callable_format;

			}
		}

		$this->conditions_format = $conditions_format;

		return $this;

	}

	public function get() {

		$matched_recipe_ids = array();

		foreach ( $this->get_recipes() as $recipe_id => $recipe ) {

			foreach ( $recipe['triggers'] as $trigger ) {

				$trigger_id = absint( $trigger['ID'] );

				if ( ! $this->is_trigger_values_set( $trigger_id, $recipe_id ) ) {

					continue;

				}

				$matched_value = 0;

				$where_values = (array) $this->get_where_values( $trigger_id, $recipe_id );

				foreach ( $where_values as $i => $where_value ) {

					if ( $this->conditions_matched( $this->get_compare( $i ), $where_value, $this->match_conditions[ $i ] ) ) {

						$matched_value ++;

					}
				}

				if ( count( $where_values ) === $matched_value ) {

					$matched_recipe_ids[ $recipe_id ] = $trigger_id;

				}
			}
		}

		return $matched_recipe_ids;

	}

	public function conditions_matched( $notation, $where, $condition ) {

		// Process non-integer inputs.
		if ( ! empty( $this->get_compare() ) && ( ! is_numeric( $where ) || ! is_numeric( $condition ) ) ) {

			$condition_matched = ( $where === $condition );

			// Special string_contains notation.
			// @since 4.4
			if ( 'string_contains' === $notation ) {

				$add_slashes       = apply_filters( 'automator_escape_matching_characters', '/._-:\\' );
				$condition_matched = preg_match( '/(' . addcslashes( $where, $add_slashes ) . ')/i', $condition );

				$this->push_log( 'Asserting ' . gettype( $where ) . ":{$where} '{$notation}' " . gettype( $condition ) . ":$condition | Result: " . ( $condition_matched ? 'Matched' : 'Failed' ) );

				return $condition_matched;

			}

			$this->push_log( 'Asserting (non-numeric)' . gettype( $where ) . ":{$where} '{$notation}' " . gettype( $condition ) . ":$condition | Result: " . ( $condition_matched ? 'Matched' : 'Failed' ) );

			return $condition_matched;

		}

		// Otherwise, process with equality sign.
		$condition_matched = $this->match_condition( $notation, $where, $condition );

		$this->push_log( 'Asserting ' . gettype( $where ) . ":{$where} '{$notation}' " . gettype( $condition ) . ":$condition | Result: " . ( $condition_matched ? 'Matched (with non-numeric parameter converted to int)' : 'Failed' ) );

		return $condition_matched;

	}

	protected function match_condition( $notation, $where, $condition ) {

		return Automator()->utilities->match_condition_vs_number( $notation, $where, $condition );

	}

	public function is_trigger_values_set( $trigger_id, $recipe_id ) {

		$is_valid = true;

		$where_conditions = (array) $this->get_where_conditions();

		if ( empty( $where_conditions ) ) {

			return false;

		}

		foreach ( $where_conditions as $trigger_option_code ) {

			$trigger_meta = Automator()->get->meta_from_recipes( $this->get_recipes(), $trigger_option_code );

			if ( empty( $trigger_meta[ $recipe_id ][ $trigger_id ] ) ) {

				$is_valid = false;

			}
		}

		return $is_valid;

	}

	protected function get_where_values( $trigger_id, $recipe_id ) {

		// Reset the values. Hooks that executed multiple times can fill the where values.
		$this->actual_where_values = array();

		foreach ( $this->get_where_conditions() as $i => $trigger_option_code ) {

			$trigger_meta = Automator()->get->meta_from_recipes( $this->get_recipes(), $trigger_option_code );

			$where_value = $trigger_meta[ $recipe_id ][ $trigger_id ];

			$this->push_log( "Recipe ID: {$recipe_id}, Trigger ID: {$trigger_id}, Option Code: {$trigger_option_code}" );

			// Handle 'Any' automatically.
			if ( intval( -1 ) === intval( $where_value ) ) {
				// Make the where value equals to match condition automatically so it becomes valid.
				$where_value = $this->match_conditions[ $i ];

				$this->push_log = sprintf( 'Comparing %s:%s as "Any"', gettype( $this->match_conditions[ $i ] ), $this->match_conditions[ $i ] );

			}

			$this->actual_where_values[] = $this->value_format( $where_value, $this->get_conditions_format( $i ) );

		}

		return $this->actual_where_values;

	}

	protected function value_format( $value, callable $format = null ) {

		return $format( $value );

	}

	protected function explain() {

		$where_values = array();

		return array(
			'where_conditions_values' => $this->get_actual_where_values(),
			'match_conditions_values' => $this->get_match_conditions(),
			'compare'                 => $this->get_compare(),
			'conditions_format'       => $this->get_conditions_format(),
			'process'                 => $this->log,
		);

	}

}
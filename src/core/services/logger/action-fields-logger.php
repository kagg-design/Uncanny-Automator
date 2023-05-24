<?php
namespace Uncanny_Automator\Logger;

/**
 * Internal class use for logging fields.
 *
 * @since 4.12
 */
class Action_Fields_Logger {

	const META_KEY = 'action_fields';

	/**
	 * Logs the field values in action meta log table.
	 *
	 * @param int[] $args Accepts ['user_id','action_log_id','action_id']
	 * @param mixed[] $fields Accepts the array result from resolver.
	 *
	 * @return bool|int
	 */
	public function log( $args = array(), $fields = array() ) {

		$args = wp_parse_args(
			$args,
			array(
				'user_id'       => 0,
				'action_log_id' => 0,
				'action_id'     => 0,
			)
		);

		return Automator()->db->action->add_meta(
			$args['user_id'],
			$args['action_log_id'],
			$args['action_id'],
			self::META_KEY,
			wp_json_encode( array_merge( (array) $fields['options'], (array) $fields['options_group'] ) )
		);

	}

}

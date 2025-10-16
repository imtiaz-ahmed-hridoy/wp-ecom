<?php

namespace ImageOptimization\Modules\Optimization\Components;

use ImageOptimization\Classes\Async_Operation\{
	Async_Operation,
	Async_Operation_Hook,
	Async_Operation_Queue,
	Exceptions\Async_Operation_Exception
};

use ImageOptimization\Classes\Logger;

use Exception;
use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Actions_Cleanup {
	const FIVE_MINUTES_IN_SECONDS = 300;

	/**
	 * @async
	 * @return void
	 */
	public function cleanup_stuck_operations() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'actionscheduler_actions';
		$now = time();
		$threshold = $now - self::FIVE_MINUTES_IN_SECONDS;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT action_id
				FROM {$table_name}
				WHERE last_attempt_gmt IS NOT NULL
				  AND UNIX_TIMESTAMP(last_attempt_gmt) < %d
				",
				$threshold
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $results ) ) {
			Logger::log( Logger::LEVEL_INFO, 'No stuck optimization operations found for cleanup.' );
			return;
		}

		foreach ( $results as $action_id ) {
			$action = Async_Operation::get_by_id( (int) $action_id );

			if (
				! $action ||
				Async_Operation_Queue::OPTIMIZE !== $action->get_queue() ||
				Async_Operation::OPERATION_STATUS_RUNNING !== $action->get_status()
			) {
				continue;
			}

			try {
				do_action(
					'action_scheduler_failed_action',
					$action_id,
					self::FIVE_MINUTES_IN_SECONDS
				);

				Logger::log(
					Logger::LEVEL_INFO,
					"Triggered retry for stuck action ID {$action_id}."
				);
			} catch ( Throwable $t ) {
				Logger::log(
					Logger::LEVEL_ERROR,
					"Failed to handle stuck operation for action ID {$action_id}: " . $t->getMessage()
				);
			}
		}
	}

	public function schedule_cleanup() {
		try {
			Async_Operation::create_recurring(
				time(),
				self::FIVE_MINUTES_IN_SECONDS,
				Async_Operation_Hook::STUCK_OPERATION_CLEANUP,
				[],
				Async_Operation_Queue::CLEANUP,
				10,
				true
			);
		} catch ( Async_Operation_Exception $aoe ) {
			Logger::log(
				Logger::LEVEL_ERROR,
				'Failed to schedule recurring stuck operation cleanup: ' . $aoe->getMessage()
			);
		}
	}

	public function __construct() {
		add_action( 'action_scheduler_init', [ $this, 'schedule_cleanup' ] );
		add_action( Async_Operation_Hook::STUCK_OPERATION_CLEANUP, [ $this, 'cleanup_stuck_operations' ] );
	}
}

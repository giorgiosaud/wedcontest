<?php 
namespace Zonapro\WedContest\Logger;

use Zonapro\WedContest\Logger\Handler\File;


class Log implements LogInterface{


	/**
	 * Stores registered log handlers.
	 *
	 * @var array
	 */
	protected $handlers;

	/**
	 * Minimum log level this handler will process.
	 *
	 * @var int Integer representation of minimum log level to handle.
	 */
	protected $threshold;

	/**
	 * Constructor for the logger.
	 *
	 * @param array $handlers Optional. Array of log handlers. If $handlers is not provided,
	 *     the filter 'woocommerce_register_log_handlers' will be used to define the handlers.
	 *     If $handlers is provided, the filter will not be applied and the handlers will be
	 *     used directly.
	 * @param string $threshold Optional. Define an explicit threshold. May be configured
	 *     via  WC_LOG_THRESHOLD. By default, all logs will be processed.
	 */
	public function __construct( $handlers = null, $threshold = null ) {
		if ( null === $handlers ) {
			$handlers = apply_filters( 'wedcontest_register_log_handlers', array() );
		}

		$register_handlers = array();

		if ( ! empty( $handlers ) && is_array( $handlers ) ) {
			foreach ( $handlers as $handler ) {
				$implements = class_implements( $handler );
				if ( is_object( $handler ) && is_array( $implements ) && in_array( LogInterface::class, $implements ) ) {
					$register_handlers[] = $handler;
				} else {
					wc_doing_it_wrong(
						__METHOD__,
						sprintf(
							/* translators: 1: class name 2: WC_Log_Handler_Interface */
							__( 'The provided handler %1$s does not implement %2$s.', 'wedcontest' ),
							'<code>' . esc_html( is_object( $handler ) ? get_class( $handler ) : $handler ) . '</code>',
							'<code>Interface</code>'
						),
						'3.0'
					);
				}
			}
		}

		if ( null !== $threshold ) {
			$threshold = Levels::get_level_severity( $threshold );
		} elseif ( defined( 'WED_LOG_THRESHOLD' ) && Levels::is_valid_level( WC_THRESHOLD ) ) {
			$threshold = Levels::get_level_severity( WC_LOG_THRESHOLD );
		} else {
			$threshold = null;
		}

		$this->handlers  = $register_handlers;
		$this->threshold = $threshold;
	}

	/**
	 * Determine whether to handle or ignore log.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug
	 * @return bool True if the log should be handled.
	 */
	protected function should_handle( $level ) {
		if ( null === $this->threshold ) {
			return true;
		}
		return $this->threshold <= Levels::get_level_severity( $level );
	}

	/**
	 * Add a log entry.
	 *
	 * This is not the preferred method for adding log messages. Please use log() or any one of
	 * the level methods (debug(), info(), etc.). This method may be deprecated in the future.
	 *
	 * @param string $handle
	 * @param string $message
	 * @param string $level
	 *
	 * @return bool
	 */
	public function add( $handle, $message, $level = Levels::NOTICE ) {
		$message = apply_filters( 'wedcontest_logger_add_message', $message, $handle );
		$this->log( $level, $message, array( 'source' => $handle, '_legacy' => true ) );
		wc_do_deprecated_action( 'wedcontest_log_add', array( $handle, $message ), '3.0', 'This action has been deprecated with no alternative.' );
		return true;
	}

	/**
	 * Add a log entry.
	 *
	 * @param string $level One of the following:
	 *     'emergency': System is unusable.
	 *     'alert': Action must be taken immediately.
	 *     'critical': Critical conditions.
	 *     'error': Error conditions.
	 *     'warning': Warning conditions.
	 *     'notice': Normal but significant condition.
	 *     'info': Informational messages.
	 *     'debug': Debug-level messages.
	 * @param string $message Log message.
	 * @param array $context Optional. Additional information for log handlers.
	 */
	public function log( $level, $message, $context = array() ) {
		if ( ! Levels::is_valid_level( $level ) ) {
			/* translators: 1: WC_Logger::log 2: level */
			wed_doing_it_wrong( __METHOD__, sprintf( __( '%1$s was called with an invalid level "%2$s".', 'wedcontest' ), '<code>Log ::log</code>', $level ), '3.0' );
		}

		if ( $this->should_handle( $level ) ) {
			$timestamp = current_time( 'timestamp' );
			$message = apply_filters( 'wedcontest_logger_log_message', $message, $level, $context );

			foreach ( $this->handlers as $handler ) {
				$handler->handle( $timestamp, $level, $message, $context );
			}
		}
	}

	/**
	 * Adds an emergency level message.
	 *
	 * System is unusable.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( Levels::EMERGENCY, $message, $context );
	}

	/**
	 * Adds an alert level message.
	 *
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function alert( $message, $context = array() ) {
		$this->log( Levels::ALERT, $message, $context );
	}

	/**
	 * Adds a critical level message.
	 *
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function critical( $message, $context = array() ) {
		$this->log( Levels::CRITICAL, $message, $context );
	}

	/**
	 * Adds an error level message.
	 *
	 * Runtime errors that do not require immediate action but should typically be logged
	 * and monitored.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function error( $message, $context = array() ) {
		$this->log( Levels::ERROR, $message, $context );
	}

	/**
	 * Adds a warning level message.
	 *
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
	 * necessarily wrong.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function warning( $message, $context = array() ) {
		$this->log( Levels::WARNING, $message, $context );
	}

	/**
	 * Adds a notice level message.
	 *
	 * Normal but significant events.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function notice( $message, $context = array() ) {
		$this->log( Levels::NOTICE, $message, $context );
	}

	/**
	 * Adds a info level message.
	 *
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function info( $message, $context = array() ) {
		$this->log( Levels::INFO, $message, $context );
	}

	/**
	 * Adds a debug level message.
	 *
	 * Detailed debug information.
	 *
	 * @see WC_Logger::log
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function debug( $message, $context = array() ) {
		$this->log( Levels::DEBUG, $message, $context );
	}

	/**
	 * Clear entries from chosen file.
	 *
	 * @deprecated 3.0.0
	 *
	 * @param string $handle
	 *
	 * @return bool
	 */
	public function clear( $handle ) {
		wc_deprecated_function( '\Zonapro\WedContest\Logger\Log::clear', '3.0', '\Zonapro\WedContest\Logger\Handler\File::clear' );
		$handler = new File();
		return $handler->clear( $handle );
	}

}
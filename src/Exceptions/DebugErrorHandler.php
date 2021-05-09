<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Exceptions;

	use Throwable;
	use Whoops\RunInterface;
	use WPEmerge\Contracts\ErrorHandlerInterface;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Contracts\ResponseInterface;
	use WPEmerge\Events\ExceptionHandled;
	use WPEmerge\Http\Response;

	class DebugErrorHandler implements ErrorHandlerInterface {


		/** @var \Whoops\RunInterface */
		private $whoops;

		private $registered = false;


		public function __construct( RunInterface $whoops) {

			$this->whoops = $whoops;

		}

		public function register() {

			if ( $this->registered ) {

				return;

			}

			set_exception_handler( [ $this, 'handleException' ] );
			set_error_handler( [ $this, 'handleError' ] );


			$this->registered = true;

		}

		public function unregister() {

			if ( ! $this->registered ) {
				return;
			}

			restore_exception_handler();
			restore_error_handler();

			$this->registered = false;

		}

		public function handleException( $exception)  {


			$method = RunInterface::EXCEPTION_HANDLER;

			$this->whoops->{$method}( $exception );

			ExceptionHandled::dispatch();


		}

		public function handleError( $errno, $errstr, $errfile, $errline ) {

			if ( error_reporting() ) {

				$this->handleException(
					new \ErrorException( $errstr, 0, $errno, $errfile, $errline ),
				);

			}


		}

		public function transformToResponse( RequestInterface $request, Throwable $exception ) : ResponseInterface {

			 $this->handleException( $exception);

		}


	}
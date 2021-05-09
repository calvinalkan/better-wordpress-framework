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

		/** @var bool */
		private $is_ajax;

		private $registered = false;



		public function __construct( RunInterface $whoops,  $is_ajax = false) {

			$this->whoops = $whoops;
			$this->is_ajax = $is_ajax;

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

		public function handleException( $exception, $in_routing_flow = false )  {


			$method = RunInterface::EXCEPTION_HANDLER;

			$output = $this->whoops->{$method}( $exception );

			$response = new Response( $output, 500 );
			$response->setType( ( $this->is_ajax ) ? 'application/json' : 'text/html' );

			if (  $in_routing_flow ) {

				return $response;

			}

			$response->sendHeaders();
			$response->sendBody();

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


			return $this->handleException( $exception, true );


		}


	}
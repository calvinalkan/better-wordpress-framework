<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Exceptions;

	use Throwable;
	use Whoops\RunInterface;
	use WPEmerge\Contracts\ErrorHandlerInterface;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Contracts\ResponseInterface;
	use WPEmerge\Http\Response;

	class DebugErrorHandler implements ErrorHandlerInterface {


		/** @var \Whoops\RunInterface */
		private $whoops;

		/** @var bool */
		private $is_ajax;

		private $registered = false;

		public function __construct( RunInterface $whoops, $is_ajax = false ) {

			$this->whoops = $whoops;

			$this->is_ajax = $is_ajax;
		}

		public function register() {

			if ( $this->registered ) {

				return;

			}

			// $this->whoops->register();
			set_exception_handler( [ $this, 'handleException' ] );
			set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
				if (error_reporting()) {
					throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
				}
			});

			$this->registered = true;

		}

		public function unregister() {

			if ( ! $this->registered ) {
				return;
			}

			// $this->whoops->unregister();
			restore_exception_handler();
			restore_error_handler();

			$this->registered = false;

		}

		public function handleException( $exception, $in_routing_flow = false  ) {

			$method = RunInterface::EXCEPTION_HANDLER;

			$output = $this->whoops->{$method}( $exception );

			$content_type = ( $this->is_ajax ) ? 'application/json' : 'text/html';

			$response = new Response( $output, 500 );
			$response->setType( $content_type );

			if ( $in_routing_flow  ) {

				return $response;

			}

			$response->sendHeaders();
			$response->sendBody();

		}

		public function transformToResponse( RequestInterface $request, Throwable $exception ) : ResponseInterface {


			return $this->handleException($exception, true );

			// $method = RunInterface::EXCEPTION_HANDLER;
			//
			// $output = $this->whoops->{$method}( $exception );
			//
			// $content_type = ( $this->is_ajax ) ? 'application/json' : 'text/html';
			//
			// $response = new Response($output, 500);
			// $response->setType($content_type);
			//
			// if ( $this->outside_routing_flow ) {
			//
			// 	$response->sendHeaders();
			// 	$response->sendBody();
			//
			// }
			//
			// return $response;

		}

		public function writeToOutput( bool $false = false ) : void {

			$this->whoops->writeToOutput( $false );

		}

		public function allowQuit( bool $false = false ) : void {

			$this->whoops->allowQuit( $false );

		}

		public function isRegistered() : bool {

			return $this->registered;

		}

	}
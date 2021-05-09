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

		public function __construct( RunInterface $whoops, $is_ajax = false ) {

			$this->whoops = $whoops;

			$this->is_ajax = $is_ajax;
		}

		public function register() {

			$this->whoops->register();

		}

		public function unregister() {

			$this->whoops->unregister();

		}

		public function transformToResponse( RequestInterface $request, Throwable $exception ) : ResponseInterface {

			$method = RunInterface::EXCEPTION_HANDLER;

			$output = $this->whoops->{$method}( $exception );

			$content_type = ( $this->is_ajax ) ? 'application/json' : 'text/html';

			return (new Response($output, 500))->setType($content_type);


		}

		public function writeToOutput( bool $false = false ) : void {

			$this->whoops->writeToOutput($false);

		}

		public function allowQuit( bool $false = false ) : void {

			$this->whoops->allowQuit($false);

		}

	}
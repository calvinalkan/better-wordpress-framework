<?php


	declare( strict_types = 1 );


	namespace WPEmerge\ServiceProviders;

	use WPEmerge\Contracts\ErrorHandlerInterface;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Contracts\ServiceProvider;
	use WPEmerge\Factories\ErrorHandlerFactory;

	class ExceptionsServiceProvider extends ServiceProvider {


		public function register() : void {

			$this->container->singleton( ErrorHandlerInterface::class, function () {

				/** @var RequestInterface $request */
				$request = $this->container->make( RequestInterface::class );

				return ErrorHandlerFactory::make(
					$this->container,
					$this->config->get( 'debug', false ),
					$request->isAjax(),
					$this->config->get( 'exceptions.editor', 'phpstorm' )

				);
			} );

		}

		public function bootstrap() : void {

			$error_handler = $this->container->make(ErrorHandlerInterface::class);

			$error_handler->register();

			$this->container->instance( ErrorHandlerInterface::class, $error_handler );

		}

	}

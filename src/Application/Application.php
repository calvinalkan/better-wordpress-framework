<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Application;

	use Contracts\ContainerAdapter;
	use SniccoAdapter\BaseContainerAdapter;
	use WPEmerge\Contracts\ErrorHandlerInterface;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Exceptions\ConfigurationException;
	use WPEmerge\Factories\ErrorHandlerFactory;
	use WPEmerge\Http\Request;
	use WPEmerge\ServiceProviders\ApplicationServiceProvider;

	class Application {


		use ManagesAliases;
		use LoadsServiceProviders;
		use HasContainer;

		private $bootstrapped = false;

		/**
		 * @var \WPEmerge\Application\ApplicationConfig
		 */
		private $config;


		public function __construct( ContainerAdapter $container ) {

			$this->setContainerAdapter( $container );
			$this->container()[ Application::class ]      = $this;
			$this->container()[ ContainerAdapter::class ] = $this->container();
			$this->container()->instance( RequestInterface::class, Request::capture() );


		}


		/**
		 * Make and assign a new application instance.
		 *
		 * @param  string|ContainerAdapter  $container_adapter  ::class or default
		 *
		 * @return static
		 */
		public static function create( $container_adapter ) : Application {

			return new static(
				( $container_adapter !== 'default' ) ? $container_adapter : new BaseContainerAdapter()
			);
		}

		/**
		 * Bootstrap the application and loads all service providers.
		 *
		 * @param  array  $config  The configuration provided by a user during bootstrapping.
		 *
		 * @throws \WPEmerge\Exceptions\ConfigurationException
		 */
		public function boot( array $config = [] ) : void {


			if ( $this->bootstrapped ) {

				throw new ConfigurationException( static::class . ' already bootstrapped.' );

			}

			$this->bindConfigInstance( $config );

			$error_handler = $this->createErrorHandler(
				$this->config->get( 'exceptions.editor', 'phpstorm' )
			);


			$this->loadServiceProviders( $this->container() );

			$this->bootstrapped = true;

			// If we would always unregister here it would not be possible to handle
			// any errors that happen between this point and the the triggering of the
			// hooks that run the HttpKernel.
			if ( ! $this->isTakeOverMode() ) {

				$error_handler->unregister();

			}


		}

		private function bindConfigInstance( array $config ) {

			$config = new ApplicationConfig( $config );

			$this->container()->instance( ApplicationConfig::class, $config );
			$this->config = $config;

		}

		public function config( string $key, $default = null ) {

			return $this->config->get( $key, $default );

		}

		/**
		 * @throws \WPEmerge\Exceptions\ConfigurationException
		 */
		private function createErrorHandler( string $editor ) : ErrorHandlerInterface {

			$request = $this->container()->make(RequestInterface::class);

			$error_handler = ErrorHandlerFactory::make(
				$request,
				$this->config->get('debug', false ),
				$request->isAjax(),
				$editor
			);

			$error_handler->register();

			$this->container()->instance( ErrorHandlerInterface::class, $error_handler );

			return $error_handler;

		}

		private function isTakeOverMode() {

			return $this->config->get( ApplicationServiceProvider::STRICT_MODE, false );

		}

	}

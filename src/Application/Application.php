<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Application;

	use Contracts\ContainerAdapter;
	use Illuminate\Config\Repository;
	use SniccoAdapter\BaseContainerAdapter;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Exceptions\ConfigurationException;
	use WPEmerge\Factories\ExceptionHandlerFactory;
	use WPEmerge\Http\Request;
	use WPEmerge\Support\VariableBag;

	class Application {


		use ManagesAliases;
		use LoadsServiceProviders;
		use HasContainer;


		private $bootstrapped = false;

		/**
		 * @var \WPEmerge\Application\ApplicationConfig
		 */
		private $config;


		public function __construct( ContainerAdapter $container) {

			$this->setContainerAdapter( $container );
			$this->container()[ Application::class ]   = $this;
			$this->container()[ ContainerAdapter::class ] = $this->container();


		}

		public function captureRequest ( RequestInterface $request = null ) : Application {

			$this->container()->instance(
				RequestInterface::class,
				$request ??  Request::capture()
			);


			return $this;


		}

		public function handleErrors ( string $editor = 'phpstorm' ) : Application {

			$request = $this->container()->make(RequestInterface::class);

			$error_handler = ( new ExceptionHandlerFactory(
				WP_DEBUG,
				$request->isAjax(),
				$editor)
			)->create();

			$this->container()->instance(ExceptionHandlerFactory::class, $error_handler);

			$error_handler->register();

			$error_handler->allowQuit(true);
			$error_handler->writeToOutput(true);

			return $this;

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
		public function boot( array $config = [] ) :void {



			if ( $this->bootstrapped ) {

				throw new ConfigurationException( static::class . ' already bootstrapped.' );

			}

			$this->bindConfig( $config );

			$this->loadServiceProviders( $this->container());

			$this->bootstrapped = true;


		}

		private function bindConfig( array $config ) {

			$config = new ApplicationConfig($config);

			$this->container()->instance(ApplicationConfig::class, $config );
			$this->config = $config;

		}

		public function config (string $key, $default = null ) {

			return $this->config->get($key, $default);

		}

	}

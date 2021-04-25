<?php


	namespace WPEmerge\Requests;

	use GuzzleHttp\Psr7\ServerRequest;
	use WPEmerge\Contracts\RequestInterface;
	use WPEmerge\Contracts\RouteInterface;
	use WPEmerge\Events\IncomingRequest;
	use WPEmerge\Support\Arr;

	/**
	 * A representation of a request to the server.
	 */
	class Request extends ServerRequest implements RequestInterface {

		/**
		 * @var \WPEmerge\Routing\Route|null The route that our request matched
		 */
		private $route = null;

		/** @var string The Type of request that's being handled. . */
		private $type;

		/**
		 * @todo Replace this with some sort of fallback route.
		 * @var bool
		 */
		public $force_route_match = false;

		public static function fromGlobals() : Request {

			$request = parent::fromGlobals();
			$new     = new self(
				$request->getMethod(),
				$request->getUri(),
				$request->getHeaders(),
				$request->getBody(),
				$request->getProtocolVersion(),
				$request->getServerParams()
			);

			return $new
				->withCookieParams( $_COOKIE )
				->withQueryParams( $_GET )
				->withParsedBody( $_POST )
				->withUploadedFiles( static::normalizeFiles( $_FILES ) );
		}


		public function getUrl() {

			return $this->getUri();
		}

		protected function getMethodOverride( $default ) {

			$valid_overrides = [ 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS' ];
			$override        = $default;

			$header_override = (string) $this->getHeaderLine( 'X-HTTP-METHOD-OVERRIDE' );
			if ( ! empty( $header_override ) ) {
				$override = strtoupper( $header_override );
			}

			$body_override = (string) $this->body( '_method', '' );
			if ( ! empty( $body_override ) ) {
				$override = strtoupper( $body_override );
			}

			if ( in_array( $override, $valid_overrides, true ) ) {
				return $override;
			}

			return $default;
		}

		/**
		 * {@inheritDoc}
		 */
		public function getMethod() {

			$method = parent::getMethod();

			if ( $method === 'POST' ) {
				$method = $this->getMethodOverride( $method );
			}

			return $method;
		}

		/**
		 * {@inheritDoc}
		 */
		public function isGet() {

			return $this->getMethod() === 'GET';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isHead() {

			return $this->getMethod() === 'HEAD';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isPost() {

			return $this->getMethod() === 'POST';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isPut() {

			return $this->getMethod() === 'PUT';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isPatch() {

			return $this->getMethod() === 'PATCH';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isDelete() {

			return $this->getMethod() === 'DELETE';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isOptions() {

			return $this->getMethod() === 'OPTIONS';
		}

		/**
		 * {@inheritDoc}
		 */
		public function isReadVerb() {

			return in_array( $this->getMethod(), [ 'GET', 'HEAD', 'OPTIONS' ] );
		}

		/**
		 * {@inheritDoc}
		 */
		public function isAjax() {

			return strtolower( $this->getHeaderLine( 'X-Requested-With' ) ) === 'xmlhttprequest';
		}

		/**
		 * Get all values or a single one from an input type.
		 *
		 * @param  array  $source
		 * @param  string  $key
		 * @param  mixed  $default
		 *
		 * @return mixed
		 */
		protected function get( $source, $key = '', $default = null ) {

			if ( empty( $key ) ) {
				return $source;
			}

			return Arr::get( $source, $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function attributes( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getAttributes(), $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function query( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getQueryParams(), $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function body( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getParsedBody(), $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function cookies( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getCookieParams(), $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function files( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getUploadedFiles(), $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function server( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getServerParams(), $key, $default );
		}

		/**
		 * {@inheritDoc}
		 * @see ::get()
		 */
		public function headers( $key = '', $default = null ) {

			return call_user_func( [ $this, 'get' ], $this->getHeaders(), $key, $default );
		}

		public function getFullUrlString() : string {

			$uri   = $this->getUri();
			$https = $uri->getScheme();
			$path  = $uri->getPath();
			$host  = $uri->getHost();

			return $https . ':' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $host . $path;

		}

		public function setRoute( RouteInterface $route ) {

			$this->route = $route;
		}

		public function setType( string $request_type ) {

			$this->type = $request_type;

		}

		public function type() : string {

			return $this->type;

		}

		public function route() : ?RouteInterface {

			return $this->route;

		}

		public function forceMatch() {

			$this->force_route_match = true;

		}

	}

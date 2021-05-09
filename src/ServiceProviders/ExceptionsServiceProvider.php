<?php


	declare( strict_types = 1 );


	namespace WPEmerge\ServiceProviders;

	use WPEmerge\Contracts\ServiceProvider;


	/**
	 * Provide exceptions dependencies.
	 *
	 */
	class ExceptionsServiceProvider extends ServiceProvider {


		public function register() : void {

			$this->config->extend( 'debug.enable', true );
			$this->config->extend( 'debug.pretty_errors', true );


		}

		public function bootstrap() : void {

			// Nothing to bootstrap.

		}

	}

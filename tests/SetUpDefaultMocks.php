<?php


	declare( strict_types = 1 );


	namespace Tests;

	use Mockery;
	use WPEmerge\Facade\WP;

	trait SetUpDefaultMocks {

		protected function setWpApiMocks () {

			WP::shouldReceive( 'isAdmin' )->andReturnFalse()->byDefault();
			WP::shouldReceive( 'isAdminAjax' )->andReturnFalse()->byDefault();

		}

		protected function closeMockery () {

			Mockery::close();

		}

	}
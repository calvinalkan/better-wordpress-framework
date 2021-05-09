<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Exceptions;

	use WPEmerge\Events\ExceptionHandled;

	class ShutdownHandler {


		public function exceptionHandled ( ExceptionHandled $event ) {

			exit(1);

		}

	}
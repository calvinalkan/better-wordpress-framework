<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Exceptions;

	use WPEmerge\Http\Response;

	class AuthorizationException extends Exception {

		public $redirect_to;

		public function render() {

			return new Response('You are not allowed to do this.', 419 );

		}

	}
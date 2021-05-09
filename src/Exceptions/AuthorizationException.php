<?php


	declare( strict_types = 1 );


	namespace WPEmerge\Exceptions;

	class AuthorizationException extends Exception {

		public $redirect_to;

	}
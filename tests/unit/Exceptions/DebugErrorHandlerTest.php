<?php


	declare( strict_types = 1 );

	namespace Tests\unit\Exceptions;


	use Exception;
	use PHPUnit\Framework\TestCase;
	use Tests\AssertsResponse;
	use WPEmerge\Contracts\ErrorHandlerInterface;
	use WPEmerge\Contracts\ResponseInterface;
	use WPEmerge\Factories\ErrorHandlerFactory;

	class DebugErrorHandlerTest extends TestCase {

		use AssertsResponse;

		/** @test */
		public function exceptions_are_rendered_with_whoops () {


			$handler = $this->newErrorHandler();

			$handler->writeToOutput(false);

			$exception = new Exception('Whoops Exception');

			$response = $handler->transformToResponse( $this->createRequest(), $exception );

			$this->assertInstanceOf(ResponseInterface::class, $response);
			$this->assertOutput('Whoops Exception', $response);
			$this->assertStatusCode(500, $response);
			$this->assertContentType('text/html', $response);


		}

		/** @test */
		public function debug_data_is_provided_in_the_json_response_for_ajax_request () {

			$handler = $this->newErrorHandler(TRUE);
			$handler->writeToOutput(false);

			$exception = new Exception('Whoops Ajax Exception');

			$response = $handler->transformToResponse( $this->createRequest(), $exception );


			$output = json_decode( $response->body(), true )['error'];
			$this->assertSame( 'Exception', $output['type'] );
			$this->assertSame( 'Whoops Ajax Exception', $output['message'] );
			$this->assertArrayHasKey( 'code', $output );
			$this->assertArrayHasKey( 'trace', $output );
			$this->assertArrayHasKey( 'file', $output );
			$this->assertArrayHasKey( 'line', $output );

		}


		private function newErrorHandler (bool $is_ajax = false ) : ErrorHandlerInterface {

			return (( new ErrorHandlerFactory(true, $is_ajax) ))->create();

		}



	}

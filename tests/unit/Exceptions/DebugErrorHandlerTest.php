<?php


	declare( strict_types = 1 );

	namespace Tests\unit\Exceptions;


	use Exception;
	use PHPUnit\Framework\TestCase;
	use Tests\AssertsResponse;
	use Tests\TestRequest;
	use WPEmerge\Application\ApplicationEvent;
	use WPEmerge\Exceptions\DebugErrorHandler;
	use WPEmerge\Factories\ErrorHandlerFactory;

	class DebugErrorHandlerTest extends TestCase {

		use AssertsResponse;

		protected function setUp() : void {

			parent::setUp();
			ApplicationEvent::make();
			ApplicationEvent::fake();

		}

		protected function tearDown() : void {

			parent::tearDown();

			ApplicationEvent::setInstance(null );

		}

		/** @test */
		public function exceptions_are_rendered_with_whoops () {


			$handler = $this->newErrorHandler();

			$exception = new Exception('Whoops Exception');

			ob_start();
			$handler->transformToResponse( $this->createRequest(), $exception );
			$output = ob_get_clean();

			$this->assertStringContainsString('Whoops Exception', $output);


		}


		/** @test */
		public function debug_data_is_provided_in_the_json_response_for_ajax_request () {

			$handler = $this->newErrorHandler(TRUE);


			$exception = new Exception('Whoops Ajax Exception');

			ob_start();
			$handler->transformToResponse( $this->createRequest(), $exception );
			$response = ob_get_clean();

			$output = json_decode( $response, true )['error'];
			$this->assertSame( 'Exception', $output['type'] );
			$this->assertSame( 'Whoops Ajax Exception', $output['message'] );
			$this->assertArrayHasKey( 'code', $output );
			$this->assertArrayHasKey( 'trace', $output );
			$this->assertArrayHasKey( 'file', $output );
			$this->assertArrayHasKey( 'line', $output );
			$this->assertArrayHasKey( 'trace', $output );

		}


		private function newErrorHandler (bool $is_ajax = false ) : DebugErrorHandler {

			$request = TestRequest::from('GET', 'foo');
			$request->overrideGlobals();

			return ErrorHandlerFactory::make(
				$request,
				true,
				$is_ajax
			);

		}



	}

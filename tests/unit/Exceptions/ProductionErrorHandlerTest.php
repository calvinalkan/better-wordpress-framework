<?php


	declare( strict_types = 1 );


	namespace Tests\unit\Exceptions;

	use PHPUnit\Framework\TestCase;
	use SniccoAdapter\BaseContainerAdapter;
	use Tests\AssertsResponse;
	use Tests\stubs\TestException;
	use WPEmerge\Application\ApplicationEvent;
	use WPEmerge\Contracts\ResponseInterface;
	use WPEmerge\Events\UnrecoverableExceptionHandled;
	use WPEmerge\Exceptions\ProductionErrorHandler;
	use WPEmerge\Factories\ErrorHandlerFactory;
	use WPEmerge\Http\Response;

	class ProductionErrorHandlerTest extends TestCase {

		use AssertsResponse;

		protected function setUp() : void {

			parent::setUp();

			ApplicationEvent::make();
			ApplicationEvent::fake();

		}

		protected function tearDown() : void {

			parent::tearDown();

			ApplicationEvent::setInstance(null);

		}



		/** @test */
		public function inside_the_routing_flow_the_exceptions_get_transformed_into_response_objects() {


			$handler = $this->newErrorHandler();
			$handler->transformToResponse(new TestException('Sensitive Info') );

			ApplicationEvent::assertNotDispatched(UnrecoverableExceptionHandled::class);


		}

		/** @test */
		public function outside_the_routing_flow_exceptions_will_lead_to_script_termination () {

			$handler = $this->newErrorHandler();

			ob_start();
			$handler->handleException( new TestException('Sensitive Info') );
			$output = ob_get_clean();

			$this->assertStringContainsString('Internal Server Error', $output);


			ApplicationEvent::assertDispatched(UnrecoverableExceptionHandled::class);

		}

		/** @test */
		public function for_ajax_request_the_content_type_is_set_correctly () {

			$handler = $this->newErrorHandler(true);


			$response = $handler->transformToResponse( new TestException('Sensitive Info') );

			$this->assertInstanceOf(ResponseInterface::class, $response);
			$this->assertStatusCode(500, $response);
			$this->assertContentType('application/json', $response);
			$this->assertOutput('Internal Server Error', $response );

			ApplicationEvent::assertNotDispatched(UnrecoverableExceptionHandled::class);

		}

		/** @test */
		public function an_unspecified_exception_gets_converted_into_a_500_internal_server_error () {

			$handler = $this->newErrorHandler();

			$response = $handler->transformToResponse( new TestException('Sensitive Info') );

			$this->assertInstanceOf(ResponseInterface::class, $response);
			$this->assertStatusCode(500, $response);
			$this->assertContentType('text/html', $response);
			$this->assertOutput('Internal Server Error', $response);


		}

		/** @test */
		public function an_exception_can_have_custom_rendering_logic () {

			$handler = $this->newErrorHandler();

			$response = $handler->transformToResponse( new ReportTableException() );

			$this->assertInstanceOf(ResponseInterface::class, $response);
			$this->assertStatusCode(500, $response);
			$this->assertContentType('text/html', $response);
			$this->assertOutput('Foo', $response);

		}


		private function newErrorHandler (bool $is_ajax = false ) : ProductionErrorHandler {

			return ErrorHandlerFactory::make(new BaseContainerAdapter(), false, $is_ajax);

		}


	}

	class ReportTableException extends \Exception {

		public function render () {
			return new Response('Foo', 500);
		}

	}
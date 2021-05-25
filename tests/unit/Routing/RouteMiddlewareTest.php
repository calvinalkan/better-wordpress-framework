<?php


    declare(strict_types = 1);


    namespace Tests\unit\Routing;

    use Contracts\ContainerAdapter;
    use Mockery;
    use Tests\traits\CreateDefaultWpApiMocks;
    use Tests\traits\TestHelpers;
    use Tests\UnitTest;
    use Tests\stubs\Middleware\BarMiddleware;
    use Tests\stubs\Middleware\BazMiddleware;
    use Tests\stubs\Middleware\FooBarMiddleware;
    use Tests\stubs\Middleware\FooMiddleware;
    use WPEmerge\Application\ApplicationEvent;
    use WPEmerge\Facade\WP;
    use WPEmerge\Http\Request;
    use WPEmerge\Routing\Router;

    class RouteMiddlewareTest extends UnitTest
    {

        use TestHelpers;
        use CreateDefaultWpApiMocks;

        /**
         * @var ContainerAdapter
         */
        private $container;

        /** @var Router */
        private $router;

        protected function beforeTestRun()
        {

            $this->container = $this->createContainer();
            $this->routes = $this->newRouteCollection();
            ApplicationEvent::make($this->container);
            ApplicationEvent::fake();
            WP::setFacadeContainer($this->container);

        }

        protected function beforeTearDown()
        {

            ApplicationEvent::setInstance(null);
            Mockery::close();
            WP::reset();

        }

        /** @test */
        public function applying_a_route_group_to_a_route_applies_all_middleware_in_the_group()
        {

            $this->createRoutes(function () {

                $this->router->get('/foo', function (Request $request) {

                    return $request->body;

                })->middleware('foobar');

            });

            $kernel = $this->newKernel([
                'foobar' => [
                    FooMiddleware::class,
                    BarMiddleware::class,

                ],
            ]);

            $request = $this->webRequest('GET', '/foo');

            $output = $this->runKernelAndGetOutput($request, $kernel);

            $this->assertSame('foobar', $output);


        }

        /** @test */
        public function middleware_in_the_global_group_is_always_applied()
        {

            $this->createRoutes(function () {

                $this->router->get('/foo', function (Request $request) {

                    return $request->body;

                });


            });

            $request = $this->webRequest('GET', '/foo');

            $kernel = $this->newKernel([
                'global' => [
                    FooMiddleware::class,
                    BarMiddleware::class,

                ],
            ]);

            $output = $this->runKernelAndGetOutput($request, $kernel);

            $this->assertSame('foobar', $output);

        }

        /** @test */
        public function duplicate_middleware_is_filtered_out()
        {

            $this->createRoutes(function () {

                $this->router->middleware('foobar')->get('/foo', function (Request $request) {

                    return $request->body;

                });


            });

            $kernel = $this->newKernel(
                [
                    'global' => [
                        FooMiddleware::class,
                        BarMiddleware::class,
                    ],
                    'foobar' => [
                        FooMiddleware::class,
                        BarMiddleware::class,
                    ]

                ]);

            $request = $this->webRequest('GET', '/foo');

            $this->assertSame('foobar', $this->runKernelAndGetOutput($request, $kernel));


        }

        /** @test */
        public function duplicate_middleware_is_filtered_out_when_passing_the_same_middleware_arguments()
        {

            $this->router->aliasMiddleware('foo', FooMiddleware::class);
            $this->router->middlewareGroup('all', [

                FooMiddleware::class.':FOO',
                BarMiddleware::class,
                BazMiddleware::class,

            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware(['all', 'foo:FOO']);

            $this->router->loadRoutes();

            $request = $this->request('GET', 'foo');
            $this->assertOutput('FOObarbaz', $this->router->runRoute($request));

        }

        /** @test */
        public function multiple_middleware_groups_can_be_applied()
        {

            $this->router->middlewareGroup('foo', [
                FooMiddleware::class,
            ]);

            $this->router->middlewareGroup('bar', [
                BarMiddleware::class,
            ]);

            $this->router->middleware('foo', 'bar')
                         ->get('/foo', function (Request $request) {

                             return $request->body;

                         });

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $response = $this->router->runRoute($request);

            $this->assertOutput('foobar', $response);

        }

        /** @test */
        public function middleware_can_be_aliased()
        {


            $this->router->middleware('foo')->get('foo', function (Request $request) {

                return $request->body;

            });

            $this->router->loadRoutes();

            $this->router->aliasMiddleware('foo', FooMiddleware::class);
            $request = $this->request('GET', '/foo');
            $this->assertOutput('foo', $this->router->runRoute($request));


        }

        /** @test */
        public function unknown_middleware_throws_an_exception()
        {

            $this->expectExceptionMessage('Unknown middleware [foo]');

            $this->router->middleware('foo')->get('foo', function (Request $request) {

                return $request->body;

            });

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('foo', $this->router->runRoute($request));


        }


        /** @test */
        public function multiple_middleware_arguments_can_be_passed()
        {

            $this->router->aliasMiddleware('foobar', FooBarMiddleware::class);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware('foobar');

            $this->router->post('/foo', function (Request $request) {

                return $request->body;

            })->middleware('foobar:FOO');

            $this->router->patch('/foo', function (Request $request) {

                return $request->body;

            })->middleware('foobar:FOO,BAR');

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('foobar', $this->router->runRoute($request));

            $request = $this->request('POST', '/foo');
            $this->assertOutput('FOObar', $this->router->runRoute($request));

            $request = $this->request('PATCH', '/foo');
            $this->assertOutput('FOOBAR', $this->router->runRoute($request));

        }

        /** @test */
        public function a_middleware_group_can_point_to_a_middleware_alias()
        {

            $this->router->aliasMiddleware('foo', FooMiddleware::class);
            $this->router->middlewareGroup('foogroup', [
                'foo',
            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware('foogroup');

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('foo', $this->router->runRoute($request));

        }

        /** @test */
        public function group_and_route_middleware_can_be_combined()
        {

            $this->router->aliasMiddleware('baz', BazMiddleware::class);
            $this->router->middlewareGroup('foobar', [
                FooMiddleware::class,
                BarMiddleware::class,
            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware(['baz', 'foobar']);

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('bazfoobar', $this->router->runRoute($request));


        }

        /** @test */
        public function a_middleware_group_can_contain_another_middleware_group()
        {

            $this->router->middlewareGroup('foo', [
                FooMiddleware::class,
            ]);

            $this->router->middlewareGroup('bar', [
                BarMiddleware::class,
                'foo',
            ]);

            $this->router->middlewareGroup('baz', [
                BazMiddleware::class,
                'bar',
            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware('baz');

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('bazbarfoo', $this->router->runRoute($request));


        }

        /** @test */
        public function middleware_can_be_applied_without_an_alias()
        {

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware(FooBarMiddleware::class.':FOO,BAR');

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('FOOBAR', $this->router->runRoute($request));


        }

        /** @test */
        public function string_middleware_without_an_alias_will_raise_an_exception()
        {

            $this->expectExceptionMessage('Unknown middleware [foo] used.');

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware('foo');

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertNullResponse($this->router->runRoute($request));

        }


        /**
         *
         *
         *
         *
         * SORTING
         *
         *
         *
         *
         */

        /** @test */
        public function non_global_middleware_can_be_sorted()
        {

            $this->router->middlewarePriority([

                FooMiddleware::class,
                BarMiddleware::class,
                BazMiddleware::class,

            ]);

            $this->router->middlewareGroup('barbaz', [

                BazMiddleware::class,
                BarMiddleware::class,

            ]);

            $this->router->middleware('barbaz')->group(function () {

                $this->router->get('/foo', function (Request $request) {

                    return $request->body;

                })->middleware(FooMiddleware::class);

            });

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('foobarbaz', $this->router->runRoute($request));

        }

        /** @test */
        public function middleware_keeps_its_relative_position_if_its_has_no_priority_defined()
        {


            $this->router->middlewarePriority([

                FooMiddleware::class,
                BarMiddleware::class,

            ]);

            $this->router->middlewareGroup('all', [

                FooBarMiddleware::class,
                BarMiddleware::class,
                BazMiddleware::class,
                FooMiddleware::class,

            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware('all');

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('foobarfoobarbaz', $this->router->runRoute($request));

        }

        /** @test */
        public function if_a_global_middleware_group_is_applied_its_middleware_will_be_excluded_from_the_sorting_and_always_come_first()
        {

            $this->router->middlewareGroup('global', [
                FooMiddleware::class,
                BarMiddleware::class,
            ]);

            $this->router->middlewarePriority([

                BazMiddleware::class,
                FooBarMiddleware::class,
                BarMiddleware::class,
                FooMiddleware::class,

            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware([FooBarMiddleware::class, BazMiddleware::class]);

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $this->assertOutput('foobarbazfoobar', $this->router->runRoute($request));


        }

        /** @test */
        public function all_middleware_can_be_skipped()
        {

            $this->router->middlewareGroup('global', [
                FooMiddleware::class,
                BarMiddleware::class,
            ]);

            $this->router->get('/foo', function (Request $request) {

                return $request->body;

            })->middleware([FooBarMiddleware::class, BazMiddleware::class]);

            // The middleware does not run and cant append anything to $request->body
            $this->router->withoutMiddleware();

            $this->router->loadRoutes();

            $request = $this->request('GET', '/foo');
            $request->body = 'test';
            $this->assertOutput('test', $this->router->runRoute($request));


        }

    }
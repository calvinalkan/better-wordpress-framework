<?php


    declare(strict_types = 1);


    namespace Tests\integration\ServiceProviders;

    use BetterWpHooks\Contracts\Dispatcher;
    use BetterWpHooks\Dispatchers\WordpressDispatcher;
    use Tests\IntegrationTest;
    use Tests\fixtures\Middleware\FooMiddleware;
    use Tests\stubs\TestApp;
    use Tests\unit\Routing\Foo;
    use WPEmerge\Contracts\AbstractRedirector;
    use WPEmerge\Http\Cookies;
    use WPEmerge\Http\HttpKernel;
    use WPEmerge\Http\Redirector;
    use WPEmerge\Http\ResponseFactory;
    use WPEmerge\Http\StatefulRedirector;
    use WPEmerge\Session\SessionServiceProvider;

    class HttpServiceProviderTest extends IntegrationTest
    {

        /** @test */
        public function the_kernel_can_be_resolved_correctly()
        {

            $this->newTestApp();

            $this->assertInstanceOf(HttpKernel::class, TestApp::resolve(HttpKernel::class));


        }

        /** @test */
        public function the_response_factory_can_be_resolved()
        {

            $this->newTestApp();

            $this->assertInstanceOf(ResponseFactory::class, TestApp::resolve(ResponseFactory::class));

        }

        /** @test */
        public function middleware_aliases_are_bound()
        {

            $this->newTestApp([
                'middleware' => [
                    'aliases' => [
                        'foo' => Foo::class,
                    ],
                ],
            ]);

            $aliases = TestApp::config('middleware.aliases', []);

            $this->assertArrayHasKey('auth', $aliases);
            $this->assertArrayHasKey('guest', $aliases);
            $this->assertArrayHasKey('can', $aliases);
            $this->assertArrayHasKey('foo', $aliases);

        }

        /** @test */
        public function middleware_groups_are_bound()
        {

            $this->newTestApp([
                'middleware' => [
                    'groups' => [
                        'dashboard' => 'DashBoardMiddleware',
                    ],
                ],
            ]);

            $m_groups = TestApp::config('middleware.groups', []);

            $this->assertArrayHasKey('global', $m_groups);
            $this->assertArrayHasKey('web', $m_groups);
            $this->assertArrayHasKey('admin', $m_groups);
            $this->assertArrayHasKey('ajax', $m_groups);
            $this->assertArrayHasKey('dashboard', $m_groups);

        }

        /** @test */
        public function middleware_priority_is_set()
        {

            $this->newTestApp([
                'middleware' => [
                    'priority' => [
                        FooMiddleware::class,
                    ],
                ],
            ]);

            $priority = TestApp::config('middleware.priority', []);

            $this->assertContains(FooMiddleware::class, $priority);


        }



        /** @test */
        public function the_cookie_instance_can_be_resolved () {

            $this->newTestApp();

            $this->assertInstanceOf(Cookies::class, TestApp::resolve(Cookies::class));

        }

        /** @test */
        public function the_redirector_can_be_resolved () {

            $this->newTestApp();

            $this->assertInstanceOf(Redirector::class, TestApp::resolve(AbstractRedirector::class));

        }

        /** @test */
        public function if_sessions_are_enabled_a_stateful_redirector_is_used () {

            $this->newTestApp([
                'session' => [
                    'enabled'=> true
                ],
                'providers'=> [
                    SessionServiceProvider::class
                ]
            ]);

            $this->assertInstanceOf(StatefulRedirector::class, TestApp::resolve(AbstractRedirector::class));

        }

    }


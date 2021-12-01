<?php

declare(strict_types=1);

namespace Tests\unit\core\Factories;

use Tests\UnitTest;
use Snicco\Routing\ClosureAction;
use Tests\concerns\CreateContainer;
use Snicco\Routing\ControllerAction;
use Snicco\Factories\RouteActionFactory;
use Tests\fixtures\Controllers\Web\WebController;

use const TEST_CONFIG;

class RouteActionFactoryTest extends UnitTest
{
    
    use CreateContainer;
    
    private RouteActionFactory $factory;
    
    protected function setUp() :void
    {
        parent::setUp();
        
        $this->factory =
            new RouteActionFactory(TEST_CONFIG['controllers'], $this->createContainer());
    }
    
    /** @test */
    public function a_passed_closure_always_results_in_a_closure_handler()
    {
        $foo = function () {
            return 'foo';
        };
        
        $handler = $this->factory->create($foo, '');
        
        $this->assertInstanceOf(ClosureAction::class, $handler);
        
        $this->assertSame('foo', $handler->execute([]));
    }
    
    /** @test */
    public function a_fully_qualified_namespaced_class_callable_results_in_a_controller()
    {
        $controller = WebController::class.'@handle';
        
        $handler = $this->factory->create($controller, '');
        
        $this->assertInstanceOf(ControllerAction::class, $handler);
        
        $this->assertEquals('web_controller', $handler->execute([]));
    }
    
    /** @test */
    public function an_array_callable_results_in_a_controller()
    {
        $controller = [WebController::class, 'handle'];
        
        $handler = $this->factory->create($controller, '');
        
        $this->assertInstanceOf(ControllerAction::class, $handler);
        
        $this->assertEquals('web_controller', $handler->execute([]));
    }
    
    /** @test */
    public function non_existing_controller_classes_raise_an_exception()
    {
        $this->expectExceptionMessage("[InvalidController, 'handle'] is not a valid callable.");
        
        $controller = 'InvalidController@handle';
        
        $this->factory->create($controller, '');
    }
    
    /** @test */
    public function non_callable_methods_on_a_controller_raise_an_exception()
    {
        $this->expectExceptionMessage(
            "[".WebController::class.", 'invalidMethod'] is not a valid callable."
        );
        
        $controller = [WebController::class, 'invalidMethod'];
        
        $this->factory->create($controller, '');
    }
    
    /** @test */
    public function controllers_can_be_resolved_without_the_full_namespace()
    {
        $controller = ['WebController', 'handle'];
        $handler = $this->factory->create($controller, '');
        $this->assertInstanceOf(ControllerAction::class, $handler);
        $this->assertEquals('web_controller', $handler->execute([]));
        
        $controller = 'WebController@handle';
        $handler = $this->factory->create($controller, '');
        $this->assertInstanceOf(ControllerAction::class, $handler);
        $this->assertEquals('web_controller', $handler->execute([]));
    }
    
    /** @test */
    public function a_controller_can_be_invokeable()
    {
        $handler = $this->factory->create(InvokableController::class, '');
        
        $this->assertInstanceOf(ControllerAction::class, $handler);
        
        $this->assertEquals('invoked_controller', $handler->execute([]));
    }
    
}

class InvokableController
{
    
    public function __invoke()
    {
        return 'invoked_controller';
    }
    
}
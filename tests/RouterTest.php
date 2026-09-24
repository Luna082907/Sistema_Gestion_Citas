<?php

declare(strict_types=1);

namespace Tests;

use App\Controller\DoctorController;
use App\Core\Router;
use App\Repository\DoctorRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testAgendaRoutesMatchGetRequests(): void
    {
        $router = new Router();
        $calledPaths = [];

        $router->get('/agenda', static function () use (&$calledPaths): void {
            $calledPaths[] = '/agenda';
        });

        $router->get('/appointments/agenda', static function () use (&$calledPaths): void {
            $calledPaths[] = '/appointments/agenda';
        });

        $router->dispatch('GET', '/agenda');
        $router->dispatch('GET', '/appointments/agenda');

        $this->assertSame(['/agenda', '/appointments/agenda'], $calledPaths);
    }

    public function testProtectedRoomRoutesRunMiddleware(): void
    {
        $middlewareCalled = false;
        $handlerCalled = false;
        $router = new Router();

        $router->get('/rooms/create', static function () use (&$handlerCalled): void {
            $handlerCalled = true;
        }, [static function () use (&$middlewareCalled): void {
            $middlewareCalled = true;
        }]);

        $router->dispatch('GET', '/rooms/create');

        $this->assertTrue($middlewareCalled);
        $this->assertTrue($handlerCalled);
    }

    public function testDoctorSpecialtyValidationAcceptsAccentedValues(): void
    {
        $controller = new DoctorController(new DoctorRepository(new PDO('sqlite::memory:')));
        $method = new \ReflectionMethod($controller, 'validate');
        $method->setAccessible(true);

        $errors = $method->invoke($controller, [
            'license_number' => 'ABC-123',
            'first_name' => 'Ana',
            'last_name' => 'Ramírez',
            'specialty' => 'Cardiología',
            'active' => '1',
        ]);

        $this->assertSame([], $errors);
    }
}

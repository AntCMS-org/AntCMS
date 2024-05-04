<?php

use AntCMS\HookController;
use PHPUnit\Framework\TestCase;

class HookTest extends TestCase
{
    public function hookCallback(array $params): void
    {
        $this->assertIsArray($params);
        $this->assertArrayHasKey('test', $params);
        $this->assertTrue($params['test'] === 'data');
    }

    public function testHookNameAlllowed(): void
    {
        $result = HookController::registerHook('thisIs_Valid1234', '');
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testHookNameNotAllowed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The hook name 'thisIs NOT Valid1234' is invalid. Only a-z A-Z, 0-9, and _ are allowed to be in the hook name.");
        HookController::registerHook('thisIs NOT Valid1234', '');
    }

    public function testHookWorks(): void
    {
        $name = 'testHook';

        $result = HookController::registerHook($name, '');
        $this->assertIsBool($result);
        $this->assertTrue($result);

        HookController::registerCallback($name, [$this, 'hookCallback']);
        HookController::fire($name, ['test' => 'data']);
    }

    public function testIsRegistered(): void
    {
        $name = 'notYetRegistered';

        $result = HookController::isRegistered($name);
        $this->assertIsBool($result);
        $this->assertFalse($result);

        HookController::registerHook($name, '');

        $result = HookController::isRegistered($name);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }
}

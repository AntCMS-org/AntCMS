<?php

use AntCMS\HookController;
use PHPUnit\Framework\TestCase;

class HookTest extends TestCase
{
    // Validates the data provided by a hook is as it should be.
    public function hookCallback(AntCMS\Event $event): void
    {
        $this->assertIsArray($event->getParameters());
        $this->assertArrayHasKey('test', $event->getParameters());
        $this->assertTrue($event->getParameters()['test'] === 'data');
        $this->assertEquals('testHook', $event->getHookName());
    }

    // Modifies an event's parameters so we can then assert the updated parameters were recieved.
    public function modifyHook(AntCMS\Event $event): AntCMS\Event
    {
        $event->setParameters(['Howdy!', 'How', "are", 'you?']);
        return $event;
    }

    public function testHookNameAlllowed(): void
    {
        $result = HookController::registerHook('thisIs_Valid1234');
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testHookNameNotAllowed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The hook name 'thisIs NOT Valid1234' is invalid. Only a-z A-Z, 0-9, and _ are allowed to be in the hook name.");
        HookController::registerHook('thisIs NOT Valid1234');
    }

    public function testHookWorks(): void
    {
        $name = 'testHook';

        $result = HookController::registerHook($name);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        HookController::registerCallback($name, $this->hookCallback(...));
        $event = HookController::fire($name, ['test' => 'data']);

        $this->assertEquals(3, $event->getReadCount());
        $this->assertEquals(0, $event->getUpdateCount());
        $this->assertTrue($event->isDone());
        $this->assertGreaterThan(0, $event->timeElapsed());
    }

    public function testHookParamUpdated(): void
    {
        $name = 'testHook';
        $startData = ['test' => 'data'];
        $expected = ['Howdy!', 'How', "are", 'you?'];

        $result = HookController::registerHook($name);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        HookController::registerCallback($name, $this->modifyHook(...));
        $event = HookController::fire($name, $startData);

        $this->assertEquals($expected, $event->getParameters());
        $this->assertEquals(1, $event->getUpdateCount());
    }

    public function testIsRegistered(): void
    {
        $name = 'notYetRegistered';

        $result = HookController::isRegistered($name);
        $this->assertIsBool($result);
        $this->assertFalse($result);

        HookController::registerHook($name);

        $result = HookController::isRegistered($name);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }
}

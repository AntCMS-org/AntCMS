<?php

use AntCMS\Twig;
use PHPUnit\Framework\TestCase;

class TwigTest extends TestCase
{
    public function testTemplateDoesNotExist(): void
    {
        Twig::registerTwig();
        $result = Twig::templateExists('thisdoesnotexist');
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testTemplateDoesExist(): void
    {
        Twig::registerTwig();
        $result = Twig::templateExists('default.html.twig');
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testRender(): void
    {
        Twig::registerTwig();
        $result = Twig::render('default.html.twig');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}

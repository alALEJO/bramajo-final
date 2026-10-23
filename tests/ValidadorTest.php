<?php

use PHPUnit\Framework\TestCase;

class ValidadorTest extends TestCase
{
    public function testPasswordValida(): void
    {
        $this->assertTrue((new Validador(['p' => 'Bramajo2026']))->password('p')->esValido());
    }

    public function testPasswordCortaOSinMayuscula(): void
    {
        $this->assertFalse((new Validador(['p' => 'Corta1']))->password('p')->esValido());
        $this->assertFalse((new Validador(['p' => 'sinmayuscula2026']))->password('p')->esValido());
    }

    public function testCorreoInvalido(): void
    {
        $v = (new Validador(['correo' => 'sinarroba.com']))->email('correo');
        $this->assertArrayHasKey('correo', $v->errores());
    }

    public function testRolFueraDeLaLista(): void
    {
        $v = (new Validador(['rol' => 'SUPERUSUARIO']))->enLista('rol', ['ADMIN_GENERAL', 'ORGANIZADOR', 'PARTICIPANTE'], 'Rol inválido.');
        $this->assertFalse($v->esValido());
    }

    public function testEscaparEvitaXss(): void
    {
        $this->assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', Validador::escapar('<script>alert(1)</script>'));
    }
}

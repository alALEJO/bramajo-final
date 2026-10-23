<?php

use PHPUnit\Framework\TestCase;

// Pruebas de los tres módulos de competencia, sin base de datos.
class CompetenciaTest extends TestCase
{
    private array $torneo = ['puntos_victoria' => 3, 'puntos_empate' => 1, 'puntos_derrota' => 0, 'cantidad_rondas' => null];

    // Juega una ronda: gana siempre el local 2 a 1.
    private function jugar(array $cruces): array
    {
        $ronda = ['estado' => 'CERRADA', 'cruces' => []];
        foreach ($cruces as [$local, $visitante]) {
            $ronda['cruces'][] = ['id_local' => $local, 'id_visitante' => $visitante,
                                  'puntos_local' => $visitante ? 2 : null, 'puntos_visitante' => $visitante ? 1 : null];
        }
        return $ronda;
    }

    public function testLigaTodosContraTodosSinRepetir(): void
    {
        $liga = new Liga();
        $ids = [1, 2, 3, 4, 5, 6];
        $rondas = [];
        $parejas = [];
        for ($i = 0; $i < $liga->totalRondas(6, $this->torneo); $i++) {
            $nueva = $liga->generarRonda($ids, $rondas, $this->torneo);
            foreach ($nueva['cruces'] as [$a, $b]) {
                $parejas[] = min($a, $b) . '-' . max($a, $b);
            }
            $rondas[] = $this->jugar($nueva['cruces']);
        }
        $this->assertCount(15, $parejas);
        $this->assertCount(15, array_unique($parejas));
    }

    public function testLigaImparDejaUnoLibrePorFecha(): void
    {
        $nueva = (new Liga())->generarRonda([1, 2, 3, 4, 5], [], $this->torneo);
        $libres = array_filter($nueva['cruces'], fn($c) => $c[1] === null);
        $this->assertCount(1, $libres);
        $this->assertEquals('Fecha 1', $nueva['nombre']);
    }

    public function testEliminacionConSeisDaDosPasesLibres(): void
    {
        $nueva = (new EliminacionDirecta())->generarRonda([1, 2, 3, 4, 5, 6], [], $this->torneo);
        $this->assertEquals('Cuartos de final', $nueva['nombre']);
        $this->assertCount(4, $nueva['cruces']);
        $this->assertCount(2, array_filter($nueva['cruces'], fn($c) => $c[1] === null));
    }

    public function testEliminacionAvanzanLosGanadoresHastaLaFinal(): void
    {
        $modulo = new EliminacionDirecta();
        $ids = [1, 2, 3, 4];
        $semifinal = $modulo->generarRonda($ids, [], $this->torneo);
        $rondas = [$this->jugar($semifinal['cruces'])];
        $final = $modulo->generarRonda($ids, $rondas, $this->torneo);
        $this->assertEquals('Final', $final['nombre']);
        $this->assertEquals([[1, 3]], $final['cruces']);
        $rondas[] = $this->jugar($final['cruces']);
        $this->assertEquals(1, $modulo->campeon($rondas));
        $this->assertTrue($modulo->terminado($ids, $rondas, $this->torneo));
    }

    public function testSuizoNoRepiteRivales(): void
    {
        $suizo = new SistemaSuizo();
        $ids = [1, 2, 3, 4, 5, 6, 7, 8];
        $torneo = $this->torneo + ['cantidad_rondas' => 3];
        $torneo['cantidad_rondas'] = 3;
        $rondas = [];
        $parejas = [];
        for ($i = 0; $i < 3; $i++) {
            $nueva = $suizo->generarRonda($ids, $rondas, $torneo);
            foreach ($nueva['cruces'] as [$a, $b]) {
                $parejas[] = min($a, $b) . '-' . max($a, $b);
            }
            $rondas[] = $this->jugar($nueva['cruces']);
        }
        $this->assertCount(12, array_unique($parejas));
    }

    public function testNoSeGeneraSinCerrarLaRondaAnterior(): void
    {
        $this->expectException(DomainException::class);
        (new Liga())->generarRonda([1, 2, 3, 4], [['estado' => 'ABIERTA', 'cruces' => []]], $this->torneo);
    }

    public function testTablaDePosiciones(): void
    {
        $ronda = ['estado' => 'CERRADA', 'cruces' => [
            ['id_local' => 1, 'id_visitante' => 2, 'puntos_local' => 3, 'puntos_visitante' => 0],
            ['id_local' => 3, 'id_visitante' => 4, 'puntos_local' => 1, 'puntos_visitante' => 1],
        ]];
        $tabla = (new Liga())->tabla([1, 2, 3, 4], [$ronda], $this->torneo);
        $this->assertEquals(1, $tabla[0]['id']);
        $this->assertEquals(3, $tabla[0]['pts']);
        $this->assertEquals(1, $tabla[1]['pts']);
        $this->assertEquals(2, $tabla[3]['id']);
    }
}

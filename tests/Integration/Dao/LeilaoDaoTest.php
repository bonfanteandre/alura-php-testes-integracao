<?php

namespace Alura\Leilao\Tests\Integration\Dao;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Infra\ConnectionCreator;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    /** @var \PDO */
    private static $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('
            CREATE TABLE leiloes (
                id INTEGER PRIMARY KEY,
                descricao TEXT,
                finalizado BOOL,
                dataInicio TEXT
            );'
        );
    }
    
    public function setUp(): void
    {
        self::$pdo->beginTransaction();
    }

    /**
     * @dataProvider leiloes
     */
    public function testeBuscaLeiloesNaoFinalizados(array $leiloes)
    {
        $leilaoDao = new LeilaoDao(self::$pdo);

        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }
        
        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Variant 1972', $leiloes[0]->recuperarDescricao());
        self::assertFalse($leiloes[0]->estaFinalizado());
    }

    /**
     * @dataProvider leiloes
     */
    public function testeBuscaLeiloesFinalizados(array $leiloes)
    {
        $leilaoDao = new LeilaoDao(self::$pdo);

        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Fiat 147', $leiloes[0]->recuperarDescricao());
        self::assertTrue($leiloes[0]->estaFinalizado());
    }

    public function testAoAtualizarLeilaoStatusDeveSerAlterado()
    {
        $leilao = new Leilao('Brasilia Amarela');
        
        $leilaoDao = new LeilaoDao(self::$pdo);
        $leilao = $leilaoDao->salva($leilao);

        $leilao->finaliza();
        $leilaoDao->atualiza($leilao);

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(1, $leiloes);
        self::assertSame('Brasilia Amarela', $leiloes[0]->recuperarDescricao());
        self::assertTrue($leiloes[0]->estaFinalizado());
    }

    public function tearDown(): void
    {
        self::$pdo->rollBack();
    }

    public function leiloes()
    {
        $leilaoNaoFinalizado = new Leilao('Variant 1972');

        $leilaoFinalizado = new Leilao('Fiat 147');
        $leilaoFinalizado->finaliza();

        return [
            [
                [$leilaoNaoFinalizado, $leilaoFinalizado]
            ]
        ];
    }
}

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

    public function testInsercaoEBuscaDevemFuncionar()
    {
        $leilaoVariant = new Leilao('Variant 1972');
        
        $leilaoDao = new LeilaoDao(self::$pdo);
        $leilaoDao->salva($leilaoVariant);

        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame('Variant 1972', $leiloes[0]->recuperarDescricao());
    }

    public function tearDown(): void
    {
        self::$pdo->rollBack();
    }
}

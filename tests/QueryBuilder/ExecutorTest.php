<?php

namespace SourceTest\QueryBuilder;

use PHPUnit\Framework\TestCase;
use Source\QueryBuilder\Executor;
use Source\QueryBuilder\Query\Delete;
use Source\QueryBuilder\Query\Insert;
use Source\QueryBuilder\Query\Select;
use Source\QueryBuilder\Query\Update;

class ExecutorTest extends TestCase
{
    private static $conn;
    private $executor;

    public static function setUpBeforeClass(): void
    {
        self::$conn = new \PDO('mysql:dbname=tdd;host=localhost', 'root','');
        self::$conn->exec("
            CREATE TABLE IF NOT EXISTS `products`(
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` TEXT,
                `price` FLOAT,
                `created_at` DATETIME,
                `updated_at` DATETIME,
                PRIMARY KEY(`id`)
            );
        ");
        self::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    }

    protected function setUp(): void
    {
        $this->executor = new Executor(self::$conn);
    }

    public static function tearDownAfterClass(): void
    {
        self::$conn->exec("DROP TABLE `products`");
    }

    public function testInsertANewProductInADatabase()
    {
        $query = new Insert('products', ['name', 'price', 'created_at', 'updated_at']);

        $this->executor->setQuery($query);

        $this->executor->setParams(':name', 'Product 1')
                       ->setParams(':price', 19.99)
                       ->setParams(':created_at', date('Y-m-d H:i:s'))
                       ->setParams(':updated_at', date('Y-m-d H:i:s'));

        $this->assertTrue($this->executor->execute());
    }

    public function testTheSelectionOfANewProduct()
    {
        $query = new Select('products');

        $this->executor->setQuery($query);
        $this->executor->execute();

        $products = $this->executor->getResult();
        $this->assertEquals('Product 1', $products[0]['name']);
        $this->assertEquals(19.99, $products[0]['price']);
    }

    public function testUpdateAndGetASingleProduct()
    {
        $query = new Update('products', ['name'], ['id' => 1]);
        $this->executor->setQuery($query);;
        $this->executor->setParams(":name", "Produto 1 Editado");

        $this->assertTrue($this->executor->execute());

        $this->executor = new Executor(self::$conn);

        $query = (new Select('products'))->where('id', '=', ':id');

        $this->executor->setQuery($query);
        $this->executor->setParams(':id', 1);
        $this->executor->execute();

        $products = $this->executor->getResult();


        $this->assertEquals('Produto 1 Editado', $products[0]['name']);
    }

    public function testDeleteAProductFromDatabase()
    {
        $query = new Delete('products', ['id' => 1]);
        $this->executor->setQuery($query);;

        $this->assertTrue($this->executor->execute());

        $this->executor = new Executor(self::$conn);

        $query = (new Select('products'))->where('id', '=', ':id');

        $this->executor->setQuery($query);
        $this->executor->setParams(':id', 1);
        $this->executor->execute();

        $products = $this->executor->getResult();

        $this->assertCount(0, $products);
    }

}
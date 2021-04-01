<?php

namespace SourceTest\QueryBuilder\Query;

use PHPUnit\Framework\TestCase;
use Source\QueryBuilder\Query\Delete;

class DeleteTest extends TestCase
{

    private $delete;

    protected function assertPostConditions(): void
    {
        $this->assertTrue(class_exists(Delete::class));
    }

    protected function setUp(): void
    {
        $this->delete = new Delete('products', ['id' => 10]);
    }

    public function testIfDeleteQueryHasGeneratedWithSuccess()
    {
        $sql = "DELETE FROM products WHERE id = 10";

        $this->assertEquals($sql, $this->delete->getSql());
    }

}
<?php

namespace M6Web\Bundle\ElasticsearchBundle\Tests\Units\Elasticsearch\ConnectionPool\Selector;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\Connections\Connection;
use M6Web\Bundle\ElasticsearchBundle\Tests\Units\Elasticsearch\ConnectionMocker;
use mageekguy\atoum\test;

/**
 * Class RandomStickySelector
 *
 * @package M6Web\Bundle\ElasticsearchBundle\Tests\Units\Elasticsearch\ConnectionPool\Selector
 */
class RandomStickySelector extends test
{
    use ConnectionMocker;

    /**
     * Test the behavior of the select method if no connections are given.
     */
    public function testSelectFromEmpty()
    {
        $this
            ->if($this->newTestedInstance)

            ->then
                ->exception(
                    function () {
                        $this->testedInstance->select([]);
                    }
                )
                    ->isInstanceOf(NoNodesAvailableException::class);
    }

    /**
     * Test the stickiness of the select method.
     *
     * @return void
     */
    public function testSelectSticky()
    {
        $this
            ->given($connections = $this->getConnectionMocks(1000))
            ->if($this->newTestedInstance)

            // Test returned class is a connection.
            ->then
                ->object($firstConnectionReturned = $this->testedInstance->select($connections))
                    ->isInstanceOf(Connection::class)

            // Every next select should be identical to the first.
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($firstConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($firstConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($firstConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($firstConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($firstConnectionReturned)

            // First connection returned is excluded from the available connections.
            ->if($connections =
                array_filter(
                    $connections,
                    function ($baseConnection) use ($firstConnectionReturned) {
                        return $baseConnection !== $firstConnectionReturned;
                    }
                )
            )
            // Next connection returned is a new one and every call after should be the same.
            ->then
                ->object($newConnectionReturned = $this->testedInstance->select($connections))
                    ->isInstanceOf(Connection::class)
                    ->isNotIdenticalTo($firstConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($newConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($newConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($newConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($newConnectionReturned)
            ->and
                ->object($newSelect = $this->testedInstance->select($connections))
                    ->isIdenticalTo($newConnectionReturned);
    }
}

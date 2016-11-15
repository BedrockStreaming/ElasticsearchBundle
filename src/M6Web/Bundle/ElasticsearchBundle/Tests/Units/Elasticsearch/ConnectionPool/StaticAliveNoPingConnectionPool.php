<?php

namespace M6Web\Bundle\ElasticsearchBundle\Tests\Units\Elasticsearch\ConnectionPool;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\ConnectionPool\Selectors\SelectorInterface;
use Elasticsearch\Connections\Connection;
use Elasticsearch\Connections\ConnectionFactoryInterface;
use M6Web\Bundle\ElasticsearchBundle\Tests\Units\Elasticsearch\ConnectionMocker;
use mageekguy\atoum\test;

/**
 * Class StaticAliveNoPingConnectionPool
 *
 * @package M6Web\Bundle\ElasticsearchBundle\Tests\Units\Elasticsearch\ConnectionPool
 */
class StaticAliveNoPingConnectionPool extends test
{
    use ConnectionMocker;

    /**
     * Test Select
     */
    public function testNextConnectionAllOk()
    {
        $this
            ->given($connections = [$first = $this->getConnectionMockReady(), $second = $this->getConnectionMockReady()])
            ->if($this->newTestedInstance($connections, $this->getSelectorMock(), $this->getConnectionFactoryMock(), []))

            // Test returned class is a connection.
            ->then
                ->object($firstConnectionReturned = $this->testedInstance->nextConnection($connections))
                    ->isIdenticalTo($first);
    }

    /**
     * Test Select
     */
    public function testNextConnectionOneFailure()
    {
        $this
            ->given($connections = [$first = $this->getConnectionMockFailed(), $second = $this->getConnectionMockReady()])
            ->if($this->newTestedInstance($connections, $this->getSelectorMock(), $this->getConnectionFactoryMock(), []))

            // Test returned class is a connection.
            ->then
                ->object($firstConnectionReturned = $this->testedInstance->nextConnection($connections))
                    ->isIdenticalTo($second);
    }

    /**
     * Test Select
     */
    public function testNextConnectionFullFailure()
    {
        $this
            ->given($connections = [$first = $this->getConnectionMockFailed(), $second = $this->getConnectionMockFailed()])
            ->if($this->newTestedInstance($connections, $this->getSelectorMock(), $this->getConnectionFactoryMock(), []))

            // Test returned class is a connection.
            ->then
                ->exception(
                    function () use ($connections) {
                        $this->testedInstance->nextConnection($connections);
                    }
                )
                    ->isInstanceOf(NoNodesAvailableException::class);
    }

    /**
     * @return Connection
     */
    protected function getConnectionMockReady()
    {
        return $this->getConnectionMock(
            [
                'isAlive' => true,
                'getPingFailures' => 0,
                'getLastPing' => 0,
            ]
        );
    }

    /**
     * @return Connection
     */
    protected function getConnectionMockFailed()
    {
        return $this->getConnectionMock(
            [
                'isAlive' => false,
                'getPingFailures' => time(),
                'getLastPing' => time(),
            ]
        );
    }

    /**
     * @return SelectorInterface
     */
    protected function getSelectorMock()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $selectorMock = new \mock\Elasticsearch\ConnectionPool\Selectors\SelectorInterface;

        $this->calling($selectorMock)->select = function ($connections) {
            return reset($connections);
        };

        return $selectorMock;
    }

    /**
     * @return ConnectionFactoryInterface
     */
    protected function getConnectionFactoryMock()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $selectorMock = new \mock\Elasticsearch\Connections\ConnectionFactoryInterface;

        return $selectorMock;
    }
}

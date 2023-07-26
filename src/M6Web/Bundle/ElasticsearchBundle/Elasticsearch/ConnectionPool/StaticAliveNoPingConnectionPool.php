<?php

declare(strict_types=1);

namespace M6Web\Bundle\ElasticsearchBundle\Elasticsearch\ConnectionPool;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\ConnectionPool\StaticNoPingConnectionPool;
use Elasticsearch\Connections\Connection;
use Elasticsearch\Connections\ConnectionInterface;

/**
 * Class StaticAliveNoPingConnectionPool
 *
 * > Extend StaticNoPingConnectionPool and add the ability to remove any failed connection
 *    from the list of eligible connections to be chosen from by the selector.
 */
class StaticAliveNoPingConnectionPool extends StaticNoPingConnectionPool
{
    private int $pingTimeout = 60;
    private int $maxPingTimeout = 3600;

    /**
     * > Allow to customize the ping time out.
     */
    public function setPingTimeout(int $pingTimeout)
    {
        $this->pingTimeout = $pingTimeout;
    }

    /**
     * > Allow to customize the ping max time out.
     */
    public function setMaxPingTimeout(int $maxPingTimeout)
    {
        $this->maxPingTimeout = $maxPingTimeout;
    }

    /**
     * @throws NoNodesAvailableException
     */
    public function nextConnection(bool $force = false): ConnectionInterface
    {
        // > Replace $this->connections by $connections in order to modify the list later.
        $connections = $this->connections;
        $total = count($connections);
        while ($total--) {
            /** @var Connection $connection */
            $connection = $this->selector->select($connections);
            if ($connection->isAlive() === true) {
                return $connection;
            }

            if ($this->readyToRevive($connection) === true) {
                return $connection;
            }

            // > Remove the failed connection from the list of eligible connections.
            $connections = array_filter(
                $connections,
                function ($baseConnection) use ($connection) {
                    return $baseConnection !== $connection;
                }
            );
        }

        throw new NoNodesAvailableException('No alive nodes found in your cluster');
    }

    /**
     * > Same as parent private method.
     */
    protected function readyToRevive(Connection $connection): bool
    {
        $timeout = min(
            $this->pingTimeout * pow(2, $connection->getPingFailures()),
            $this->maxPingTimeout
        );

        if ($connection->getLastPing() + $timeout < time()) {
            return true;
        } else {
            return false;
        }
    }
}

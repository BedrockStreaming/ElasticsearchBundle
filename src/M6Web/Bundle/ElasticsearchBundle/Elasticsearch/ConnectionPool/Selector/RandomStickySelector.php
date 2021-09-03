<?php

namespace M6Web\Bundle\ElasticsearchBundle\Elasticsearch\ConnectionPool\Selector;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\ConnectionPool\Selectors\SelectorInterface;
use Elasticsearch\Connections\ConnectionInterface;

/**
 * Class RandomStickySelector
 *
 * @package M6Web\Bundle\ElasticsearchBundle\Elasticsearch\ConnectionPool\Selector
 */
class RandomStickySelector implements SelectorInterface
{
    protected $current;

    /**
     * Select a random connection from the provided array and stick with it.
     *
     * @param ConnectionInterface[] $connections Array of Connection objects
     *
     * @throws NoNodesAvailableException
     *
     * @return ConnectionInterface
     */
    public function select($connections): ConnectionInterface
    {
        if (empty($connections)) {
            throw new NoNodesAvailableException('No node to select from…');
        }

        if (($this->current === null) || !isset($connections[$this->current])) {
            $this->current = array_rand($connections);
        }

        return $connections[$this->current];
    }
}

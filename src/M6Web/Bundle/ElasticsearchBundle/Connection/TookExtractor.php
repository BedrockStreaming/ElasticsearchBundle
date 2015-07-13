<?php

namespace M6Web\Bundle\ElasticsearchBundle\Connection;

/**
 * TookExtractor
 */
trait TookExtractor
{
    /**
     * Extract took from response
     *
     * @param array $response
     *
     * @return int|null
     */
    protected function extractTookFromResponse(array $response)
    {
        if (isset($response['text'])) {
            $matches = array();
            if (preg_match('/\"took\":(\d+)/', $response['text'], $matches)) {

                return (int) $matches[1];
            }
        }

        return null;
    }
}

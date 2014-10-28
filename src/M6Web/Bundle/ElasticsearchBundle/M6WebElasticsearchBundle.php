<?php

namespace M6Web\Bundle\ElasticsearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class M6WebElasticsearchBundle
 *
 * @package M6Web\Bundle\ElasticsearchBundle
 */
class M6WebElasticsearchBundle extends Bundle
{
    /**
     * @return DependencyInjection\M6WebElasticsearchExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new DependencyInjection\M6WebElasticsearchExtension();
    }
}

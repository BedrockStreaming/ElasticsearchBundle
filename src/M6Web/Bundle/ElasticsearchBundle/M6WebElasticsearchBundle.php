<?php

namespace M6Web\Bundle\ElasticsearchBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class M6WebElasticsearchBundle
 */
class M6WebElasticsearchBundle extends Bundle
{
    /**
     * @return DependencyInjection\M6WebElasticsearchExtension|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface|null
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DependencyInjection\M6WebElasticsearchExtension();
    }
}

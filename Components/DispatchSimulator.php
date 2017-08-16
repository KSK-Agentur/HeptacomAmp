<?php

namespace HeptacomAmp\Components;

use Exception;
use Shopware\Kernel;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

/**
 * Class DispatchSimulator
 * @package HeptacomAmp\Components
 */
class DispatchSimulator
{
    /**
     * @var Client
     */
    private $client;

    /**
     * DispatchSimulator constructor.
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->client = new Client($kernel);
    }

    /**
     * @param Shop $shop
     * @param array $params
     * @return Crawler
     * @throws Exception
     */
    public function request(Shop $shop, array $params = [])
    {
        if ($shop === null) {
            throw new Exception('Shop cannot be null.');
        }

        $uri = implode([
            'https://',
            ($shop->getSecureHost()) ? $shop->getSecureHost() : $shop->getHost(),
            ($shop->getSecureBasePath()) ? $shop->getSecureBasePath() : $shop->getBasePath(),
            ($shop->getSecureBaseUrl()) ? $shop->getSecureBaseUrl() : $shop->getBaseUrl(),
        ]);

        $params = array_merge([
            'module' => 'frontend',
            'controller' => 'index',
            'action' => 'index',
            'amp' => 1,
        ], $params);

        return $this->client->request('GET', $uri, $params, [], ['HTTPS' => true]);
    }
}
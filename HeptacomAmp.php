<?php declare(strict_types=1);

namespace HeptacomAmp;

use Enlight_Controller_Request_Request;
use Enlight_Event_EventArgs;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

class HeptacomAmp extends Plugin
{
    const PLUGIN_NAME = 'HeptacomAmp';

    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_RouteShutdown' => 'autoloadComposer',
        ];
    }

    public function autoloadComposer(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Request_Request $request */
        $request = $args->get('request');

        if ($request->getParam('amp', 0)) {
            require_once implode(DIRECTORY_SEPARATOR, [$this->getPath(), 'vendor', 'autoload.php']);
        }
    }
}

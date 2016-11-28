<?php

namespace Shopware\HeptacomAmp\Subscriber;

use Enlight\Event\SubscriberInterface;

class Detail implements SubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onFrontendDetailPostDispatch',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_HeptacomAmpDetail' => 'onGetControllerPathFrontend',
        );
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFrontendDetailPostDispatch(\Enlight_Event_EventArgs $args)
    {
        $request = $args->getRequest();
        $controller = $args->getSubject();
        $view = $controller->View();

        $view->addTemplateDir(__DIR__ . '/../Views');

        if ($request->getParam('amp') == 1) {
            $sArticle = (int) $request->get('sArticle');

            $controller->redirect([
                'controller' => 'heptacomAmpDetail',
                'action' => 'index',
                'sArticle' => $sArticle,
            ]);
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathFrontend(\Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Frontend/HeptacomAmpDetail.php';
    }
}

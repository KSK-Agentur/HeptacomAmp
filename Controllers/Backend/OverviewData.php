<?php

use HeptacomAmp\Factory\UrlFactory;
use HeptacomAmp\Services\Searcher\UrlSearcher;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Logger;
use Shopware\Components\Routing\Router;
use Shopware\Models\Category\Category;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

/**
 * Class Shopware_Controllers_Backend_HeptacomAmpOverviewData
 */
class Shopware_Controllers_Backend_HeptacomAmpOverviewData extends Shopware_Controllers_Api_Rest implements CSRFWhitelistAware
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var UrlSearcher
     */
    private $urlSearcher;

    /**
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'getShops',
            'getCategories',
            'getArticles',
            'getCategories',
            'getCustoms',
        ];
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $this->urlFactory = $this->container->get('heptacom_amp.factory.url');
        $this->urlSearcher = $this->container->get('heptacom_amp.services.searcher.url');
    }

    /**
     * @return ShopRepository
     */
    private function getShopRepository()
    {
        return $this->container->get('models')->getRepository(Shop::class);
    }

    /**
     * @return CategoryRepository
     */
    private function getCategoryRepository()
    {
        return $this->container->get('models')->getRepository(Category::class);
    }

    /**
     * @param Shop $shop
     * @return array
     */
    private function getDiscoverableCategories(Shop $shop)
    {
        $sql = <<<EOL
SELECT DISTINCT categories.id, categories.description name
FROM s_core_shops shops
INNER JOIN s_categories categories ON categories.path LIKE concat('%|',  shops.category_id, '|%')
INNER JOIN s_articles_categories article_categories ON article_categories.categoryID = categories.id
INNER JOIN s_articles articles ON articles.id = article_categories.articleID
INNER JOIN s_articles_details details ON details.id = articles.main_detail_id
WHERE articles.active = 1
AND details.ordernumber IS NOT NULL AND TRIM(details.ordernumber) <> ''
AND shops.id = :shopId;
EOL;

        try {
            $categories = $this->getModelManager()->getConnection()->executeQuery($sql, ['shopId' => $shop->getId()])->fetchAll();

            foreach ($categories as &$category) {
                $category = [
                    'id' => (int) $category['id'],
                    'name' => (string) $category['name'],
                ];
            }

            return $categories;
        } catch (Exception $exception) {
            /** @var Logger $logger */
            $logger = $this->get('pluginlogger');
            $logger->error($exception->getMessage());

            return [];
        }
    }

    /**
     * @param Category $category
     * @return array|bool|mixed
     */
    private function getDiscoverableArticles(Shop $shop, Category $category)
    {
        try {
            /** @var ContextServiceInterface $shopService */
            $shopService = $this->container->get('shopware_storefront.context_service');
            $context = $shopService->createShopContext($shop->getId());
            /** @var StoreFrontCriteriaFactoryInterface $criteriaFactory */
            $criteriaFactory = $this->container->get('shopware_search.store_front_criteria_factory');
            $criteria = $criteriaFactory->createBaseCriteria([$category->getId()], $context);
            /** @var ProductSearchInterface $productSearch */
            $productSearch = $this->container->get('shopware_search.product_search');

            return array_map(function (ListProduct $listProduct) {
                return [
                    'id' => $listProduct->getId(),
                    'name' => $listProduct->getName(),
                ];
            }, $productSearch->search($criteria, $context)->getProducts());
        } catch (Exception $exception) {
            /** @var Logger $logger */
            $logger = $this->get('pluginlogger');
            $logger->error($exception->getMessage());

            return [];
        }
    }

    /**
     * Callable via /backend/HeptacomAmpOverviewData/getShops
     */
    public function getShopsAction()
    {
        if (empty($shops = array_map([$this->urlFactory, 'dehydrate'], $this->urlSearcher->findShops()))) {
            $this->View()->assign(['success' => false, 'data' => []]);
        } else {
            $this->View()->assign(['success' => true, 'data' => $shops]);
        }
    }

    /**
     * Callable via /backend/HeptacomAmpOverviewData/getCategories
     */
    public function getCategoriesAction()
    {
        /** @var Shop $shop */
        if (($shop = $this->getShopRepository()->find($this->Request()->getParam('shop'))) === null) {
            $this->View()->assign(['success' => false, 'data' => []]);
        } else {
            $this->View()->assign(['success' => true, 'data' => $this->getDiscoverableCategories($shop)]);
        }
    }

    /**
     * Callable via /backend/HeptacomAmpOverviewData/getArticles
     */
    public function getArticlesAction()
    {
        /** @var Shop $shop */
        $shop = $this->getShopRepository()->find($this->Request()->getParam('shop'));
        /** @var Category $category */
        $category = $this->getCategoryRepository()->find($this->Request()->getParam('category'));

        $result = [];
        $articles = $this->getDiscoverableArticles($shop, $category);

        foreach ($articles as &$article) {
            $result[] = [
                'name' => $article['name'],
                'test_url' => $this->urlFactory->getUrl($shop, 'frontend', 'detail', 'index', ['sArticle' => $article['id'], 'amp' => 1]),
                'urls' => [
                    'mobile' => $this->urlFactory->getUrl($shop, 'frontend', 'detail', 'index', ['sArticle' => $article['id'], 'amp' => 1]),
                    'desktop' => $this->urlFactory->getUrl($shop, 'frontend', 'detail', 'index', ['sArticle' => $article['id']])
                ],
            ];
        }

        if (empty($result)) {
            $this->View()->assign(['success' => false, 'data' => []]);
        } else {
            $this->View()->assign(['success' => true, 'data' => $result]);
        }
    }

    /// TODO undo 4037b1c9780ed0faaa18192ee4157bf203981bd4 for customs and categories in cache warming
}

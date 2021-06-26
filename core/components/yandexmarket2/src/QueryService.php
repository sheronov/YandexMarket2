<?php

namespace YandexMarket;

use Exception;
use Generator;
use modResource;
use modX;
use PDO;
use xPDOQuery;
use YandexMarket\Handlers\xPDOLazyIterator;
use YandexMarket\Models\Category;
use YandexMarket\Models\Field;
use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;
use YandexMarket\Queries\CategoriesQuery;
use YandexMarket\Queries\OffersQuery;
use YandexMarket\Xml\FileGenerator;
use ymCategory;

/**
 * Вспомогательный класс для подготовки запросов и генераторов предложений и категорий
 *
 * @package YandexMarket
 */
class QueryService
{
    protected $pricelist;
    protected $modx;

    //config
    protected $reduceQueries = false;

    protected $offersQuery;
    protected $categoriesQuery;

    public function __construct(Pricelist $pricelist, modX $modX)
    {
        $this->pricelist = $pricelist;
        $this->modx = $modX;

        $this->offersQuery = new OffersQuery($pricelist, $modX);
        $this->categoriesQuery = new CategoriesQuery($pricelist, $modX);

        $this->reduceQueries = $this->modx->getOption('yandexmarket2_reduce_queries', null, false);
    }

    public function getOffersAlias(): string
    {
        return $this->offersQuery->getAlias();
    }

    public function getCategoriesAlias(): string
    {
        return $this->categoriesQuery->getAlias();
    }

    public function getOffersCount(): int
    {
        $countQuery = clone $this->getOffersQuery();
        $countQuery->query['limit'] = '';
        $countQuery->query['offset'] = '';
        $countQuery->query['orderby'] = [];
        $countQuery->query['sortby'] = [];
        return $this->modx->getCount($countQuery->getClass(), $countQuery);
    }

    public function getCategoriesCount(): int
    {
        $countQuery = clone $this->getCategoriesQuery();
        $countQuery->query['limit'] = '';
        $countQuery->query['offset'] = '';
        $countQuery->query['orderby'] = [];
        $countQuery->query['sortby'] = [];
        return $this->modx->getCount($countQuery->getClass(), $countQuery);
    }

    public function isOffersPluginPrepared(): bool
    {
        return $this->offersQuery->hasPlugins();
    }

    public function isCategoriesPluginPrepared(): bool
    {
        return $this->categoriesQuery->hasPlugins();
    }

    public function getPricelist(): Pricelist
    {
        return $this->pricelist;
    }

    public function getModx(): modX
    {
        return $this->modx;
    }

    public function setOffersOrder(string $by, string $dir = 'ASC')
    {
        $this->offersQuery->setOrder($by, $dir);
    }

    public function setCategoriesOrder(string $by, string $dir = 'ASC')
    {
        $this->categoriesQuery->setOrder($by, $dir);
    }

    public function setOffersLimit(int $limit = 0, int $offset = 0)
    {
        $this->offersQuery->setLimit($limit, $offset);
    }

    public function setCategoriesLimit(int $limit = 0, int $offset = 0)
    {
        $this->categoriesQuery->setLimit($limit, $offset);
    }

    public function offersHaveCodeHandler(): bool
    {
        return $this->offersQuery->hasCodeHandlers();
    }

    public function categoriesHaveCodeHandler(): bool
    {
        return $this->categoriesQuery->hasCodeHandlers();
    }

    /**
     * Уведомляем прайслист, что ресурс обновился
     * TODO: когда-нибудь можно добавить проверку на изменение категорий
     *
     * @param  modResource  $resource
     */
    public function handleResourceChanges(modResource $resource)
    {
        $q = $this->getOffersQuery();
        $q->where([$q->getAlias().'.id' => $resource->id]);
        if (!$this->modx->getCount($q->getClass(), $this->pricelist->id)) {
            //предложения нет в прайс-листе, пропускаем
            return;
        }

        if ($this->pricelist->generate_mode === Pricelist::GENERATE_MODE_AFTER_SAVE) {
            $generator = new FileGenerator($this);
            try {
                $generator->makeFile();
            } catch (Exception $e) {
                // не удалось сгенерировать файл
                $this->modx->log(modX::LOG_LEVEL_ERROR, $e->getMessage(), '', 'YandexMarket2');
                $this->pricelist->touch();
            }
        } else {
            $this->pricelist->touch();
        }
    }

    /**
     * Получение поля по его типу (root, offer и т.д)
     *
     * @param  int  $type
     *
     * @return Field|null
     */
    public function getFieldByType(int $type)
    {
        foreach ($this->pricelist->getFields(true) as $field) {
            if ($field->type === $type) {
                return $field;
            }
        }
        return null;
    }

    /**
     * @return Generator|Category[]
     */
    public function categoriesGenerator(): Generator
    {
        $query = $this->getCategoriesQuery();

        if ($this->modx->getOption('yandexmarket2_debug_mode')) {
            $query->prepare();
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Categories SQL: '.$query->toSQL(true));
        }

        $resources = $this->modx->getIterator($query->getClass(), $query);
        /** @var modResource $resource */
        foreach ($resources as $resource) {
            $ymCategory = new ymCategory($this->modx);
            $ymCategory->set('id', $resource->get('category_id') ?: $resource->id);
            $ymCategory->set('name', $resource->get('category_name') ?: $resource->pagetitle);
            $ymCategory->set('properties', $resource->get('category_properties') ?: []);
            $ymCategory->set('resource_id', $resource->id);
            $ymCategory->set('pricelist_id', $this->pricelist->id);

            $category = new Category($this->modx, $ymCategory);
            $category->setResource($resource);
            yield $category;
        }
    }

    protected function getCategoriesQuery(): xPDOQuery
    {
        $q = $this->categoriesQuery->getQuery();

        if (empty($q->query['where'])) {
            //если в категории ничего не выбрано и не добавлено условий через плагин,
            $this->categoriesQuery->setOffersQuery($this->offersQuery->getQuery());

            $this->addUnlistedCategoriesToQuery($q); //добавляем пропущенные родительские категории
        }

        return $q;
    }

    protected function addUnlistedCategoriesToQuery(xPDOQuery $query)
    {
        //значит условие только от товаров и категории следует добрать
        $addedQuery = clone $query;
        $addedQuery->query['columns'] = '';
        $addedQuery->select($this->modx->getSelectColumns($query->getClass(), $query->getAlias(), '',
            ['context_key', 'id', 'parent'])); //не рассматриваем, что родитель в другом контексте
        $tstart = microtime(true);
        if ($addedQuery->prepare() && $addedQuery->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;

            $neededToAdd = [];
            $contextResources = $addedQuery->stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            foreach ($contextResources as $context => $resources) {
                $ids = array_column($resources, 'id');
                $parents = array_unique(array_column($resources, 'parent'));
                $unlistedCategories = array_diff($parents, $ids);
                if (!empty($unlistedCategories)) {
                    foreach ($unlistedCategories as $category) {
                        $neededToAdd[] = (int)$category;
                        $categoryParents = $this->modx->getParentIds($category, 10, ['context' => $context]);
                        foreach ($categoryParents as $categoryParent) {
                            if ($categoryParent && !in_array($categoryParent, $neededToAdd, true)) {
                                $neededToAdd[] = $categoryParent;
                            }
                        }
                    }
                }
            }

            if (!empty($neededToAdd)) {
                $query->where(['OR:'.$query->getAlias().'.id:IN' => array_unique($neededToAdd)]);
                $this->modx->log(modX::LOG_LEVEL_INFO,
                    sprintf('К запросу категорий добавлены недостающие категории: %s',
                        implode(',', $neededToAdd)), '', 'YandexMarket2');
            }
        }
    }

    /**
     * Генератор предложений для дальнейшей итерации
     *
     * @return Offer[]|Generator
     */
    public function offersGenerator(): Generator
    {
        $query = $this->getOffersQuery();

        if ($this->modx->getOption('yandexmarket2_debug_mode')) {
            $query->prepare();
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Offers SQL: '.$query->toSQL(true));
        }

        if ($this->reduceQueries) {
            $offers = new xPDOLazyIterator($this->modx, [
                'criteria'  => $query,
                'class'     => $query->getClass(),
                'cacheFlag' => true,
            ]);
        } else {
            $offers = $this->modx->getIterator($query->getClass(), $query);
        }

        foreach ($offers as $offer) {
            yield new Offer($this->modx, $offer);
        }
    }

    protected function getOffersQuery(): xPDOQuery
    {
        $categoriesQuery = $this->getCategoriesQuery();
        if (!$this->categoriesQuery->isUsesOtherQuery()) {
            $this->offersQuery->setCategoriesQuery($categoriesQuery);
        }
        return $this->offersQuery->getQuery();
    }

}
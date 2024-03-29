<?php

namespace YandexMarket\Processors\Categories;

use MODX\Revolution\modContext;
use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Resource\GetNodes;
use PDO;
use YandexMarket\Model\YmCategory;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    require_once MODX_CORE_PATH.'model/modx/processors/resource/getnodes.class.php';

    abstract class AGetList extends \modResourceGetNodesProcessor
    {
        public $classKey = \YmCategory::class;

        public function prepareContextNode(\modContext $context)
        {
            return static::prepareNodeContext(parent::prepareContextNode($context));
        }

        public function prepareResourceNode(\modResource $resource)
        {
            return static::prepareNodeResource(parent::prepareResourceNode($resource), $resource);
        }

    }
} else {
    abstract class AGetList extends GetNodes
    {
        public $classKey = YmCategory::class;

        public function prepareContextNode(modContext $context)
        {
            return static::prepareNodeContext(parent::prepareContextNode($context));
        }

        public function prepareResourceNode(modResource $resource)
        {
            return static::prepareNodeResource(parent::prepareResourceNode($resource), $resource);
        }

    }
}


class GetList extends AGetList
{
    /**
     * @var mixed|string
     */
    protected        $categories   = [];
    protected        $neededToOpen = [];

    public function initialize(): bool
    {
        $initialized = parent::initialize();
        $this->categories = $this->getSelectedCategories();
        return $initialized;
    }

    public function prepare()
    {
        $this->permissions = [];

        if (!empty($this->categories) && !empty($this->contextKey) && $this->contextKey !== 'root') {
            foreach ($this->categories as $category) {
                $parents = $this->modx->getParentIds($category, 10, ['context' => $this->contextKey]);
                $parents[] = $category;
                foreach ($parents as $parent) {
                    $this->neededToOpen[$parent] = true;
                }
            }
        }
    }

    public function getResourceQuery()
    {
        $c = parent::getResourceQuery();
        $c->select([
            'childrenCount' => "(SELECT COUNT(*) FROM {$this->modx->getTableName('modResource')}"
                ." WHERE `parent` = `modResource`.`id` and `isfolder` = 1)",
        ]);
        $c->where(['isfolder' => true]);

        return $c;
    }

    public function prepareNodeContext(array $node): array
    {
        $node['selectable'] = false;
        unset($node['settings']);
        $node['children'] = [];
        $node['expanded'] = false;

        if (!empty($this->categories)) {
            $children = $this->modx->getChildIds(0, 1, ['context' => $node['pk']]);
            foreach ($this->categories as $category) {
                $parents = $this->modx->getParentIds($category, 10, ['context' => $node['pk']]);
                $parents[] = $category;
                if (array_intersect($children, $parents)) {
                    $node['expanded'] = true;
                    break;
                }
            }
        }

        return $node;
    }

    /**
     * @param  array  $node
     * @param modResource|\modResource $resource
     *
     * @return array
     */
    public function prepareNodeResource(array $node, $resource): array
    {
        unset($node['menu'], $node['allowDrop']);

        $node['selected'] = in_array($resource->id, $this->categories, true);

        $node['expanded'] = false;
        $node['selectable'] = true;
        $node['childCount'] = (int)($node['childCount'] ?? 0);
        $node['hasChildren'] = $node['childCount'] > 0;
        if ($node['hasChildren']) {
            $node['children'] = [];
        } else {
            unset($node['children']);
        }

        if (!empty($this->neededToOpen) && isset($this->neededToOpen[$resource->id]) && $node['hasChildren']) {
            $node['expanded'] = true;
        }

        return $node;
    }

    protected function getSelectedCategories(): array
    {
        $categories = [];
        $stmt = $this->modx->newQuery($this->classKey)
            ->where(['pricelist_id' => $this->getProperty('pricelist_id')])
            ->select($this->modx->getSelectColumns($this->classKey, 'YmCategory', '', ['resource_id']))
            ->prepare();

        if ($stmt && $stmt->execute()) {
            while ($resourceId = $stmt->fetch(PDO::FETCH_COLUMN)) {
                $categories[] = (int)$resourceId;
            }
        }

        return $categories;
    }

}

<?php

require_once MODX_CORE_PATH.'model/modx/processors/resource/getnodes.class.php';

class YandexMarket2GetCategoriesProcessor extends modResourceGetNodesProcessor
{

    protected $categories   = [9, 22, 4, 126];
    protected $neededToOpen = [];

    public function initialize(): bool
    {
        return parent::initialize();
    }

    public function prepare(): void
    {
        $this->permissions = [];

        if (!empty($this->categories) && !empty($this->contextKey) && $this->contextKey !== 'root') {
            foreach ($this->categories as $category) {
                $parents = $this->modx->getParentIds($category, 10, ['context' => $this->contextKey]);
                foreach ($parents as $parent) {
                    $this->neededToOpen[$parent] = true;
                }
            }
        }
    }

    public function getResourceQuery(): xPDOQuery
    {
        $c = parent::getResourceQuery();
        $c->select([
            'childrenCount' => "(SELECT COUNT(*) FROM {$this->modx->getTableName('modResource')} WHERE parent = modResource.id and isfolder = 1)",
        ]);
        $c->where(['isfolder' => true]);

        return $c;
    }

    public function prepareContextNode(modContext $context): array
    {
        $node = parent::prepareContextNode($context);
        $node['selectable'] = false;
        unset($node['settings']);
        $node['children'] = [];
        $node['expanded'] = false;

        if (!empty($this->categories)) {
            $children = $this->modx->getChildIds(0, 1, ['context' => $node['pk']]);
            foreach ($this->categories as $category) {
                if (array_intersect($children, $this->modx->getParentIds($category, 10, ['context' => $node['pk']]))) {
                    $node['expanded'] = true;
                    break;
                }
            }
        }

        return $node;
    }

    public function prepareResourceNode(modResource $resource): array
    {
        $node = parent::prepareResourceNode($resource);

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

        if (!empty($this->neededToOpen)) {
            $this->modx->log(1, 'opened '.print_r($this->neededToOpen, 1));
            // $parents = $this->modx->getParentIds($node['pk'], 1, ['context' => $node['ctx']]);
            // foreach ($parents as $parent) {
            if (isset($this->neededToOpen[$resource->id])) {
                $node['expanded'] = true;
                // break;
            }
            // }
            // if (array_intersect($parents, array_keys($this->neededToOpen))) {
            //     $node['expanded'] = true;
            // }
        }

        return $node;
    }

}

return YandexMarket2GetCategoriesProcessor::class;
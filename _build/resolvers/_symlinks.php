<?php
/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */
/** @var  MODX\Revolution\modX $modx */

if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/YandexMarket2/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/yandexmarket2')) {
            $cache->deleteTree(
                $dev . 'assets/components/yandexmarket2/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/yandexmarket2/', $dev . 'assets/components/yandexmarket2');
        }
        if (!is_link($dev . 'core/components/yandexmarket2')) {
            $cache->deleteTree(
                $dev . 'core/components/yandexmarket2/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/yandexmarket2/', $dev . 'core/components/yandexmarket2');
        }
    }
}

return true;

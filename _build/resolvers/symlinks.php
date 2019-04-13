<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/msYaMarket/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/msyamarket')) {
            $cache->deleteTree(
                $dev . 'assets/components/msyamarket/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/msyamarket/', $dev . 'assets/components/msyamarket');
        }
        if (!is_link($dev . 'core/components/msyamarket')) {
            $cache->deleteTree(
                $dev . 'core/components/msyamarket/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/msyamarket/', $dev . 'core/components/msyamarket');
        }
    }
}

return true;
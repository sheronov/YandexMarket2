<?php

namespace YandexMarket\Tests;

use modX;
use PHPUnit\Framework\TestCase;

/**
 * Extends the basic PHPUnit TestCase class to provide MODX specific methods
 *
 * @package modx-test
 */
abstract class MODxTestCase extends TestCase
{
    /**
     * @var modX $modx
     */
    public $modx = null;
    /**
     * @var bool
     */
    public $debug = false;

    public $path;

    /**
     * Ensure all tests have a reference to the MODX object
     */
    public function setUp()
    {
        $this->modx =& MODxTestHarness::getFixture('modX', 'modx');
        if ($this->modx->request) {
            $this->modx->request->loadErrorHandler();
            $this->modx->error->reset();
        }
        /* setup some basic test-environment options to allow us to simulate a site */
        $this->modx->setOption('http_host', 's9767.h8.modhost.pro');
        $this->modx->setOption('base_url', '/');
        $this->modx->setOption('site_url', 'http://s9767.h8.modhost.pro/');

        // $corePath = $this->modx->getOption('base_path').'extras/minishop/core/components/minishop2/';
        // require_once $corePath.'model/minishop2/minishop2.class.php';
        // $this->modx->setOption('minishop2.assets_path',
        //     $this->modx->getOption('base_path').'extras/minishop/assets/components/minishop2/');
        // $this->modx->setOption('minishop2.assets_url',
        //     $this->modx->getOption('base_url').'extras/minishop/assets/components/minishop2/');
        // $this->modx->miniShop2 = new miniShop2($this->modx);
        //
        // $this->modx->lexicon->load('minishop2:default');

        // $this->path = [
        //     'processors_path' => $this->modx->getOption('processorsPath', $this->modx->miniShop2->config,
        //         $corePath.'processors/')
        // ];
    }

    /**
     * Remove reference at end of test case
     */
    public function tearDown()
    {
    }

    /**
     * Check a MODX return result for a success flag
     *
     * @param  \modProcessorResponse  $result  The result response
     *
     * @return boolean
     */
    public function checkForSuccess(&$result)
    {
        if (empty($result) || !($result instanceof \modProcessorResponse)) {
            return false;
        }
        return !$result->isError();
    }

    /**
     * Check a MODX processor response and return results
     *
     * @param  object  $result  The response
     *
     * @return array
     */
    public function getResults(&$result)
    {
        $response = ltrim(rtrim($result->response, ')'), '(');
        $response = json_decode($response, true);
        return !empty($response['results']) ? $response['results'] : [];
    }
}

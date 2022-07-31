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

        $this->modx->setOption('cultureKey', 'ru');
        $this->modx->lexicon->load('yandexmarket2:default');
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

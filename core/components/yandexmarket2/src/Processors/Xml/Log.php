<?php

namespace YandexMarket\Processors\Xml;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\Registry\modFileRegister;
use MODX\Revolution\Registry\modRegistry;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class ALogProcessor extends \modProcessor { }
} else {
    abstract class ALogProcessor extends Processor { }
}

class Log extends ALogProcessor
{
    public function initialize()
    {
        $this->setProperty('register', 'yandexmarket2');
        $topic = $this->getProperty('topic');
        if (empty($topic)) {
            return $this->modx->lexicon('error');
        }
        return true;
    }

    public function process()
    {
        $register = trim($this->getProperty('register'));
        $registerClass = trim($this->getProperty('register_class', Service::isMODX3() ? modFileRegister::class : 'registry.modFileRegister'));
        $topic = trim($this->getProperty('topic'));

        $options = [
            'poll_limit'    => $this->getProperty('poll_limit', 1),
            'poll_interval' => $this->getProperty('poll_interval', 1),
            'time_limit'    => $this->getProperty('time_limit', 10),
            'msg_limit'     => $this->getProperty('message_limit', 200),
            'remove_read'   => true,
        ];

        $this->modx->getService('registry', Service::isMODX3() ? modRegistry::class : 'registry.modRegistry');
        $this->modx->registry->addRegister($register, $registerClass, ['directory' => $register]);
        if (!$this->modx->registry->$register->connect()) {
            return $this->failure($this->modx->lexicon('error'));
        }
        $this->modx->registry->$register->subscribe($topic);

        $messages = $this->modx->registry->$register->read($options);
        $response = [
            'data'     => '',
            'complete' => false
        ];
        if (!empty($messages)) {
            foreach ($messages as $message) {
                if ($message['msg'] === 'COMPLETED') {
                    $response['complete'] = true;
                    continue;
                }

                if (!$this->modx->getOption('yandexmarket2_debug_mode', null, false) && $message['level'] === 'DEBUG') {
                    continue;
                }

                $filePath = '';
                if (mb_strpos($message['file'] ?? '', 'yandexmarket2') === false) {
                    if (!empty($message['file'])) {
                        $filePath .= str_replace([MODX_CORE_PATH.'components/', MODX_CORE_PATH], '', $message['file']);
                    }
                    if (!empty($message['line'])) {
                        $filePath .= ' line '.$message['line'];
                    }
                }

                $response['data'] .= '<span class="'.strtolower($message['level']).'">';
                if (!empty($filePath)) {
                    $response['data'] .= '<small>'.$filePath.'</small> ';
                }
                $response['data'] .= $message['msg']."</span><br />\n";
            }
        }
        return $this->modx->toJSON($response);
    }
}

<?php

namespace YandexMarket\Processors\Xml;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\Registry\modRegistry;

class Log extends Processor
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
        $registerClass = trim($this->getProperty('register_class', 'registry.modFileRegister'));
        $topic = trim($this->getProperty('topic'));

        $options = [
            'poll_limit'    => $this->getProperty('poll_limit', 1),
            'poll_interval' => $this->getProperty('poll_interval', 1),
            'time_limit'    => $this->getProperty('time_limit', 10),
            'msg_limit'     => $this->getProperty('message_limit', 200),
            'remove_read'   => true,
        ];

        $this->modx->getService('registry', modRegistry::class);
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

                if (!$this->modx->getOption('yandexmarket2_debug_mode', null, false) && $message['level'] === 'WARN') {
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

<?php

namespace App\Sagas;

class RegisterUserSaga
{
    public function kafkaInit()
    {
        $conf = new \RdKafka\Conf();
        $conf->set('log_level', (string) LOG_DEBUG);
        $conf->set('debug', 'all');
        $rk = new \RdKafka\Consumer($conf);
        $rk->addBrokers("kafkaservice");
    }
}

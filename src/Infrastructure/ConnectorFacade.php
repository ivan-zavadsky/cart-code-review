<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Infrastructure;

use Exception;
use Redis;
use RedisException;

class ConnectorFacade
{
    public string $host;
    public int $port = 6379;
    public ?string $password = null;
    public ?int $dbindex = null;

    public $connector;

    public function __construct($host, $port, $password, $dbindex)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->dbindex = $dbindex;
    }

    protected function build(): void
    {
        $redis = new Redis();

        try {
            $isConnected = $redis->isConnected();
            if (! $isConnected && $redis->ping('Pong')) {
                $isConnected = $redis->connect(
                    $this->host,
                    $this->port,
                );
            }
        } catch (RedisException) {
            //todo: Выбросить исключение, что сервис недоступен
            throw new Exception('Redis connection failed');
        }

        if (
            $isConnected
            && $redis->auth($this->password)
            && $redis->select($this->dbindex)
        ) {
            //todo: нижеследующие 2 условия тоже обернуть в if,
            // при невыполнении одного из них выбросить соответствующее
            // исключение
            $this->connector = new Connector($redis);
        } else {
            throw new Exception('Redis authentication failed '
            . 'or dbindex does not exist');
        }
    }
}

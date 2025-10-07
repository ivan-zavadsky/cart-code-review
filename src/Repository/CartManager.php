<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Exception;
use Psr\Log\LoggerInterface;
use Raketa\BackendTestTask\Domain\Cart;
use Raketa\BackendTestTask\Infrastructure\ConnectorFacade;

class CartManager extends ConnectorFacade
{
    public $logger;

    public function __construct($host, $port, $password)
    {
        parent::__construct($host, $port, $password, 1);
        parent::build();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function saveCart(Cart $cart)
    {

        //todo: 1. Проверить, была ли создана сессия. Если нет, создать
        // 2. Перепутан порядок аргументов. Поправить
        // 3. Придумать и реализовать алгоритм для имени ключа.
        // Как минимум "cart_key:" . session_id()
        try {
            switch (session_status()) {
                case PHP_SESSION_NONE:
                    session_start();
                    break;
                case PHP_SESSION_DISABLED:
                    throw new Exception('Session is disabled');
            }
            $key = 'cart_key:' . session_id();
            $this->connector->set($key, $cart);
        } catch (Exception $e) {
            $this->logger->error('Error');
        }
    }

    /**
     * @return ?Cart
     */
    public function getCart()
    {
        try {
            return $this->connector->get(session_id());
        } catch (Exception $e) {
            $this->logger->error('Error');
        }

        /**
         * @var $customer - Получить из компонента фреймворка
         * @var $defaultPaymentMethod
         */
        //todo: Передать в конструктор минимально необходимые параметры
        return new Cart(
            session_id(),
            $customer,
            $defaultPaymentMethod,
            []
        );
    }
}

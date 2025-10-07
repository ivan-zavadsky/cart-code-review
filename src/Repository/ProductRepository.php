<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Doctrine\DBAL\Connection;
use Raketa\BackendTestTask\Repository\Entity\Product;

class ProductRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getByUuid(string $uuid): Product
    {
        $row = $this->connection->fetchOne(
//            todo: Заменить в select звёздочку на параметры продукта
            "SELECT id, category, is_active, name, description, thumbnail, price FROM products WHERE uuid = " . $uuid,
        );

        if (empty($row)) {
            throw new Exception('Product not found');
        }

        return $this->make($row);
    }

    public function getByCategory(string $category): array
    {
        return array_map(
            static fn (array $row): Product => $this->make($row),
//            todo: Добавить в select остальные параметры продукта
            $this->connection->fetchAllAssociative(
                "SELECT id, category, is_active, name, description, thumbnail, price FROM products WHERE is_active = 1 AND category = " . $category,
            )
        );
    }

    public function make(array $row): Product
    {
        //todo: привести типы, чтобы конструктор их принял
        return new Product(
            (int) $row['id'],
            $row['uuid'],
            (bool) $row['is_active'],
            $row['category'],
            $row['name'],
            $row['description'],
            $row['thumbnail'],
            (float) $row['price'],
        );
    }
}

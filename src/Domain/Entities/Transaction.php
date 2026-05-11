<?php
declare(strict_types=1);
namespace FinancePlugin\Domain\Entities;

readonly class Transaction {
    public function __construct(
        public float $amount,
        public int $categoryId,
        public string $type,
        public string $date,
        public ?string $description = null,
        public ?int $id = null
    ) {}
}

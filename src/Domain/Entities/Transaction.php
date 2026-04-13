<?php
namespace FinancePlugin\Domain\Entities;

readonly class Transaction {
    public function __construct(
        public float $amount,
        public int $categoryId,
        public string $type, // 'income' ή 'expense'
        public string $date,
        public ?string $description = null,
        public ?int $id = null
    ) {}
}
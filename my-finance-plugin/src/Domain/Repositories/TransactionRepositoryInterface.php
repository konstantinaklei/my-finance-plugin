<?php
namespace FinancePlugin\Domain\Repositories;

use FinancePlugin\Domain\Entities\Transaction;

interface TransactionRepositoryInterface {
    public function save(Transaction $transaction): bool;
    public function getAll(): array;
}
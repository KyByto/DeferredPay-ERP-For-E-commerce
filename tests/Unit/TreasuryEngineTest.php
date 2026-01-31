<?php

namespace Tests\Unit;

use App\Models\Supplier;
use App\Models\FinancialTransaction;
use App\Services\TreasuryEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasuryEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_debt_to_supplier()
    {
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $engine = new TreasuryEngine();

        $transaction = $engine->addDebt($supplier, 1000, 'Purchase 101');

        $this->assertDatabaseHas('financial_transactions', [
            'type' => TreasuryEngine::TYPE_DEBT,
            'amount' => 1000,
            'source_type' => Supplier::class,
            'source_id' => $supplier->id,
            'destination_type' => TreasuryEngine::ACCOUNT_LIABILITY,
            'description' => 'Purchase 101',
        ]);
    }

    public function test_can_pay_supplier()
    {
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $engine = new TreasuryEngine();

        // Assume we have cash (skipping balance check for now as it is TODO)
        $transaction = $engine->paySupplier($supplier, 500);

        $this->assertDatabaseHas('financial_transactions', [
            'type' => TreasuryEngine::TYPE_PAYMENT,
            'amount' => 500,
            'source_type' => TreasuryEngine::ACCOUNT_CAISSE,
            'destination_type' => Supplier::class,
            'destination_id' => $supplier->id,
        ]);
    }

    public function test_can_add_expense()
    {
        $engine = new TreasuryEngine();
        $engine->addExpense('Lunch', 200, 'Team lunch');

        $this->assertDatabaseHas('financial_transactions', [
            'type' => TreasuryEngine::TYPE_EXPENSE,
            'amount' => 200,
            'destination_type' => 'External',
            'description' => 'Lunch: Team lunch',
        ]);
    }

    public function test_can_register_transfer()
    {
        $engine = new TreasuryEngine();
        $engine->registerTransfer(TreasuryEngine::ACCOUNT_SOCIETE, TreasuryEngine::ACCOUNT_CAISSE, 5000);

        $this->assertDatabaseHas('financial_transactions', [
            'type' => TreasuryEngine::TYPE_TRANSFER,
            'amount' => 5000,
            'source_type' => TreasuryEngine::ACCOUNT_SOCIETE,
            'destination_type' => TreasuryEngine::ACCOUNT_CAISSE,
        ]);
    }
}
<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class TreasuryEngine
{
    public const TYPE_DEBT = 'debt';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_INCOME = 'income';
    public const TYPE_TRANSFER = 'transfer';

    public const ACCOUNT_CAISSE = 'Caisse';
    public const ACCOUNT_SOCIETE = 'Societe'; // Cash held by Delivery
    public const ACCOUNT_LIABILITY = 'Dettes'; // Virtual account for liabilities

    public function addDebt(Supplier $supplier, float $amount, string $description): FinancialTransaction
    {
        // Supplier gives value (credit), We (Liability) receive the liability
        return FinancialTransaction::create([
            'type' => self::TYPE_DEBT,
            'amount' => $amount,
            'source_type' => Supplier::class,
            'source_id' => $supplier->id,
            'destination_type' => self::ACCOUNT_LIABILITY, 
            'destination_id' => null,
            'description' => $description,
        ]);
    }

    public function paySupplier(Supplier $supplier, float $amount): FinancialTransaction
    {
        // Strict check: Cannot pay what we don't have.
        if ($this->getCashBalance() < $amount) {
            throw new \Exception("Solde de caisse insuffisant pour ce paiement.");
        }
        
        return FinancialTransaction::create([
            'type' => self::TYPE_PAYMENT,
            'amount' => $amount,
            'source_type' => self::ACCOUNT_CAISSE,
            'source_id' => null,
            'destination_type' => Supplier::class,
            'destination_id' => $supplier->id,
            'description' => "Paiement Fournisseur: {$supplier->name}",
        ]);
    }

    public function addDeliveryCollection(float $amount, string $description): FinancialTransaction
    {
        // Money collected by Delivery Service from customers.
        // Source: External (Customers), Destination: Societe Account.
        return FinancialTransaction::create([
            'type' => self::TYPE_INCOME, // Or specialized type
            'amount' => $amount,
            'source_type' => 'External',
            'source_id' => null,
            'destination_type' => self::ACCOUNT_SOCIETE,
            'destination_id' => null,
            'description' => "Encaissement Livraison: $description",
        ]);
    }

    public function addExpense(string $category, float $amount, string $description): FinancialTransaction
    {
        if ($this->getCashBalance() < $amount) {
            throw new \Exception("Solde de caisse insuffisant pour cette dépense.");
        }

        return FinancialTransaction::create([
            'type' => self::TYPE_EXPENSE,
            'amount' => $amount,
            'source_type' => self::ACCOUNT_CAISSE,
            'source_id' => null,
            'destination_type' => 'External',
            'destination_id' => null,
            'description' => "$category: $description",
        ]);
    }

    public function addIncome(string $sourceName, float $amount, string $description): FinancialTransaction
    {
        return FinancialTransaction::create([
            'type' => self::TYPE_INCOME,
            'amount' => $amount,
            'source_type' => 'External',
            'source_id' => null,
            'destination_type' => self::ACCOUNT_CAISSE,
            'destination_id' => null,
            'description' => "$sourceName: $description",
        ]);
    }

    public function registerTransfer(string $fromAccount, string $toAccount, float $amount): FinancialTransaction
    {
        // Check source balance
        $currentBalance = 0.0;
        if ($fromAccount === self::ACCOUNT_CAISSE) {
            $currentBalance = $this->getCashBalance();
        } elseif ($fromAccount === self::ACCOUNT_SOCIETE) {
            $currentBalance = $this->getSocieteBalance();
        }

        if ($currentBalance < $amount) {
            throw new \Exception("Solde insuffisant sur le compte $fromAccount.");
        }

        // Example: Societe -> Caisse (Relevé)
        return FinancialTransaction::create([
            'type' => self::TYPE_TRANSFER,
            'amount' => $amount,
            'source_type' => $fromAccount,
            'source_id' => null,
            'destination_type' => $toAccount,
            'destination_id' => null,
            'description' => "Transfert de $fromAccount vers $toAccount",
        ]);
    }

    public function getCashBalance(): float
    {
        $incoming = FinancialTransaction::where('destination_type', self::ACCOUNT_CAISSE)->sum('amount');
        $outgoing = FinancialTransaction::where('source_type', self::ACCOUNT_CAISSE)->sum('amount');
        
        return $incoming - $outgoing;
    }

    public function getSocieteBalance(): float
    {
        // Societe de Livraison
        // Incoming: Debts (We owe supplier, but where does the money come from? 
        // Wait, "Debt" transaction: Source=Supplier, Dest=Societe. 
        // This means Societe 'Received' value (goods).
        // But usually "Societe Balance" refers to Cash held by the Delivery Guy.
        // This implies "Money collected from customers".
        // The Debt logic I implemented (Source=Supplier, Dest=Societe) tracks what we owe.
        // It doesn't track Cash held by Societe.
        
        // Let's assume there are other transactions for "Delivery Collection".
        // For now, let's just implement the basic sum based on what we have.
        
        $incoming = FinancialTransaction::where('destination_type', self::ACCOUNT_SOCIETE)->sum('amount');
        $outgoing = FinancialTransaction::where('source_type', self::ACCOUNT_SOCIETE)->sum('amount');
        
        return $incoming - $outgoing;
    }
}

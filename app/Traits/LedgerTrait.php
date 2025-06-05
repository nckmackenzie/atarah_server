<?php

namespace App\Traits;

use App\Models\GlAccount;
use App\Models\Ledger;
use Carbon\Carbon;

trait LedgerTrait
{
    /**
     * Add a transaction to the ledger.
     *
     * @param string|\DateTime $transactionDate The date of the transaction.
     * @param string $account The account involved in the transaction.
     * @param string $parentAccount The parent account of the account involved.
     * @param float|null $debit The debit amount of the transaction.
     * @param float|null $credit The credit amount of the transaction.
     * @param int $accountType The type of the account.
     * @param string|null $description A description of the transaction.
     * @param string|null $reference A reference for the transaction.
     * @param string $transactionType The type of the transaction.
     * @param string|int $transactionId The ID of the transaction.
     * @param bool|null $isJournal Whether the transaction is a journal entry.
     * @param int|null $journalNo The journal number if the transaction is a journal entry.
     *
     * @return void
     */
    protected function addToLedger(
        string|\DateTime $transactionDate,
        string $account,
        string $parentAccount,
        ?float $debit,
        ?float $credit,
        int $accountType,
        ?string $description,
        ?string $reference,
        string $transactionType,
        string|int $transactionId,
        ?bool $isJournal = false,
        ?int $journalNo = null,
    ): void {

        try{
            Ledger::create([
                'transaction_date' => Carbon::parse($transactionDate)->format('Y-m-d'),
                'account' => strtolower(trim($account)),
                'parent_account' => strtolower(trim($parentAccount)),
                'debit' => ($debit ?? 0) * 100,
                'credit' => ($credit ?? 0) * 100,
                'account_type_id' => $accountType,
                'description' => $description,
                'reference' => $reference,
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId,
                'is_journal' => $isJournal ?? 0,
                'journal_no' => $journalNo
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Adds a transaction to the ledger with minimal required fields.
     *
     * @param string|\DateTime $transactionDate The date of the transaction.
     * @param string $account The name of the account.
     * @param float|null $debit The debit amount (optional).
     * @param float|null $credit The credit amount (optional).
     * @param string|null $description The description of the transaction (optional).
     * @param string|null $reference The reference for the transaction (optional).
     * @param string $transactionType The type of the transaction.
     * @param string|int $transactionId The ID of the transaction.
     * @param string $congregationId The ID of the congregation.
     * @param bool|null $isJournal Indicates if the transaction is a journal entry (optional).
     * @param int|null $journalNo The journal number (optional).
     *
     * @return void
     */
    protected function addToLedgerMin(
        string|\DateTime $transactionDate,
        string|int $accountIdentifier,
        ?float $debit,
        ?float $credit,
        ?string $description,
        ?string $reference,
        string $transactionType,
        string|int $transactionId,
        ?bool $isJournal = false,
        ?int $journalNo = null,
    ): void {

        try {
            // $glAccount = GlAccount::whereRaw('LOWER(name) = ?',[strtolower($account)])->first(['id','parent_id','account_type_id','is_subcategory']);
            $glAccount = is_numeric($accountIdentifier)
            ? GlAccount::findOrFail($accountIdentifier, ['id', 'name', 'parent_id', 'account_type_id', 'is_subcategory'])
            : GlAccount::whereRaw('LOWER(name) = ?', [strtolower($accountIdentifier)])
                ->firstOrFail(['id', 'name', 'parent_id', 'account_type_id', 'is_subcategory']);
            $parentAccount =  $glAccount->is_subcategory ? GlAccount::where('id',$glAccount->parent_id)->first(['name'])->name : $glAccount->name;

            Ledger::create([
                'transaction_date' => Carbon::parse($transactionDate)->format('Y-m-d'),
                'account' => strtolower(trim($glAccount->name)),
                'parent_account' => strtolower(trim($parentAccount)),
                'debit' => ($debit ?? 0) * 100,
                'credit' => ($credit ?? 0) * 100,
                'account_type_id' => $glAccount->account_type_id,
                'description' => $description,
                'reference' => $reference,
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId,
                'is_journal' => $isJournal ?? 0,
                'journal_no' => $journalNo,
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Deletes a record from the ledger based on the transaction type and transaction ID.
     *
     * @param string $transactionType The type of the transaction to be deleted.
     * @param string|int $transactionId The ID of the transaction to be deleted.
     * @return void
     */
    protected function deleteFromLedger(string $transactionType,string|int $transactionId)
    {
        Ledger::where('transaction_type',$transactionType)
                ->where('transaction_id',$transactionId)
                ->delete();
    }

    /**
     * Retrieve the details of a specific account.
     *
     * @param int $account The ID of the account to retrieve details for.
     * @return \Illuminate\Database\Eloquent\Model|null The account details including 'name' and 'account_type_id', or null if not found.
     */
    protected function accountDetails($account)
    {
        return GlAccount::where('id',$account)->first(['name','account_type_id']);
    }
}
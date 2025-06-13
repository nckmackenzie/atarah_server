<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JournalRequest;
use App\Models\GlAccount;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JournalRequest $request)
    {
        return $this->handleJournalEntry($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(!is_numeric($id)){
            return response()->json(['error' => 'Invalid journal number.'], 400);
        }
        $journal = Ledger::where('journal_no',$id)->where('is_journal',true)->get();
        if($journal->isEmpty()){
            return response()->json(['error' => 'Journal not found.'], 404);
        }

        $formattedJournal = [
            'transaction_date' => formatDate($journal[0]->transaction_date),
            'transaction_id' => $journal[0]->transaction_id,
            'journalNo' => $journal[0]->journal_no,
            'details' => $journal->map(function ($item) {
                $account = GlAccount::where('name',$item->account)->first();
                return [
                    'id' => $item->id,
                    'account' => [
                        'id' => $account->id,
                        'name' => $item->account,
                    ],                   
                    'debit' => $item->debit ,
                    'credit' => $item->credit ,
                    'description' => $item->description,
                ];
            }),
        ];
        return response()->json(['data' => $formattedJournal]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JournalRequest $request, string $id)
    {
        $journal = Ledger::where('transaction_type','journal entry')->where('transaction_id',$id)->first();
        if(!$journal) return response()->json(['error' => 'Resource not found'],404);
        return $this->handleJournalEntry($request,$journal->journal_no);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $journals = Ledger::where('transaction_type','journal entry')
                           ->where('transaction_id',$id);
                
        if(!$journals->exists()){
            return response()->json(['error' => 'No journal entries matches this request']);
        }

        $journalNo = $journals->first()->journal_no;

        Ledger::where('transaction_type','journal entry')
                           ->where('transaction_id',$id)
                           ->delete();
        
        return response()->json(['message' => 'Journal entry deleted successfully.'], 204);
    }

    public function fetchJournalNo()
    {
        
        return response()->json(['data' =>  $this->getJournalNo()]);
    }
    

    private function getJournalNo()
    {
        $lastNo = Ledger::latest('journal_no')->first();
        return $lastNo ? $lastNo->journal_no + 1 : 1;
    }

    private function handleJournalEntry(JournalRequest $request, string|null $journalNo=null)
    {
        $validated = $request->validated();
        $details = $validated['details'];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($details as $detail) {
            if (!isset($detail['debit']) && !isset($detail['credit'])) {
                return response()->json(['error' => 'Each detail must have either a debit or a credit value.'], 400);
            }

            if ((isset($detail['debit']) && $detail['debit'] > 0) && (isset($detail['credit']) && $detail['credit'] > 0)) {
                return response()->json(['error' => 'Each detail must have either a debit or a credit value, but not both.'], 400);
            }

            $totalDebit += $detail['debit'] ?? 0;
            $totalCredit += $detail['credit'] ?? 0;
        }

        if ($totalDebit !== $totalCredit) {
            return response()->json(['error' => 'Debit and Credit totals do not match.'], 400);
        }

        $journalNo = $journalNo ?? $this->getJournalNo();
        Ledger::where('journal_no', $journalNo)->where('is_journal',true)->delete();

        try {
           
           DB::transaction(function() use($validated,$details,$journalNo) {
               $transactionId = (string) \Illuminate\Support\Str::uuid();
               foreach ($details as $detail) {
                    $accountDetails = $this->getAccountDetails($detail['gl_account_id']);
                    Ledger::create([
                        'transaction_date' => formatDate($validated['transaction_date']),
                        'account' => $accountDetails['account_name'],
                        'parent_account' => $accountDetails['parent_account_name'],
                        'debit' => $detail['debit'] ? $detail['debit'] :  0,
                        'credit' => $detail['credit'] ? $detail['credit'] : 0,
                        'account_type_id' => $accountDetails['account_type_id'],
                        'description' => $detail['description'] ?? null,
                        'transaction_type' => 'journal entry',
                        'transaction_id' => $transactionId,
                        'is_journal' => true,
                        'journal_no' => $journalNo,
                    ]);
                }
            });

            return response()->json(['message' => 'Journal entry saved successfully.'], 201);

        } catch (\Exception $e) {
           Log::error($e->getMessage());
           return response()->json(['error' => 'There was a problem while performing your request.'],500);
        }
    }
    
    private function getAccountDetails(int $accountId)
    {
        $account = GlAccount::find($accountId,['name','account_type_id','parent_id']);
        $parentAccount = GlAccount::find($account->parent_id,['name']);
        return [
            'account_name' => $account->name,
            'parent_account_name' => $parentAccount->name,
            'account_type_id' => $account->account_type_id,
        ];
    }

}

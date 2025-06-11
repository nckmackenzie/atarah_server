<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expenses = Expense::with(['details', 'details.project:id,name', 'details.account:id,name'])
                    ->withSum('details as expense_total', 'amount');

         if ($search =$request->input('q')) {
            if ($search = $request->input('q')) {
                $expenses = $expenses->where(function ($query) use ($search) {
                    $query->where('payee', 'like', '%' . $search . '%')
                        ->orWhere('payment_reference', 'like', '%' . $search . '%')
                        ->orWhereHas('details', function ($q) use ($search) {
                            $q->where('description', 'like', '%' . $search . '%')
                                ->orWhere('amount', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('details.project', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('details.account', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                    });
                });
            }
        }

        $expenses = $expenses->latest()->limit(100)->get();
        return response()->json(['data' => $expenses]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExpenseRequest $request)
    {
        return $this->handleExpense($request, new Expense());
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load(['details', 'details.project:id,name', 'details.account:id,name', 'attachments']);
        return response()->json(['data' => $expense]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        return $this->handleExpense($request, $expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( Expense $expense)
    {
        try {
            DB::transaction(function() use($expense) {
                $expense->details()->delete();
                $expense->attachments()->each(function ($attachment) {
                    $filePath = str_replace(asset('storage/'), '', $attachment->file_url);
                    Storage::disk('public')->delete($filePath);
                    $attachment->delete();
                });
                $expense->delete();
            });

            return response()->json(['message' => 'Expense deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting expense: ' . $e->getMessage());
            return response()->json(['message' => 'There was an error while processing your request.'], 500);
        }
    }

    public function fetchExpenseNo()
    {
        $expenseNo = $this->getExpenseNo();
        return response()->json(['data' => $expenseNo]);
    }

    private function getExpenseNo()
    {
        $lastExpense = Expense::max('expense_no');
        return $lastExpense ? $lastExpense + 1 : 1;
    }

    private function handleExpense(ExpenseRequest $request, Expense $expense)
    {
        $validatedData = $request->validated();        

        $isEdit = $expense->exists;

        try {
            
            DB::transaction(function() use($validatedData, $expense,$isEdit) {
                $expense->fill(array_merge($validatedData, [
                    'expense_date' => formatDate($validatedData['expense_date']),
                    'expense_no' => $isEdit ? $expense->expense_no : $this->getExpenseNo(),
                ]));
                $expense->save();

                $expense->details()->delete();

                if(isset($validatedData['attachments_to_delete']) && count($validatedData['attachments_to_delete']) > 0){
                    foreach ($validatedData['attachments_to_delete'] as $attachmentId) {
                        $attachment = $expense->attachments()->find($attachmentId);
                        if ($attachment) {
                            Storage::delete($attachment->file_path); 
                            $attachment->delete(); 
                        }
                    }
                }

                $totalAmount = 0;
                foreach($validatedData['details'] as $detail)
                {
                    $totalAmount += $detail['amount'];                
                    ExpenseDetail::create([
                        'expense_id' => $expense->id,
                        'gl_account_id' => $detail['gl_account_id'],
                        'project_id' => $detail['project_id'],
                        'description' => trim(strtolower($detail['description'])),
                        'amount' => $detail['amount'],
                    ]);                   
                }                
                  
                if (isset($validatedData['attachments']) && count($validatedData['attachments']) > 0) {
                    $attachments = $validatedData['attachments'];
                    foreach ($attachments as $attachment) {
                        $path = $attachment->store('expenses/attachments', 'public');
                        $expense->attachments()->create(['file_url' => asset("storage/{$path}")]);
                    }
                }                
            });

            return response()->json(['message' => 'Expense saved successfully'], $isEdit ? 200 : 201);

        } catch (\Exception $e) {
            Log::error('Error saving expense: ' . $e->getMessage());
            return response()->json(['message' => 'There was an error while processing your request.'], 500);
        }  
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Models\GlAccount;
use App\Models\Ledger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlAccountController extends Controller
{
    /**
     * Display a listing of the resource.
    */
    public function index(Request $request)
    {
       $query = GlAccount::whereNull('parent_id');
        if($search = $request->input('q')){
            $query->where('name','like','%'.$search.'%');
        }
        $glaccounts = $query->with(['childrenRecursive','accountType'])
                            ->get();

        return response()->json(['data' => $this->formatGlAccounts($glaccounts)]);
    }
    
    public function getAccounts()
    {
        $accounts = GlAccount::whereNotNull('parent_id')->where('active',true)->orderBy('name')->get();
        return response()->json(['data' => $accounts]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountRequest $request)
    {
        return $this->handleGlAccount($request, new GlAccount());
    }

    /**
     * Display the specified resource.
     */
    public function show(GlAccount $account)
    {
        return response()->json(['data' => $account]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountRequest $request, GlAccount $account)
    {
        return $this->handleGlAccount($request, $account);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GlAccount $account)
    {
        if ($this->notMutable($account)) {
            return response()->json(['error' => 'This account cannot be deleted!'], 400);
        }

        $isReferenced = Ledger::where('account', strtolower(trim($account->name)))
                                ->orWhere('parent_account', strtolower(trim($account->name)))
                                ->exists();

        if ($isReferenced) {
            return response()->json(['error' => 'This account cannot be deleted as it\'s referenced in the ledger!'], 400);
        }

        try {
            DB::transaction(function() use ($account) {
                $account->delete();
            });

            return response()->json(['message' => 'Gl Account deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Gl Account Error: ' . $e->getMessage());
            return response()->json(['error' => 'There was a problem processing your request!'], 500);
        }
    }

    private function formatGlAccounts($glAccounts)
    {
        return $glAccounts->map(function ($account)  {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'account_type' => $account->accountType ? $account->accountType->name : null,
                'children' => $this->formatGlAccounts($account->children),
                'isActive' => $account->is_active,
                'isEditable' => $account->is_editable,                
            ];
        });
    }

    private function handleGlAccount(AccountRequest $request, GlAccount $glAccount)
    {
        $isEdit = $glAccount->exists;

        if ($isEdit && $this->notMutable($glAccount)) {
            return response()->json(['error' => 'This account is not editable!'], 400);
        }

        $nameExists = GlAccount::where('name', strtolower(trim($request->validated()['name'])))
                                ->where('id', '!=', $glAccount->id)
                                ->exists();

        if($nameExists) {
            return response()->json(['error' => 'Gl Account name already exists!'], 400);
        }
        
        try {
           
            DB::transaction(function() use ($request, $glAccount,$isEdit) {

                $validated = $request->validated();

                $nameIsChanged = !$isEdit ? false : strtolower(trim($glAccount->name)) != strtolower(trim($validated['name']));

                $glAccount->fill(array_merge($validated, [
                    'parent_id' => $validated['is_subcategory'] ? $validated['parent_id'] : $validated['account_type_id']
                ]));                
                $glAccount->save();                

                if($isEdit){
                    $glAccount->children()->update(['account_type_id' => $validated['account_type_id']]);
                    if($validated['active'] == false){
                        $glAccount->children()->update(['active' => $validated['active']]);
                    }

                    if($nameIsChanged){
                        Ledger::whereRaw('LOWER(account) = ?', [strtolower(trim($validated['name']))])
                                ->update(['account' => strtolower(trim($validated['name']))]);

                        Ledger::whereRaw('LOWER(parent_account) = ?', [strtolower(trim($validated['name']))])
                                ->update(['parent_account' => strtolower(trim($validated['name']))]);
                    }
                }                

            });

            return response()->json([
                'message' => $isEdit ? 'Gl Account updated successfully!' : 'Gl Account created successfully!'
            ],$isEdit ? 200 : 201);
        } catch (\Exception $e) {
            Log::error('Gl Account Error: ' . $e->getMessage());
            return response()->json(['error' => 'There was a problem processing your request!'], 500);
        }
    }

    private function notMutable(GlAccount $glAccount)
    {
        return $glAccount->is_editable == false || $glAccount->parent_id === null;
    }
    
}

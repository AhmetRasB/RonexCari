<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    /**
     * Show account selection page
     */
    public function select()
    {
        $accounts = Account::active()->get();
        
        if ($accounts->count() === 1) {
            // If only one account, auto-select it
            session(['current_account_id' => $accounts->first()->id]);
            return redirect()->route('dashboard');
        }
        
        return view('account.select', compact('accounts'));
    }

    /**
     * Show account management page
     */
    public function manage()
    {
        $accounts = Account::all();
        return view('account.manage', compact('accounts'));
    }

    /**
     * Update account information
     */
    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string'
        ]);

        $account->update($request->all());

        return redirect()->back()->with('success', 'Hesap bilgileri güncellendi.');
    }

    /**
     * Store selected account in session
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id'
        ]);

        $account = Account::findOrFail($request->account_id);
        
        if (!$account->is_active) {
            return redirect()->back()->with('error', 'Seçilen hesap aktif değil.');
        }

        session(['current_account_id' => $account->id]);
        
        return redirect()->route('dashboard')->with('success', $account->name . ' hesabı seçildi.');
    }

    /**
     * Switch to another account
     */
    public function switch(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id'
        ]);

        $account = Account::findOrFail($request->account_id);
        
        if (!$account->is_active) {
            return redirect()->back()->with('error', 'Seçilen hesap aktif değil.');
        }

        session(['current_account_id' => $account->id]);
        
        return redirect()->back()->with('success', $account->name . ' hesabına geçildi.');
    }
}

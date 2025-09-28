<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountSelectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip account selection for certain routes
        $skipRoutes = [
            'login',
            'logout',
            'account.select',
            'account.switch',
            'password.confirm',
            'password.update',
            'profile.edit',
            'profile.update',
            'profile.destroy',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'register',
            'password.request',
            'password.email',
            'password.reset'
        ];

        if (in_array($request->route()?->getName(), $skipRoutes)) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        // Check if current account is selected in session
        if (!session()->has('current_account_id')) {
            // Check if user has any active accounts
            $activeAccounts = \App\Models\Account::active()->get();
            if ($activeAccounts->count() === 0) {
                return redirect()->route('account.select')->with('error', 'Aktif hesap bulunamadı.');
            }
            
            // Auto-select the first account (for testing)
            $account = $activeAccounts->first();
            session(['current_account_id' => $account->id]);
        }

        // Verify the account exists and is active
        $account = \App\Models\Account::find(session('current_account_id'));
        if (!$account || !$account->is_active) {
            session()->forget('current_account_id');
            return redirect()->route('account.select');
        }

        // Share current account with all views
        view()->share('currentAccount', $account);

        return $next($request);
    }
}

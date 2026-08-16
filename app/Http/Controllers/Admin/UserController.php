<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) =>
                $w->where('name', 'ilike', "%{$s}%")->orWhere('email', 'ilike', "%{$s}%")))
            ->when($request->role && $request->role !== 'All',
                fn ($q, $r) => $q->where('role', strtolower($r)))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', [
            'users'   => $users,
            'search'  => $request->search,
            'role'    => $request->role ?? 'All',
            'summary' => [
                'total'   => User::count(),
                'admins'  => User::where('role', 'admin')->count(),
                // is_premium is derived now, so count it through transactions.
                'premium' => \App\Models\Transaction::where('status', 'Paid')
                    ->distinct('user_id')
                    ->count('user_id'),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with('user')
            ->when($request->status && $request->status !== 'All',
                fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions', [
            'transactions' => $transactions,
            'status'       => $request->status ?? 'All',
            'summary'      => [
                'revenue' => (float) Transaction::where('status', 'Paid')->sum('amount'),
                'paid'    => Transaction::where('status', 'Paid')->count(),
                'pending' => Transaction::where('status', 'Pending')->count(),
            ],
        ]);
    }
}

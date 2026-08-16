<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $feedback = Feedback::with(['user', 'church'])
            ->when($request->status && $request->status !== 'All',
                fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.feedback', [
            'feedback' => $feedback,
            'status'   => $request->status ?? 'All',
            'summary'  => [
                'total'    => Feedback::count(),
                'pending'  => Feedback::where('status', 'Pending')->count(),
                'approved' => Feedback::where('status', 'Approved')->count(),
                'flagged'  => Feedback::where('status', 'Flagged')->count(),
            ],
        ]);
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:Pending,Approved,Flagged'],
        ]);

        $feedback->update($data);

        return back()->with('success', "Feedback marked as {$data['status']}.");
    }
}

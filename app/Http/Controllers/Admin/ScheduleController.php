<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.schedules', [
            'schedules' => Schedule::with('church')->orderBy('schedule_date')->paginate(15),
            'churches'  => Church::active()->orderBy('name')->get(),
            'types'     => ['Mass', 'Novena', 'Feast Day', 'Procession', 'Other'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'church_id'     => ['required', 'exists:churches,id'],
            'event_name'    => ['required', 'string', 'max:200'],
            'event_type'    => ['required', 'in:Mass,Novena,Feast Day,Procession,Other'],
            'schedule_date' => ['nullable', 'date'],
            'start_time'    => ['nullable', 'date_format:H:i'],
            'end_time'      => ['nullable', 'date_format:H:i', 'after:start_time'],
            'is_recurring'  => ['nullable', 'boolean'],
            'recurrence'    => ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string'],
        ]);

        Schedule::create($data + [
            'is_recurring' => $request->boolean('is_recurring'),
            'created_at'   => now(),
        ]);

        return back()->with('success', 'Schedule added.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return back()->with('success', 'Schedule removed.');
    }
}

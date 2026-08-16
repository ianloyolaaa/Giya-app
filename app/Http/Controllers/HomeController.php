<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\Schedule;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            // rating is derived now, so sort on the averaged feedback column.
            'featured'  => Church::active()->featured()->orderByRating()->take(4)->get(),
            'upcoming'  => Schedule::with('church')->orderBy('schedule_date')->take(5)->get(),
            'stats'     => [
                'churches' => Church::active()->count(),
                'cities'   => Church::active()->distinct('location')->count('location'),
            ],
        ]);
    }
}

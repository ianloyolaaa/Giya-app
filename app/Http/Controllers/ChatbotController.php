<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function index(): View
    {
        return view('chatbot');
    }
}

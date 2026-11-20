<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:160'],
            'review' => ['required', 'string', 'min:12', 'max:1500'],
            'website' => ['nullable', 'max:0'],
        ]);

        Review::query()->create([
            'reviewer_name' => $validated['name'],
            'reviewer_email' => $validated['email'] ?? null,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['review'],
            'status' => 'pending',
        ]);

        return redirect()->to(route('home').'#review-form')->with('review_success', true);
    }
}

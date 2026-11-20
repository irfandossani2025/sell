<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()->latest()->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Review $review, string $status): RedirectResponse
    {
        abort_unless(in_array($status, ['approved', 'rejected'], true), 404);

        $review->update([
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);

        return back()->with('status', $status === 'approved' ? 'Review is now visible on the site.' : 'Review has been rejected.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteEvent;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $start = Carbon::today()->subDays(6);
        $eventsByDay = SiteEvent::query()
            ->whereDate('occurred_at', '>=', $start)
            ->selectRaw('DATE(occurred_at) as event_day, event_type, COUNT(*) as event_count')
            ->groupBy('event_day', 'event_type')
            ->orderBy('event_day')
            ->get();

        $daily = collect(range(0, 6))->map(function (int $offset) use ($start, $eventsByDay): array {
            $date = $start->copy()->addDays($offset)->toDateString();
            $events = $eventsByDay->where('event_day', $date);

            return [
                'label' => Carbon::parse($date)->format('D'),
                'views' => (int) $events->where('event_type', 'product_view')->sum('event_count'),
                'clicks' => (int) $events->where('event_type', 'affiliate_click')->sum('event_count'),
            ];
        });

        $maxActivity = max(1, (int) $daily->max(fn (array $day): int => $day['views'] + $day['clicks']));
        $stats = [
            'products' => Product::query()->where('status', 'published')->count(),
            'clicks' => SiteEvent::query()->where('event_type', 'affiliate_click')->count(),
            'pending_reviews' => Review::query()->where('status', 'pending')->count(),
            'sales' => Order::query()->where('status', 'paid')->count(),
        ];
        $topProducts = Product::query()
            ->with('category')
            ->withCount(['events as affiliate_clicks_count' => fn ($query) => $query->where('event_type', 'affiliate_click')])
            ->where('product_type', 'affiliate')
            ->orderByDesc('affiliate_clicks_count')
            ->limit(5)
            ->get();
        $recentOrders = Order::query()->with('items')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('daily', 'maxActivity', 'stats', 'topProducts', 'recentOrders'));
    }
}

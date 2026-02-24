<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'technician.user', 'technician.maintenanceCompany', 'service']);

        // --- Statistics Calculation ---
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfLastWeek = $startOfWeek->copy()->subWeek();

        // Total Stats
        $totalEvaluations = Review::count();
        $lastWeekTotal = Review::where('created_at', '<', $startOfWeek)
            ->where('created_at', '>=', $startOfLastWeek)
            ->count();
        $totalChange = $lastWeekTotal > 0 ? (($totalEvaluations - $lastWeekTotal) / $lastWeekTotal) * 100 : 0;

        // Average Rating
        $averageRating = Review::avg('rating') ?: 0;
        $lastWeekAvg = Review::where('created_at', '<', $startOfWeek)
            ->where('created_at', '>=', $startOfLastWeek)
            ->avg('rating') ?: 0;
        $avgChange = $lastWeekAvg > 0 ? (($averageRating - $lastWeekAvg) / $lastWeekAvg) * 100 : 0;

        // Positive Ratings (> 3)
        $positiveRatings = Review::where('rating', '>', 3)->count();
        $lastWeekPositive = Review::where('rating', '>', 3)
            ->where('created_at', '<', $startOfWeek)
            ->where('created_at', '>=', $startOfLastWeek)
            ->count();
        $positiveChange = $lastWeekPositive > 0 ? (($positiveRatings - $lastWeekPositive) / $lastWeekPositive) * 100 : 0;

        // Negative Ratings (<= 3)
        $negativeRatings = Review::where('rating', '<=', 3)->count();
        $lastWeekNegative = Review::where('rating', '<=', 3)
            ->where('created_at', '<', $startOfWeek)
            ->where('created_at', '>=', $startOfLastWeek)
            ->count();
        $negativeChange = $lastWeekNegative > 0 ? (($negativeRatings - $lastWeekNegative) / $lastWeekNegative) * 100 : 0;

        // Doughnut Chart Data (distribution)
        $ratingDistribution = [
            '5' => Review::where('rating', 5)->count(),
            '4' => Review::where('rating', 4)->count(),
            '3' => Review::where('rating', 3)->count(),
            '2' => Review::where('rating', 2)->count(),
            '1' => Review::where('rating', 1)->count(),
        ];

        // Sparkline Data (last 7 days)
        $days = [];
        $totalTrend = [];
        $positiveTrend = [];
        $negativeTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $days[] = $date;
            $totalTrend[] = Review::whereDate('created_at', $date)->count();
            $positiveTrend[] = Review::whereDate('created_at', $date)->where('rating', '>', 3)->count();
            $negativeTrend[] = Review::whereDate('created_at', $date)->where('rating', '<=', 3)->count();
        }

        $stats = [
            'total' => [
                'value' => $totalEvaluations,
                'change' => round($totalChange, 2),
                'trend' => $totalTrend,
            ],
            'average' => [
                'value' => round($averageRating, 1),
                'change' => round($avgChange, 2),
                'distribution' => json_encode(array_values($ratingDistribution)),
            ],
            'positive' => [
                'value' => $positiveRatings,
                'change' => round($positiveChange, 2),
                'trend' => $positiveTrend,
            ],
            'negative' => [
                'value' => $negativeRatings,
                'change' => round($negativeChange, 2),
                'trend' => $negativeTrend,
            ]
        ];
        // --- End Statistics ---

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('technician', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%");
                });
            });
        }

        // Filter by Client Type
        if ($request->filled('client_type')) {
            $clientType = $request->input('client_type');
            if ($clientType === 'individual') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'individual');
                });
            } elseif ($clientType === 'company') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'corporate_customer');
                });
            }
        }

        // Filter by Technician Type
        if ($request->filled('technician_type')) {
            $techType = $request->input('technician_type');
            if ($techType === 'company') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNotNull('maintenance_company_id');
                });
            } elseif ($techType === 'platform' || $techType === 'individual') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNull('maintenance_company_id');
                });
            }
        }

        // Filter by Service Category
        if ($request->filled('service_category_id')) {
            $categoryId = $request->input('service_category_id');
            $query->whereHas('service', function ($q) use ($categoryId) {
                $q->where('parent_id', $categoryId);
            });
        }

        // Filter by specific Service IDs (Child services)
        if ($request->has('service_ids')) {
            $serviceIds = $request->input('service_ids');
            if (is_array($serviceIds) && count($serviceIds) > 0) {
                $query->whereIn('service_id', $serviceIds);
            }
        }

        // Filter by Date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter by Status (Quick filters)
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'positive') {
                $query->where('rating', '>', 3);
            } elseif ($status === 'negative') {
                $query->where('rating', '<=', 3);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'newest');
        if ($sortBy == 'name') {
            $query->join('users', 'reviews.user_id', '=', 'users.id')
                  ->orderBy('users.name', 'asc')
                  ->select('reviews.*');
        } elseif ($sortBy == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sortBy == 'rating') {
             $query->orderBy('rating', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('per_page', 10);
        $items = $query->paginate($perPage);
        
        $categories = Service::whereNull('parent_id')->get();
        $services = Service::whereNotNull('parent_id')->get();

        return view('admin.reviews.index', compact('items', 'stats', 'categories', 'services'));
    }

    public function show($id)
    {
        $item = Review::with(['user', 'technician.user', 'technician.maintenanceCompany', 'service'])->findOrFail($id);
        return view('admin.reviews.show', compact('item'));
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', __('Review deleted successfully.'));
    }

    public function download(Request $request)
    {
        $query = Review::with(['user', 'technician.user', 'technician.maintenanceCompany', 'service']);

        if ($request->has('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('technician', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('client_type')) {
            $clientType = $request->input('client_type');
            if ($clientType === 'individual') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'individual');
                });
            } elseif ($clientType === 'company') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'corporate_customer');
                });
            }
        }

        if ($request->filled('technician_type')) {
            $techType = $request->input('technician_type');
            if ($techType === 'company') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNotNull('maintenance_company_id');
                });
            } elseif ($techType === 'platform' || $techType === 'individual') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNull('maintenance_company_id');
                });
            }
        }

        if ($request->has('service_id')) {
            $serviceId = $request->input('service_id');
            if (is_array($serviceId)) {
                $query->whereIn('service_id', $serviceId);
            } else {
                $query->where('service_id', $serviceId);
            }
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'positive') {
                $query->where('rating', '>', 3);
            } elseif ($status === 'negative') {
                $query->where('rating', '<=', 3);
            }
        }

        $reviews = $query->orderBy('created_at', 'desc')->get();

        $filename = "reviews_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

        fputcsv($handle, [
            'Client Name',
            'Client Type',
            'Technician Name',
            'Technician Type',
            'Service',
            'Rating',
            'Client Note',
            'Status',
            'Date'
        ]);

        foreach ($reviews as $review) {
            $clientType = $review->user ? ($review->user->type == 'individual' ? 'Individual' : 'Company') : 'N/A';
            $techType = $review->technician && $review->technician->maintenance_company_id ? 'Company' : 'Individual/Platform';
            $status = $review->rating > 3 ? 'Positive' : 'Negative';

            fputcsv($handle, [
                $review->user ? $review->user->name : 'N/A',
                $clientType,
                $review->technician ? ($review->technician->user->name ?? $review->technician->name) : 'N/A',
                $techType,
                $review->service ? ($review->service->name_ar ?? $review->service->name_en) : 'N/A',
                $review->rating,
                $review->comment ?? '',
                $status,
                $review->created_at->format('Y-m-d H:i')
            ]);
        }

        fseek($handle, 0);

        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]
        );
    }
}

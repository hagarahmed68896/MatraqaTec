<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'technician.maintenanceCompany', 'service']);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('technician', function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%");
                });
            });
        }

        // Filter by Client Type
        if ($request->has('client_type')) {
            $clientType = $request->input('client_type');
            if ($clientType === 'individual') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'individual');
                });
            } elseif ($clientType === 'company') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'maintenance_company');
                });
            }
        }

        // Filter by Technician Type
        if ($request->has('technician_type')) {
            $techType = $request->input('technician_type');
            if ($techType === 'company') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNotNull('maintenance_company_id');
                });
            } elseif ($techType === 'individual') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNull('maintenance_company_id');
                });
            }
        }

        // Filter by Service
        if ($request->has('service_id')) {
            $serviceId = $request->input('service_id');
            if (is_array($serviceId)) {
                $query->whereIn('service_id', $serviceId);
            } else {
                $query->where('service_id', $serviceId);
            }
        }

        // Filter by Date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter by Status
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'positive') {
                $query->where('rating', '>', 3);
            } elseif ($status === 'negative') {
                $query->where('rating', '<=', 3);
            }
        }

        // Sorting
        $sortColumn = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_order', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->input('per_page', 20);
        $items = $query->paginate($perPage);

        return view('admin.reviews.index', compact('items'));
    }

    public function show($id)
    {
        $item = Review::with(['user', 'technician.maintenanceCompany', 'service'])->findOrFail($id);
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
        $query = Review::with(['user', 'technician.maintenanceCompany', 'service']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('technician', function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                      ->orWhere('name_ar', 'like', "%{$search}%");
                });
            });
        }

        if ($request->has('client_type')) {
            $clientType = $request->input('client_type');
            if ($clientType === 'individual') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'individual');
                });
            } elseif ($clientType === 'company') {
                $query->whereHas('user', function ($q) {
                    $q->where('type', 'maintenance_company');
                });
            }
        }

        if ($request->has('technician_type')) {
            $techType = $request->input('technician_type');
            if ($techType === 'company') {
                $query->whereHas('technician', function ($q) {
                    $q->whereNotNull('maintenance_company_id');
                });
            } elseif ($techType === 'individual') {
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
                $review->technician ? ($review->technician->name_ar ?? $review->technician->name_en) : 'N/A',
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

<?php

namespace App\Http\Controllers\Api\Admin;

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
                    $q->where('type', 'like', 'corporate%'); // Assuming corporate_company
                });
            }
        }

        // Filter by Technician Type (Company vs Individual/Platform)
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

        // Filter by Service (Support multiple)
        if ($request->has('service_id')) {
            $serviceId = $request->input('service_id');
            if (is_array($serviceId)) {
                $query->whereIn('service_id', $serviceId);
            } else {
                $query->where('service_id', $serviceId);
            }
        }

        // Filter by Service Category (if applicable, assuming service has parent_id or category logic, otherwise skip for now or use service_id)
        // Leaving as service_id since "Service Category" wasn't explicitly mapped in my plan beyond Service Model.

        // Filter by Date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter by Status (Positive/Negative)
        if ($request->has('status')) {
            $status = $request->input('status'); // 'positive' or 'negative' or specific numeric?
            // "الحاله (تقييم ايجابي - سلبي )"
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
        $reviews = $query->paginate($perPage);

        return response()->json(['status' => true, 'message' => 'Reviews retrieved', 'data' => $reviews]);
    }

    public function show($id)
    {
        $review = Review::with(['user', 'technician.maintenanceCompany', 'service'])->find($id);
        if (!$review) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Review retrieved', 'data' => $review]);
    }

    public function destroy($id)
    {
        $review = Review::find($id);
        if (!$review) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $review->delete();
        return response()->json(['status' => true, 'message' => 'Review deleted']);
    }

    public function download(Request $request)
    {
        $query = Review::with(['user', 'technician.maintenanceCompany', 'service']);

        // Apply same filters as index
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
                    $q->where('type', 'like', 'corporate%');
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

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reviews.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($reviews) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Columns based on User Request
            // اسم العميل, نوع العميل, اسم الفني, نوع الفني, الخدمه, التقييم, ملاحظه العميل, الحاله, التاريخ
            fputcsv($file, [
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
                // Determine Client Type
                // Assuming 'individual' => Individual, 'corporate_company' => Company
                $clientType = $review->user ? ($review->user->type == 'individual' ? 'Individual' : 'Company') : 'N/A';
                
                // Determine Technician Type
                $techType = $review->technician && $review->technician->maintenance_company_id ? 'Company' : 'Individual/Platform';

                // Determine Status
                $status = $review->rating > 3 ? 'Positive' : 'Negative';

                fputcsv($file, [
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
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

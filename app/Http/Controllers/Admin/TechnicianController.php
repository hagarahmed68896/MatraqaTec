<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\User;
use App\Models\Service;
use App\Models\Order; // Added for statistics
use App\Models\Review; // Added for statistics
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $query = Technician::with('user', 'service', 'category');

        // 1. Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter by Status
        if ($request->has('status') && $request->status) {
            $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // 3. Sorting Logic
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->orderBy('name_en', 'asc');
                    break;
                case 'status':
                    $query->join('users', 'technicians.user_id', '=', 'users.id')
                          ->orderBy('users.status', 'asc')
                          ->select('technicians.*');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $items = $query->paginate(20);

        // Statistics for Index Page
        $stats = [
            'total_technicians' => Technician::count(),
            'active_technicians' => Technician::whereHas('user', function($q) { $q->where('status', 'active'); })->count(),
            'total_completed_orders' => Order::where('status', 'completed')->whereNotNull('technician_id')->count(),
            'average_rating' => Review::avg('rating') ?? 0,
        ];
        
        return view('admin.technicians.index', compact('items', 'stats'));
    }

    public function top(Request $request)
    {
        // Get all top technicians sorted by completed orders and ratings
        $query = Technician::with(['user', 'service', 'category', 'maintenanceCompany'])
            ->withCount(['orders' => function($q) {
                $q->where('status', 'completed');
            }])
            ->withAvg('reviews', 'rating');

        // Search Logic
        if ($request->has('search') && $request->search) {
            $searchTerms = explode(' ', trim($request->search));
            
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQ) use ($term) {
                        $subQ->where('name_en', 'like', "%{$term}%")
                             ->orWhere('name_ar', 'like', "%{$term}%")
                             ->orWhereHas('user', function ($q2) use ($term) {
                                  $q2->where('email', 'like', "%{$term}%")
                                     ->orWhere('phone', 'like', "%{$term}%")
                                     ->orWhere('name', 'like', "%{$term}%");
                              })
                             ->orWhereHas('category', function ($q3) use ($term) {
                                  $q3->where('name_en', 'like', "%{$term}%")
                                     ->orWhere('name_ar', 'like', "%{$term}%");
                              })
                             ->orWhereHas('service', function ($q4) use ($term) {
                                  $q4->where('name_en', 'like', "%{$term}%")
                                     ->orWhere('name_ar', 'like', "%{$term}%");
                              });
                    });
                }
            });
        }

        $items = $query->orderByDesc('orders_count')->paginate(20);
        
        return view('admin.technicians.top', compact('items'));
    }

    public function create()
    {
        $services = Service::whereNotNull('parent_id')->get();
        return view('admin.technicians.create', compact('services'));
    }

    public function store(Request $request)
    {
        $locale = app()->getLocale();
        $rules = [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'required|string|unique:users',
            'service_id' => 'required|exists:services,id',
            'years_experience' => 'nullable|integer',
            'status' => 'required|string', 
            'image' => 'nullable|image|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        $validated = $request->validate($rules);

        $password = $request->password ?? Str::random(10);
        
        $user = User::create([
            'name' => $request->name_en, // Default to EN name for User model
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'technician',
            'phone' => $request->phone,
            'status' => $request->status ?? 'active',
        ]);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('technicians', 'public');
        }

        $technician = Technician::create([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'national_id' => $request->national_id, 
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'bio_en' => $request->bio_en,
            'bio_ar' => $request->bio_ar,
            'years_experience' => $request->years_experience ?? 0,
            'image' => $imagePath,
        ]);

        if ($request->hasFile('documents')) {
             foreach ($request->file('documents') as $doc) {
                 $path = $doc->store('technicians/documents', 'public');
                 $technician->attachments()->create([
                     'path' => $path,
                     'type' => 'document',
                     'name' => $doc->getClientOriginalName()
                 ]);
             }
        }

        return redirect()->route('admin.technicians.index')->with('success', __('Technician created successfully.'));
    }

    public function show(Request $request, $id)
    {
        $item = Technician::with(['user', 'service', 'maintenanceCompany', 'attachments', 'user.city'])->findOrFail($id);
        
        // Detailed Stats
        $stats = [
             'total_orders' => Order::where('technician_id', $item->id)->count(),
             'completed_orders' => Order::where('technician_id', $item->id)->where('status', 'completed')->count(),
             'revenue' => Order::where('technician_id', $item->id)->where('status', 'completed')->sum('total_price'),
             'rating' => Review::where('technician_id', $item->id)->avg('rating') ?? 0,
        ];

        $orders = Order::where('technician_id', $item->id)->with(['service', 'user'])->latest()->get();
        $reviews = Review::where('technician_id', $item->id)->with(['order', 'user'])->latest()->get();

        // Performance Chart Data
        $chartType = $request->get('chart_type', 'monthly');
        $performanceData = [];
        
        if ($chartType === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $label = __('Week') . ' ' . $date->weekOfYear;
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$date->startOfWeek()->toDateTimeString(), $date->endOfWeek()->toDateTimeString()])
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } elseif ($chartType === 'yearly') {
            for ($i = 2; $i >= 0; $i--) {
                $date = Carbon::now()->subYears($i);
                $label = $date->year;
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } else { // monthly
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $label = $date->translatedFormat('F');
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        }

        if ($request->ajax()) {
            return response()->json(['performanceData' => $performanceData]);
        }

        return view('admin.technicians.show', compact('item', 'stats', 'orders', 'reviews', 'performanceData', 'chartType'));
    }

    public function edit($id)
    {
        $item = Technician::findOrFail($id);
        $services = Service::whereNotNull('parent_id')->get();
        return view('admin.technicians.edit', compact('item', 'services'));
    }

    public function update(Request $request, $id)
    {
        $technician = Technician::findOrFail($id);
        
        $rules = [
            'email' => 'required|string|email|max:255|unique:users,email,'.$technician->user_id,
            'phone' => 'nullable|string|unique:users,phone,'.$technician->user_id,
            'image' => 'nullable|image|max:2048',
        ];

        $request->validate($rules);
        
        $user = $technician->user;
        if ($user) {
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            // Sync name
            if ($request->has('name_en')) $user->name = $request->name_en;
            $user->save();
        }

        $data = $request->except(['email', 'password', 'phone', 'status', 'type', 'image', 'documents']);
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('technicians', 'public');
        }

        $technician->update($data);

        return redirect()->route('admin.technicians.index')->with('success', __('Technician updated successfully.'));
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        if ($technician->user) {
            $technician->user->delete();
        } else {
            $technician->delete();
        }
        return redirect()->route('admin.technicians.index')->with('success', __('Technician deleted successfully.'));
    }

    public function toggleBlock($id)
    {
        $technician = Technician::findOrFail($id);
        $user = $technician->user;
        
        if ($user) {
             if ($user->status === 'blocked') {
                $user->status = 'active';
                $user->blocked_at = null;
                $message = __('Technician unblocked successfully');
            } else {
                $user->status = 'blocked';
                $user->blocked_at = now();
                $message = __('Technician blocked successfully');
            }
            $user->save();
             return back()->with('success', $message);
        }

        return back()->with('error', __('User record not found'));
    }

    public function download(Request $request)
    {
        $query = Technician::with(['user', 'service', 'category']);

        // 1. Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter by Status
        if ($request->has('status') && $request->status) {
            $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $items = $query->latest()->get();

        $csvFileName = 'technicians_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Row
            fputcsv($handle, [
                __('ID'),
                __('Name (AR)'),
                __('Name (EN)'),
                __('Email'),
                __('Phone'),
                __('Service'),
                __('Category'),
                __('Type'),
                __('Years Experience'),
                __('Rating'),
                __('Status'),
                __('Created At'),
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->name_ar,
                    $item->name_en,
                    $item->user->email ?? '',
                    $item->user->phone ?? '',
                    $item->service->name_ar ?? '',
                    $item->category->name_ar ?? '',
                    $item->maintenanceCompany ? __('Corporate') : __('Independent'),
                    $item->years_experience,
                    number_format($item->reviews()->avg('rating') ?? 0, 1),
                    __($item->user->status ?? 'active'),
                    $item->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

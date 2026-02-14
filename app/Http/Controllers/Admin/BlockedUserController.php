<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\IndividualCustomer;
use App\Models\CorporateCustomer;
use App\Models\Technician;
use App\Models\MaintenanceCompany;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BlockedUserController extends Controller
{
    public function customers(Request $request)
    {
        $type = $request->get('type', 'individual');
        
        if ($type === 'individual') {
            $query = IndividualCustomer::with('user')->whereHas('user', function ($q) {
                $q->where('status', 'blocked');
            });
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name_ar', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q2) use ($search) {
                          $q2->where('email', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }
        } else {
            // Corporate customers use 'inactive' status usually, but the user asked for "Blocked"
            // Checking CorporateCustomerController, it uses 'inactive'
            $query = CorporateCustomer::with('user')->whereHas('user', function ($q) {
                $q->where('status', 'inactive')->orWhere('status', 'blocked');
            });

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('company_name_ar', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q2) use ($search) {
                          $q2->where('email', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        
        return view('admin.blocked.customers', compact('items', 'type'));
    }

    public function companies(Request $request)
    {
        $query = MaintenanceCompany::with(['user'])->withCount('orders')->whereHas('user', function ($q) {
            $q->where('status', 'blocked');
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        return view('admin.blocked.maintenance_companies', compact('items'));
    }

    public function technicians(Request $request)
    {
        $query = Technician::with(['user', 'service', 'maintenanceCompany', 'category'])->whereHas('user', function ($q) {
            $q->where('status', 'blocked');
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        return view('admin.blocked.technicians', compact('items'));
    }

    public function supervisors(Request $request)
    {
        $query = User::with('roles')->where('type', 'supervisor')->where('status', 'blocked');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        return view('admin.blocked.supervisors', compact('items'));
    }

    public function bulkUnblock(Request $request)
    {
        $ids = $request->ids;
        $targetType = $request->target_type;

        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => __('No users selected.')], 400);
        }

        User::whereIn('id', $ids)->update(['status' => 'active', 'blocked_at' => null]);

        return response()->json(['success' => true, 'message' => __('Selected users unblocked successfully.')]);
    }

    public function download(Request $request)
    {
        $target = $request->target;
        $type = $request->get('type', 'individual');
        
        $filename = "blocked_{$target}_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://temp', 'w+');
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
        
        if ($target === 'customers') {
            if ($type === 'individual') {
                $items = IndividualCustomer::with('user')->whereHas('user', function ($q) { $q->where('status', 'blocked'); })->get();
                fputcsv($handle, [__('ID'), __('Name'), __('Email'), __('Mobile Number'), __('Address'), __('Date')]);
                foreach ($items as $item) {
                    fputcsv($handle, [$item->id, $item->first_name_ar ?? $item->name, $item->user->email ?? '', $item->user->phone ?? '', $item->address, $item->created_at]);
                }
            } else {
                $items = CorporateCustomer::with('user')->whereHas('user', function ($q) { $q->where('status', 'inactive')->orWhere('status', 'blocked'); })->get();
                fputcsv($handle, [__('Company Name'), __('Mobile Number'), __('Email'), __('Address'), __('Commercial Record'), __('Tax Number'), __('Orders Count'), __('Date')]);
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->company_name_ar ?? $item->name, 
                        $item->user->phone ?? '',
                        $item->user->email ?? '', 
                        $item->address,
                        $item->commercial_record_number, 
                        $item->tax_number,
                        $item->order_count ?? 0,
                        $item->created_at
                    ]);
                }
            }
        } elseif ($target === 'companies') {
            $items = MaintenanceCompany::with(['user'])->withCount('orders')->whereHas('user', function ($q) { $q->where('status', 'blocked'); })->get();
            fputcsv($handle, [__('Company Name'), __('Mobile Number'), __('Email'), __('Address'), __('Commercial Record'), __('Tax Number'), __('Orders Count'), __('Date')]);
            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->name ?? $item->company_name_ar, 
                    $item->user->phone ?? '',
                    $item->user->email ?? '', 
                    $item->address,
                    $item->commercial_record_number, 
                    $item->tax_number,
                    $item->orders_count ?? 0,
                    $item->created_at
                ]);
            }
        } elseif ($target === 'technicians') {
            $items = Technician::with(['user', 'service', 'maintenanceCompany', 'category'])->whereHas('user', function ($q) { $q->where('status', 'blocked'); })->get();
            fputcsv($handle, [__('Technician Name'), __('Mobile Number'), __('Email'), __('Company Name'), __('Service Name'), __('Service Type'), __('Orders Count'), __('Date')]);
            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->name ?? $item->name_ar, 
                    $item->user->phone ?? '',
                    $item->user->email ?? '', 
                    $item->maintenanceCompany->name ?? '-',
                    $item->service->name_ar ?? $item->service->name ?? '-',
                    $item->category->name_ar ?? $item->category->name ?? '-',
                    $item->order_count ?? 0,
                    $item->created_at
                ]);
            }
        } elseif ($target === 'supervisors') {
            $items = User::with('roles')->where('type', 'supervisor')->where('status', 'blocked')->get();
            fputcsv($handle, [__('Supervisor Name'), __('Mobile Number'), __('Email'), __('Role'), __('Date')]);
            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->name, 
                    $item->phone, 
                    $item->email, 
                    $item->roles->pluck('name')->implode(', '),
                    $item->created_at
                ]);
            }
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
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}

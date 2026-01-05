<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    
public function index(Request $request)
    {
        $query = Contract::with('maintenanceCompany');

        // Search by contract number or company name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('maintenanceCompany', function ($q2) use ($search) {
                      $q2->where('company_name_en', 'like', "%{$search}%")
                         ->orWhere('company_name_ar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by maintenance company
        if ($request->has('maintenance_company_id')) {
            $query->where('maintenance_company_id', $request->maintenance_company_id);
        }

        // Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->join('maintenance_companies', 'contracts.maintenance_company_id', '=', 'maintenance_companies.id')
                          ->orderBy('maintenance_companies.company_name_en', 'asc')
                          ->select('contracts.*');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $contracts = $query->paginate(15);
        return response()->json(['status' => true, 'message' => 'Contracts retrieved', 'data' => $contracts]);
    }


public function store(Request $request)
{
    // 1. Validate with detailed error reporting
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'contract_number'        => 'required|string|unique:contracts',
        'maintenance_company_id' => 'required|exists:maintenance_companies,id',
        'project_value'          => 'required|numeric',
        'start_date'             => 'required|date',
        'end_date'               => 'required|date|after:start_date',
        'status'                 => 'required|in:active,expired,completed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        \Illuminate\Support\Facades\DB::beginTransaction();

        // 2. Attempt to create
        $contract = Contract::create($request->all());

        \Illuminate\Support\Facades\DB::commit();

        return response()->json([
            'status' => true, 
            'message' => 'Contract created successfully', 
            'data' => $contract
        ], 201);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        
        // This will return the EXACT database error (e.g., missing column, permission denied)
        return response()->json([
            'status' => false,
            'message' => 'Database Error',
            'error_detail' => $e->getMessage()
        ], 500);
    }
}

public function show($id)
    {
        $contract = Contract::with('maintenanceCompany')->find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Contract retrieved', 'data' => $contract]);
    }

public function update(Request $request, $id)
{
    // 1. Find the contract
    $contract = Contract::find($id);
    if (!$contract) {
        return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
    }

    // 2. Validate (allowing "sometimes" for partial updates)
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'contract_number'        => 'sometimes|string|unique:contracts,contract_number,' . $id,
        'maintenance_company_id' => 'sometimes|exists:maintenance_companies,id',
        'project_value'          => 'sometimes|numeric',
        'start_date'             => 'sometimes|date',
        'end_date'               => 'sometimes|date|after:start_date',
        'status'                 => 'sometimes|in:active,expired,completed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        \Illuminate\Support\Facades\DB::beginTransaction();

        // 3. Update only the fields provided in the request
        $contract->update($request->all());

        \Illuminate\Support\Facades\DB::commit();

        return response()->json([
            'status' => true, 
            'message' => 'Contract updated successfully', 
            'data' => $contract->fresh('maintenanceCompany') // fresh() gets latest DB state
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        
        return response()->json([
            'status' => false,
            'message' => 'Update failed',
            'error_detail' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($id)
    {
        $contract = Contract::find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $contract->delete();
        return response()->json(['status' => true, 'message' => 'Contract deleted']);
    }

    public function download()
    {
        $contracts = Contract::with('maintenanceCompany')->get();
        return $this->generateCsv($contracts, "contracts.csv");
    }

    private function generateCsv($contracts, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Contract Number', 'Company Name', 'Value', 'Status', 'Start Date', 'End Date']); 

        foreach ($contracts as $contract) {
            fputcsv($handle, [
                $contract->id,
                $contract->contract_number,
                $contract->maintenanceCompany ? $contract->maintenanceCompany->company_name_en : 'N/A',
                $contract->project_value,
                $contract->status,
                $contract->start_date ? $contract->start_date->format('Y-m-d') : '',
                $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
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
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}

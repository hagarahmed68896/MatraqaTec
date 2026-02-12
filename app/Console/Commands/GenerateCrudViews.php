<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateCrudViews extends Command
{
    protected $signature = 'crud:generate-all';
    protected $description = 'Generate CRUD views and controllers for all Admin API resources';

    // Map of resources to generate
    protected $resources = [
        'Service' => ['name_ar', 'name_en', 'price', 'image'],
        'Order' => ['user_id', 'service_id', 'status', 'total_price'],
        'Technician' => ['user_id', 'service_id', 'status', 'experience_years'],
        'MaintenanceCompany' => ['name_ar', 'name_en', 'email', 'phone'],
        'City' => ['name_ar', 'name_en', 'region'],
        'District' => ['name_ar', 'name_en', 'city_id'],
        'CorporateCustomer' => ['company_name', 'email', 'phone', 'address'],
        'IndividualCustomer' => ['name', 'email', 'phone', 'address'],
        'Contract' => ['order_id', 'start_date', 'end_date', 'total_amount'],
        'Payment' => ['order_id', 'amount', 'payment_method', 'status'],
        'Invoice' => ['order_id', 'amount', 'status', 'due_date'],
        'FinancialSettlement' => ['technician_id', 'amount', 'status', 'settled_at'],
        'PlatformProfit' => ['order_id', 'amount', 'percentage'],
        'Refund' => ['payment_id', 'amount', 'reason', 'status'],
        'Appointment' => ['order_id', 'scheduled_at', 'status'],
        'Review' => ['order_id', 'user_id', 'rating', 'comment'],
        'Complaint' => ['user_id', 'order_id', 'subject', 'status'],
        'TechnicianRequest' => ['user_id', 'service_id', 'status'],
        'Inquiry' => ['name', 'email', 'subject', 'message'],
        'Content' => ['title_ar', 'title_en', 'content_ar', 'content_en'],
        'Faq' => ['question_ar', 'question_en', 'answer_ar', 'answer_en'],
        'Term' => ['title_ar', 'title_en', 'content_ar', 'content_en'],
        'PrivacyPolicy' => ['title_ar', 'title_en', 'content_ar', 'content_en'],
        'SocialLink' => ['platform', 'url', 'icon'],
        'Setting' => ['key', 'value', 'type'],
        'Inventory' => ['name_ar', 'name_en', 'quantity', 'price'],
        'Role' => ['name_ar', 'name_en', 'description'],
        'Permission' => ['name', 'description'],
    ];

    public function handle()
    {
        $this->info('🚀 Starting CRUD generation for all resources...');
        
        $bar = $this->output->createProgressBar(count($this->resources));
        $bar->start();

        foreach ($this->resources as $model => $fields) {
            $this->generateCrud($model, $fields);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ All CRUD interfaces generated successfully!');
        $this->info('📝 Don\'t forget to add routes to routes/web.php');
        
        return Command::SUCCESS;
    }

    protected function generateCrud($model, $fields)
    {
        $modelLower = Str::snake($model);
        $modelPlural = Str::plural($modelLower);
        $modelTitle = Str::title(Str::replace('_', ' ', $modelLower));

        // Create controller
        $this->generateController($model, $fields);
        
        // Create views directory
        $viewsPath = resource_path("views/admin/{$modelPlural}");
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        // Generate views
        $this->generateIndexView($model, $fields, $viewsPath);
        $this->generateCreateView($model, $fields, $viewsPath);
        $this->generateEditView($model, $fields, $viewsPath);
        $this->generateShowView($model, $fields, $viewsPath);
    }

    protected function generateController($model, $fields)
    {
        $modelLower = Str::snake($model);
        $modelPlural = Str::plural($modelLower);
        $controllerName = "{$model}ManagementController";
        
        $stub = <<<PHP
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\\{$model};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class {$controllerName} extends Controller
{
    public function index(Request \$request)
    {
        \$query = {$model}::query();

        if (\$request->has('search') && \$request->search) {
            \$query->where('{$fields[0]}', 'like', '%' . \$request->search . '%');
        }

        \$items = \$query->orderBy('created_at', 'desc')->paginate(25);
        return view('admin.{$modelPlural}.index', compact('items'));
    }

    public function create()
    {
        return view('admin.{$modelPlural}.create');
    }

    public function store(Request \$request)
    {
        \$validated = \$request->validate([
            // Add validation rules
        ]);

        {$model}::create(\$validated);
        return redirect()->route('admin.{$modelPlural}.index')->with('success', __('Item created successfully.'));
    }

    public function show(\$id)
    {
        \$item = {$model}::findOrFail(\$id);
        return view('admin.{$modelPlural}.show', compact('item'));
    }

    public function edit(\$id)
    {
        \$item = {$model}::findOrFail(\$id);
        return view('admin.{$modelPlural}.edit', compact('item'));
    }

    public function update(Request \$request, \$id)
    {
        \$item = {$model}::findOrFail(\$id);
        \$validated = \$request->validate([
            // Add validation rules
        ]);

        \$item->update(\$validated);
        return redirect()->route('admin.{$modelPlural}.index')->with('success', __('Item updated successfully.'));
    }

    public function destroy(\$id)
    {
        \$item = {$model}::findOrFail(\$id);
        \$item->delete();
        return redirect()->route('admin.{$modelPlural}.index')->with('success', __('Item deleted successfully.'));
    }
}
PHP;

        $path = app_path("Http/Controllers/Admin/{$controllerName}.php");
        File::put($path, $stub);
    }

    protected function generateIndexView($model, $fields, $viewsPath)
    {
        $modelLower = Str::snake($model);
        $modelPlural = Str::plural($modelLower);
        $modelTitle = Str::title(Str::replace('_', ' ', $model));

        $stub = <<<'BLADE'
@extends('layouts.admin')

@section('title', __('{{MODEL_TITLE}} Management'))
@section('page_title', __('{{MODEL_TITLE}} Management'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('All {{MODEL_TITLE}}s') }}</h2>
        <a href="{{ route('admin.{{MODEL_PLURAL}}.create') }}" class="px-6 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
            {{ __('Add New') }}
        </a>
    </div>

    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-slate-400 text-xs font-black uppercase border-b border-slate-100 dark:border-white/5">
                        <th class="pb-4 px-6">{{ __('ID') }}</th>
                        {{TABLE_HEADERS}}
                        <th class="pb-4 px-6 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-xs font-bold text-slate-600 dark:text-white/70">
                    @forelse($items as $item)
                    <tr class="border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5">
                        <td class="py-4 px-6">#{{ $item->id }}</td>
                        {{TABLE_CELLS}}
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.{{MODEL_PLURAL}}.show', $item->id) }}" class="p-2 rounded-lg hover:bg-blue-50 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </a>
                                <a href="{{ route('admin.{{MODEL_PLURAL}}.edit', $item->id) }}" class="p-2 rounded-lg hover:bg-yellow-50 text-yellow-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.{{MODEL_PLURAL}}.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600" onclick="return confirm('Are you sure?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">{{ __('No items found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="p-6 border-t border-slate-100 dark:border-white/5">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
BLADE;

        // Generate table headers and cells
        $headers = '';
        $cells = '';
        foreach (array_slice($fields, 0, 4) as $field) {
            $fieldTitle = Str::title(str_replace('_', ' ', $field));
            $headers .= "\n                        <th class=\"pb-4 px-6\">{{ __('{$fieldTitle}') }}</th>";
            $cells .= "\n                        <td class=\"py-4 px-6\">{{ \$item->{$field} }}</td>";
        }

        $stub = str_replace('{{MODEL_TITLE}}', $modelTitle, $stub);
        $stub = str_replace('{{MODEL_PLURAL}}', $modelPlural, $stub);
        $stub = str_replace('{{TABLE_HEADERS}}', $headers, $stub);
        $stub = str_replace('{{TABLE_CELLS}}', $cells, $stub);

        File::put("{$viewsPath}/index.blade.php", $stub);
    }

    protected function generateCreateView($model, $fields, $viewsPath)
    {
        $modelPlural = Str::plural(Str::snake($model));
        $stub = "@extends('layouts.admin')\n@section('content')\n<h1>Create {$model}</h1>\n<!-- Add form here -->\n@endsection";
        File::put("{$viewsPath}/create.blade.php", $stub);
    }

    protected function generateEditView($model, $fields, $viewsPath)
    {
        $modelPlural = Str::plural(Str::snake($model));
        $stub = "@extends('layouts.admin')\n@section('content')\n<h1>Edit {$model}</h1>\n<!-- Add form here -->\n@endsection";
        File::put("{$viewsPath}/edit.blade.php", $stub);
    }

    protected function generateShowView($model, $fields, $viewsPath)
    {
        $modelPlural = Str::plural(Str::snake($model));
        $stub = "@extends('layouts.admin')\n@section('content')\n<h1>{$model} Details</h1>\n<!-- Add details here -->\n@endsection";
        File::put("{$viewsPath}/show.blade.php", $stub);
    }
}

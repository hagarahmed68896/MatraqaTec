<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

trait HasCrudOperations
{
    /**
     * Display a listing of the resource with pagination and search.
     */
    public function index(Request $request)
    {
        $query = $this->getModel()::query();

        // Apply search if provided
        if ($request->has('search') && $request->search) {
            $query = $this->applySearch($query, $request->search);
        }

        // Apply filters if provided
        if ($request->has('filters')) {
            $query = $this->applyFilters($query, $request->filters);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 25);
        $items = $query->paginate($perPage);

        return view($this->getViewPath('index'), [
            'items' => $items,
            'search' => $request->search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->getViewPath('create'), [
            'item' => $this->getModel(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());
        
        $item = $this->getModel()::create($validated);

        return redirect()
            ->route($this->getRouteName('index'))
            ->with('success', __('Item created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = $this->getModel()::findOrFail($id);

        return view($this->getViewPath('show'), [
            'item' => $item,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = $this->getModel()::findOrFail($id);

        return view($this->getViewPath('edit'), [
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->getValidationRules($id));
        
        $item = $this->getModel()::findOrFail($id);
        $item->update($validated);

        return redirect()
            ->route($this->getRouteName('index'))
            ->with('success', __('Item updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = $this->getModel()::findOrFail($id);
        $item->delete();

        return redirect()
            ->route($this->getRouteName('index'))
            ->with('success', __('Item deleted successfully.'));
    }

    /**
     * Bulk delete multiple resources.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->getModel()::whereIn('id', $ids)->delete();

        return redirect()
            ->route($this->getRouteName('index'))
            ->with('success', __('Items deleted successfully.'));
    }

    /**
     * Get the model class name.
     * Must be implemented by the controller.
     */
    abstract protected function getModel(): string;

    /**
     * Get the view path prefix.
     * Must be implemented by the controller.
     */
    abstract protected function getViewPath(string $view): string;

    /**
     * Get the route name prefix.
     * Must be implemented by the controller.
     */
    abstract protected function getRouteName(string $action): string;

    /**
     * Get validation rules for create/update.
     * Must be implemented by the controller.
     */
    abstract protected function getValidationRules($id = null): array;

    /**
     * Apply search query.
     * Can be overridden by the controller.
     */
    protected function applySearch($query, string $search)
    {
        return $query;
    }

    /**
     * Apply filters to query.
     * Can be overridden by the controller.
     */
    protected function applyFilters($query, array $filters)
    {
        return $query;
    }
}

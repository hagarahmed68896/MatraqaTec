<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        $items = Inquiry::with('user')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.inquiries.index', compact('items'));
    }

    public function store(Request $request)
    {
        Inquiry::create($request->all());
        return redirect()->route('admin.inquiries.index')->with('success', __('Inquiry submitted successfully.'));
    }

    public function show($id)
    {
        $item = Inquiry::findOrFail($id);
        return view('admin.inquiries.show', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $inquiry = Inquiry::findOrFail($id);
        $inquiry->update($request->all());
        return back()->with('success', __('Inquiry updated successfully.'));
    }

    public function destroy($id)
    {
        $inquiry = Inquiry::findOrFail($id);
        $inquiry->delete();
        return redirect()->route('admin.inquiries.index')->with('success', __('Inquiry deleted successfully.'));
    }
}

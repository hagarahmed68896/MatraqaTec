@extends('layouts.admin')

@section('title', __('Edit Order') . ' #' . ($item->order_number ?? $item->id))

@section('content')
<div class="space-y-8 pb-20" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.orders.show', $item->id) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 text-[#1A1A31] dark:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() == 'ar' ? 'M14 5l7 7m0 0l-7 7m7-7H3' : 'M10 19l-7-7m0 0l7-7m-7 7h18' }}"></path></svg>
        </a>
        <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Edit Order') }} - #{{ $item->order_number ?? $item->id }}</h1>
    </div>

    <!-- Edit Form Card -->
    <div class="max-w-4xl mx-auto" x-data="{
        basePrice: {{ $item->original_price_before_tax }},
        discount: {{ $item->discount_amount }},
        get grandTotal() {
            let total = (parseFloat(this.basePrice) - parseFloat(this.discount)) * 1.15;
            return total > 0 ? total.toFixed(2) : '0.00';
        }
    }">
        <form action="{{ route('admin.orders.update', $item->id) }}" method="POST" class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] border border-slate-50 dark:border-white/5 shadow-sm p-10 space-y-8">
            @csrf
            @method('PUT')

            <!-- Form Error Alert -->
            @if ($errors->any())
                <div class="p-6 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 text-rose-600 text-sm font-bold">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Customer (User) Selection -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Customer') }} <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="h-12 px-4 ltr:pr-10 rtl:pl-10 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Service Selection -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Service') }} <span class="text-rose-500">*</span></label>
                    <select name="service_id" required class="h-12 px-4 ltr:pr-10 rtl:pl-10 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->service_id == $srv->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? $srv->name_ar : $srv->name_en }}@if($srv->parent) ({{ app()->getLocale() == 'ar' ? $srv->parent->name_ar : $srv->parent->name_en }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Selection -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Status') }} <span class="text-rose-500">*</span></label>
                    <select name="status" required class="h-12 px-4 ltr:pr-10 rtl:pl-10 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">
                        <option value="new" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'new' ? 'selected' : '' }}>{{ __('New') }}</option>
                        <option value="accepted" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'accepted' ? 'selected' : '' }}>{{ __('Accepted') }}</option>
                        <option value="scheduled" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                        <option value="in_progress" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                        <option value="completed" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        <option value="rejected" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                        <option value="cancelled" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->status == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>

                <!-- Scheduled At (Date & Time) -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Scheduled At') }}</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ $item->scheduled_at ? $item->scheduled_at->format('Y-m-d\TH:i') : '' }}" class="h-12 px-4 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">
                </div>
            </div>

            <!-- Description / Notes -->
            <div class="flex flex-col gap-2">
                <label class="text-sm font-bold text-[#1A1A31] dark:text-white">{{ __('Description') }}</label>
                <textarea name="notes" rows="4" class="p-4 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">{{ $item->notes }}</textarea>
            </div>

            <!-- Pricing Details -->
            <div class="p-8 rounded-[2rem] bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 space-y-6">
                <h3 class="text-md font-black text-[#1A1A31] dark:text-white">{{ __('Pricing and Discounts') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Base Price (Excluding VAT) -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Price Before Tax (Excl. VAT)') }}</label>
                        <div class="relative flex items-center">
                            <input type="number" step="0.01" name="base_price" x-model="basePrice"
                                   class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right text-[#1A1A31] dark:text-white">
                            <div class="absolute left-0 top-0 bottom-0 px-4 flex items-center justify-center bg-slate-100 dark:bg-white/10 rounded-l-2xl border-l border-slate-200 dark:border-white/10 select-none">
                                <span class="text-xs font-black text-slate-500 dark:text-slate-400">ر.س</span>
                            </div>
                        </div>
                    </div>

                    <!-- Discount (Excluding VAT) -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Discount Amount') }}</label>
                        <div class="relative flex items-center">
                            <input type="number" step="0.01" name="discount" x-model="discount"
                                   class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right text-[#1A1A31] dark:text-white">
                            <div class="absolute left-0 top-0 bottom-0 px-4 flex items-center justify-center bg-slate-100 dark:bg-white/10 rounded-l-2xl border-l border-slate-200 dark:border-white/10 select-none">
                                <span class="text-xs font-black text-slate-500 dark:text-slate-400">ر.س</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calculated Grand Total (Readonly) -->
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Grand Total (Incl. 15% VAT)') }}</label>
                        <div class="relative flex items-center">
                            <input type="text" readonly :value="grandTotal"
                                   class="w-full pl-12 pr-5 py-4 bg-slate-100/50 dark:bg-white/5 border border-transparent rounded-2xl text-sm font-black text-primary dark:text-white focus:outline-none select-none text-right">
                            <div class="absolute left-0 top-0 bottom-0 px-4 flex items-center justify-center bg-slate-100 dark:bg-white/10 rounded-l-2xl border-l border-slate-200 dark:border-white/10 select-none">
                                <span class="text-xs font-black text-primary dark:text-white">ر.س</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technician & Company Assignment -->
            <div class="p-8 rounded-[2rem] bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 space-y-6">
                <h3 class="text-md font-black text-[#1A1A31] dark:text-white">{{ __('Assignment') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Independent Technician -->
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Technician') }}</label>
                        <select name="technician_id" class="h-12 px-4 ltr:pr-10 rtl:pl-10 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">
                            <option value="" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white">{{ __('No Technician Assigned') }}</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->technician_id == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->user->name ?? $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Corporate Maintenance Company -->
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('Maintenance Company') }}</label>
                        <select name="maintenance_company_id" class="h-12 px-4 ltr:pr-10 rtl:pl-10 rounded-xl border border-slate-200 dark:border-white/5 dark:bg-white/5 text-sm font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-[#1A1A31] dark:text-white">
                            <option value="" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white">{{ __('No Company Assigned') }}</option>
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" class="bg-white dark:bg-[#1A1A31] text-[#1A1A31] dark:text-white" {{ $item->maintenance_company_id == $comp->id ? 'selected' : '' }}>
                                    {{ $comp->user->name ?? $comp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex justify-end gap-4 pt-6 border-t border-slate-100 dark:border-white/5">
                <a href="{{ route('admin.orders.show', $item->id) }}" class="h-12 px-8 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-[#1A1A31] dark:text-white font-bold text-xs hover:bg-slate-200 dark:hover:bg-white/10 transition-all uppercase tracking-widest">{{ __('Cancel') }}</a>
                <button type="submit" class="h-12 px-10 rounded-xl bg-[#1A1A31] text-white font-bold text-xs shadow-lg shadow-[#1A1A31]/20 hover:scale-[1.02] transition-all uppercase tracking-widest">{{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
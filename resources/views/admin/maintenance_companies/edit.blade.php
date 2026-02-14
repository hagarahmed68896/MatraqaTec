@extends('layouts.admin')

@section('page_title', __('Edit Maintenance Company'))

@section('content')
<div class="max-w-4xl mx-auto pb-20">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('admin.maintenance-companies.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-primary transition-colors font-bold group">
            <div class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center border border-slate-100 dark:border-white/5 group-hover:bg-primary/10">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            {{ __('Back to Companies') }}
        </a>
    </div>

    <form action="{{ route('admin.maintenance-companies.update', $company->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Company Basic Info -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="mb-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-[1.5rem] bg-slate-100 dark:bg-white/10 flex items-center justify-center text-primary font-black text-xl uppercase">
                    {{ mb_substr($company->user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ $company->user->name }}</h3>
                    <p class="text-xs text-slate-400 font-bold mt-1">{{ __('Update company profile and contact info') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Company Name -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Company Name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $company->name ?? $company->company_name_ar) }}" required 
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Phone -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Phone Number') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->user->phone) }}" required placeholder="5XXXXXXXX"
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('phone') border-red-500 @enderror">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Email Address') }}</label>
                    <input type="email" name="email" value="{{ old('email', $company->user->email) }}" required 
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Password') }}</label>
                    <input type="password" name="password" placeholder="{{ __('Leave blank to keep current') }}"
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none @error('password') border-red-500 @enderror">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Business Details -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="mb-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Business Registration') }}</h3>
                    <p class="text-xs text-slate-400 font-bold mt-1">{{ __('Tax and commercial record information') }}</p>
                </div>
            </div>

            <div class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Tax Number') }}</label>
                        <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number) }}" 
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Commercial Record Number') }}</label>
                        <input type="text" name="commercial_record_number" value="{{ old('commercial_record_number', $company->commercial_record_number) }}" 
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                </div>

                <div class="space-y-3" x-data="{ fileName: '' }">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Commercial Register File') }}</label>
                    <div class="flex gap-4">
                        <div class="flex-1 bg-slate-50 dark:bg-white/5 border border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-400 truncate" x-text="fileName || '{{ $company->commercial_record_file ? basename($company->commercial_record_file) : __('No file selected') }}'"></div>
                        <label class="px-8 py-4 bg-slate-900 dark:bg-white/10 text-white rounded-2xl font-black hover:bg-black transition-all text-xs uppercase tracking-widest cursor-pointer whitespace-nowrap">
                            {{ __('Change File') }}
                            <input type="file" name="commercial_record_file" class="hidden" @change="fileName = $event.target.files[0].name">
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="mb-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Bank Account Details') }}</h3>
                    <p class="text-xs text-slate-400 font-bold mt-1">{{ __('Payment and settlement information') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Bank Name') }}</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $company->bank_name) }}" 
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                </div>
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Account Name') }}</label>
                    <input type="text" name="account_name" value="{{ old('account_name', $company->account_name) }}" 
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                </div>
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Account Number') }}</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $company->account_number) }}" 
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                </div>
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('IBAN') }}</label>
                    <input type="text" name="iban" value="{{ old('iban', $company->iban) }}" 
                           class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <button type="submit" class="flex-1 py-5 bg-primary text-white rounded-[2.5rem] font-black hover:bg-primary-dark transition-all shadow-xl shadow-primary/20 uppercase tracking-widest text-sm">
                {{ __('Update Company') }}
            </button>
            <a href="{{ route('admin.maintenance-companies.index') }}" class="px-12 py-5 bg-white dark:bg-white/5 text-slate-500 rounded-[2.5rem] font-black hover:bg-slate-50 transition-all border border-slate-100 dark:border-white/5 uppercase tracking-widest text-sm text-center">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
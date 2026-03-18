@extends('layouts.admin')

@section('title', __('Add Contract'))
@section('page_title', __('Add Contract'))

@section('content')
<div class="max-w-3xl mx-auto pb-20" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.contracts.index') }}"
           class="flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-[#1A1A31] dark:hover:text-white transition-colors font-bold group">
            <div class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center border border-slate-100 dark:border-white/10 group-hover:bg-slate-50 dark:hover:bg-white/10 shadow-sm transition-all">
                <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </div>
        </a>
        <div>
            <h1 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Add Contract') }} <span class="text-slate-300 dark:text-slate-600">←</span></h1>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="mb-6 bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 rounded-2xl p-5">
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm text-red-600 dark:text-red-400 font-bold flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.contracts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Main Card --}}
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 space-y-8">

            <div class="flex items-center gap-4 pb-6 border-b border-slate-50 dark:border-white/5">
                <div class="w-12 h-12 rounded-2xl bg-[#1A1A31]/5 dark:bg-white/5 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#1A1A31] dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('Add Contract Data') }}</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ __('Enter contract details') }}</p>
                </div>
            </div>

            {{-- Contract File Upload --}}
            <div class="space-y-3">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Contract File') }}</label>
                <div x-data="{ fileName: '', isDragging: false }"
                     class="relative"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="isDragging = false; fileName = $event.dataTransfer.files[0]?.name; $refs.fileInput.files = $event.dataTransfer.files">

                    <label :class="isDragging ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 hover:border-slate-300 dark:hover:border-white/20 hover:bg-slate-100 dark:hover:bg-white/10'"
                           class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-2xl cursor-pointer transition-all">
                        <input type="file" name="contract_file" accept=".pdf" x-ref="fileInput"
                               @change="fileName = $event.target.files[0]?.name"
                               class="hidden">

                        <template x-if="!fileName">
                            <div class="flex flex-col items-center gap-3 text-center px-6">
                                <div class="w-12 h-12 rounded-xl bg-white dark:bg-white/5 shadow-sm border border-slate-100 dark:border-white/10 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-600 dark:text-gray-300">{{ __('Choose file or drag and drop here') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold mt-1">{{ __('PDF format up to 5MB') }}</p>
                                </div>
                                <span class="px-5 py-2 bg-white dark:bg-white/10 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-black text-slate-600 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 transition-all shadow-sm">
                                    {{ __('Browse File') }}
                                </span>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center gap-3 px-6">
                                <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-[#1A1A31] dark:text-white truncate" x-text="fileName"></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold">{{ __('PDF File') }}</p>
                                </div>
                                <button type="button" @click.prevent="fileName = ''; $refs.fileInput.value = ''"
                                        class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </label>
                </div>
                @error('contract_file')
                    <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Company + Project Value --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Company --}}
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Maintenance Company') }}</label>
                    <div class="relative">
                        <select name="maintenance_company_id"
                                class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none appearance-none text-right @error('maintenance_company_id') border-red-400 @enderror dark:text-white">
                            <option value="">{{ __('Select Company') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('maintenance_company_id') == $company->id ? 'selected' : '' }} class="dark:bg-[#1A1A31]">
                                    {{ $company->company_name_ar ?? $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    @error('maintenance_company_id')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Corporate Customer --}}
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Corporate Customer') }} ({{ __('Optional') }})</label>
                    <div class="relative">
                        <select name="corporate_customer_id"
                                class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none appearance-none text-right @error('corporate_customer_id') border-red-400 @enderror dark:text-white">
                            <option value="">{{ __('Select Customer') }}</option>
                            @foreach($corporate_customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('corporate_customer_id') == $customer->id ? 'selected' : '' }} class="dark:bg-[#1A1A31]">
                                    {{ $customer->company_name_ar ?? $customer->company_name_en }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    @error('corporate_customer_id')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Project Value --}}
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Project Value') }}</label>
                    <div class="relative flex items-center">
                        <input type="number" name="project_value" value="{{ old('project_value') }}"
                               placeholder="{{ __('Enter project value') }}"
                               class="w-full pl-14 pr-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right dark:text-white @error('project_value') border-red-400 @enderror">
                        <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center bg-slate-100 dark:bg-white/5 rounded-r-2xl border-r border-slate-200 dark:border-white/10">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="SAR" class="w-5 h-5">
                        </div>
                    </div>
                    @error('project_value')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contract Number --}}
            <div class="space-y-3">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Contract Number') }}</label>
                <input type="text" name="contract_number" value="{{ old('contract_number') }}"
                       placeholder="{{ __('Enter contract number') }}"
                       class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right dark:text-white @error('contract_number') border-red-400 @enderror">
                @error('contract_number')
                    <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contact Numbers --}}
            <div class="space-y-3" x-data="{ phones: [''] }">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Contact Numbers') }}</label>
                <div class="space-y-3">
                    <template x-for="(phone, index) in phones" :key="index">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1 flex items-center">
                                <input type="text" :name="'contact_numbers[]'" x-model="phones[index]"
                                       placeholder="{{ __('Enter phone number') }}"
                                       class="w-full pl-5 pr-20 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none text-right dark:text-white">
                                <div class="absolute right-0 top-0 bottom-0 px-4 flex items-center justify-center bg-slate-100 dark:bg-white/5 rounded-l-2xl border-l border-slate-200 dark:border-white/10">
                                    <span class="text-xs font-black text-slate-500 dark:text-slate-400">+966</span>
                                </div>
                            </div>
                            <button type="button" x-show="index > 0" @click="phones.splice(index, 1)"
                                    class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="phones.push('')"
                        class="flex items-center gap-2 px-5 py-3 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-black text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-white/10 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add') }}
                </button>
                @error('contact_numbers')
                    <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Start & End Dates --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none dark:text-white @error('start_date') border-red-400 @enderror">
                    @error('start_date')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('End Date') }}</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}"
                           class="w-full px-5 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary dark:focus:border-white/20 focus:bg-white dark:focus:bg-white/10 rounded-2xl text-sm font-bold transition-all outline-none dark:text-white @error('end_date') border-red-400 @enderror">
                    @error('end_date')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contract Status --}}
            <div class="space-y-4" x-data="{ status: '{{ old('status') }}' }">
                <label class="block text-sm font-black text-[#1A1A31] dark:text-white text-right">{{ __('Contract Status') }}</label>
                <div class="p-5 bg-slate-50 dark:bg-white/5 rounded-2xl space-y-3">
                    @foreach(['active' => __('active'), 'expired' => __('expired'), 'completed' => __('completed')] as $val => $label)
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-bold text-slate-600 dark:text-gray-300 group-hover:text-[#1A1A31] dark:group-hover:text-white transition-colors">{{ $label }}</span>
                        <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center"
                             :class="status == '{{ $val }}' ? 'border-primary bg-primary' : 'border-slate-300 dark:border-white/20'">
                            <input type="radio" name="status" value="{{ $val }}"
                                   x-model="status"
                                   class="hidden">
                            <template x-if="status == '{{ $val }}'">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('status')
                    <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                    class="flex-1 py-5 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-[2rem] font-black text-sm shadow-lg shadow-[#1A1A31]/20 dark:shadow-white/5 hover:scale-[1.01] transition-all uppercase tracking-widest">
                {{ __('Add Contract') }}
            </button>
            <a href="{{ route('admin.contracts.index') }}"
               class="px-8 py-5 bg-white dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-[2rem] font-black text-sm border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest text-center">
                {{ __('Cancel') }}
            </a>
        </div>

    </form>
</div>
@endsection
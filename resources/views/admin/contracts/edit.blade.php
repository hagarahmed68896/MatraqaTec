@extends('layouts.admin')

@section('title', __('Edit Contract Data'))
@section('page_title', __('Edit Contract Data'))

@section('content')
<div class="max-w-3xl mx-auto pb-20" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.contracts.index') }}"
           class="flex items-center gap-2 text-slate-500 hover:text-[#1A1A31] transition-colors font-bold group">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center border border-slate-100 group-hover:bg-slate-50 shadow-sm">
                <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </div>
        </a>
        <div>
            <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('Edit Contract Data') }} <span class="text-slate-300">←</span></h1>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-100 rounded-2xl p-5">
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm text-red-600 font-bold flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.contracts.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Main Card --}}
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-50 space-y-8">

            <div class="flex items-center gap-4 pb-6 border-b border-slate-50">
                <div class="w-12 h-12 rounded-2xl bg-[#1A1A31]/5 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#1A1A31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-[#1A1A31]">{{ __('Edit Contract Data') }}</h3>
                    <p class="text-xs text-slate-400 font-bold mt-0.5">{{ __('Edit contract details') }}</p>
                </div>
            </div>

            {{-- Contract File --}}
            <div class="space-y-3"
                 x-data="{
                     hasExisting: {{ $item->contract_file ? 'true' : 'false' }},
                     existingFile: '{{ $item->contract_file ? basename($item->contract_file) : '' }}',
                     newFileName: '',
                     deleteFile: false,
                     isDragging: false
                 }">
                <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Contract File') }}</label>

                {{-- Existing File Display --}}
                <div x-show="hasExisting && !deleteFile && !newFileName"
                     class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-[#1A1A31] truncate" x-text="existingFile"></p>
                        <p class="text-xs text-slate-400 font-bold">{{ __('Current File') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($item->contract_file)
                        <a href="{{ asset('storage/' . $item->contract_file) }}" target="_blank"
                           class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-[#1A1A31] hover:border-slate-300 transition-all"
                           title="{{ __('View File') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        @endif
                        <button type="button" @click="deleteFile = true; hasExisting = false"
                                class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-500 hover:bg-red-100 transition-all"
                                title="{{ __('Delete File') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Upload New File Area --}}
                <div x-show="!hasExisting || deleteFile || newFileName"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="isDragging = false; newFileName = $event.dataTransfer.files[0]?.name; $refs.fileInput.files = $event.dataTransfer.files">

                    <label :class="isDragging ? 'border-[#1A1A31] bg-[#1A1A31]/5' : 'border-slate-200 bg-slate-50 hover:border-slate-300 hover:bg-slate-100'"
                           class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-2xl cursor-pointer transition-all">
                        <input type="file" name="contract_file" accept=".pdf" x-ref="fileInput"
                               @change="newFileName = $event.target.files[0]?.name; deleteFile = false"
                               class="hidden">

                        <template x-if="!newFileName">
                            <div class="flex flex-col items-center gap-2 text-center px-6">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-600">{{ __('Choose file or drag here') }}</p>
                                    <p class="text-xs text-slate-400 font-bold mt-0.5">{{ __('PDF format up to 5MB') }}</p>
                                </div>
                                <span class="px-4 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                                    {{ __('Browse File') }}
                                </span>
                            </div>
                        </template>

                        <template x-if="newFileName">
                            <div class="flex items-center gap-3 px-6">
                                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-[#1A1A31] truncate" x-text="newFileName"></p>
                                    <p class="text-xs text-green-500 font-bold">{{ __('New File') }}</p>
                                </div>
                                <button type="button"
                                        @click.prevent="newFileName = ''; $refs.fileInput.value = ''; hasExisting = {{ $item->contract_file ? 'true' : 'false' }}; deleteFile = false"
                                        class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500 hover:bg-red-100 transition-all flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </label>
                </div>

                {{-- Hidden input to signal file deletion --}}
                <input type="hidden" name="delete_contract_file" :value="deleteFile ? '1' : '0'">

                @error('contract_file')
                    <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Company + Project Value --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Company --}}
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Maintenance Company') }}</label>
                    <div class="relative">
                        <select name="maintenance_company_id"
                                class="w-full px-5 py-4 bg-slate-50 border border-transparent focus:border-[#1A1A31] focus:bg-white rounded-2xl text-sm font-bold transition-all outline-none appearance-none text-right @error('maintenance_company_id') border-red-400 @enderror">
                            <option value="">{{ __('Select Company') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ (old('maintenance_company_id', $item->maintenance_company_id) == $company->id) ? 'selected' : '' }}>
                                    {{ $company->company_name_ar ?? $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    @error('maintenance_company_id')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Project Value --}}
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Project Value') }}</label>
                    <div class="relative flex items-center">
                        <input type="number" name="project_value"
                               value="{{ old('project_value', $item->project_value) }}"
                               placeholder="{{ __('Enter project value') }}"
                               class="w-full pl-14 pr-5 py-4 bg-slate-50 border border-transparent focus:border-[#1A1A31] focus:bg-white rounded-2xl text-sm font-bold transition-all outline-none text-right @error('project_value') border-red-400 @enderror">
                        <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center bg-slate-100 rounded-r-2xl border-r border-slate-200">
                            <img src="{{ asset('assets/images/Vector (1).svg') }}" alt="{{ __('SAR') }}" class="w-5 h-5">
                        </div>
                    </div>
                    @error('project_value')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contract Number --}}
            <div class="space-y-3">
                <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Contract Number') }}</label>
                <input type="text" name="contract_number"
                       value="{{ old('contract_number', $item->contract_number) }}"
                       placeholder="{{ __('Enter contract number') }}"
                       class="w-full px-5 py-4 bg-slate-50 border border-transparent focus:border-[#1A1A31] focus:bg-white rounded-2xl text-sm font-bold transition-all outline-none text-right @error('contract_number') border-red-400 @enderror">
                @error('contract_number')
                    <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contact Numbers --}}
            @php
                $existingPhones = $item->contact_numbers
                    ? (is_array($item->contact_numbers) ? $item->contact_numbers : explode(',', $item->contact_numbers))
                    : [''];
                $phonesJson = json_encode(array_values(array_filter($existingPhones)) ?: ['']);
            @endphp
            <div class="space-y-3" x-data="{ phones: {{ $phonesJson }} }">
                <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Contact Numbers') }}</label>
                <template x-for="(phone, index) in phones" :key="index">
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 flex items-center">
                            <input type="text" :name="'contact_numbers[]'" x-model="phones[index]"
                                   placeholder="{{ __('Enter phone number') }}"
                                   class="w-full pl-5 pr-20 py-4 bg-slate-50 border border-transparent focus:border-[#1A1A31] focus:bg-white rounded-2xl text-sm font-bold transition-all outline-none text-right">
                            <div class="absolute right-0 top-0 bottom-0 px-4 flex items-center justify-center bg-slate-100 rounded-l-2xl border-l border-slate-200">
                                <span class="text-xs font-black text-slate-500">+966</span>
                            </div>
                        </div>
                        <button type="button" x-show="index > 0" @click="phones.splice(index, 1)"
                                class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500 hover:bg-red-100 transition-all flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="phones.push('')"
                        class="flex items-center gap-2 px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add') }}
                </button>
            </div>

            {{-- Start & End Dates --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date"
                           value="{{ old('start_date', $item->start_date?->format('Y-m-d')) }}"
                           class="w-full px-5 py-4 bg-slate-50 border border-transparent focus:border-[#1A1A31] focus:bg-white rounded-2xl text-sm font-bold transition-all outline-none @error('start_date') border-red-400 @enderror">
                    @error('start_date')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-3">
                    <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('End Date') }}</label>
                    <input type="date" name="end_date"
                           value="{{ old('end_date', $item->end_date?->format('Y-m-d')) }}"
                           class="w-full px-5 py-4 bg-slate-50 border border-transparent focus:border-[#1A1A31] focus:bg-white rounded-2xl text-sm font-bold transition-all outline-none @error('end_date') border-red-400 @enderror">
                    @error('end_date')
                        <p class="text-red-500 text-xs font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contract Status --}}
            <div class="space-y-4">
                <label class="block text-sm font-black text-[#1A1A31] text-right">{{ __('Contract Status') }}</label>
                <div class="p-5 bg-slate-50 rounded-2xl space-y-3">
                    @foreach(['active' => __('active'), 'expired' => __('expired'), 'completed' => __('completed')] as $val => $label)
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-sm font-bold text-slate-600 group-hover:text-[#1A1A31] transition-colors">{{ $label }}</span>
                        <div class="relative w-5 h-5 border-2 rounded-full transition-all flex items-center justify-center
                                    {{ old('status', $item->status) == $val ? 'border-[#1A1A31] bg-[#1A1A31]' : 'border-slate-300' }}">
                            <input type="radio" name="status" value="{{ $val }}"
                                   {{ old('status', $item->status) == $val ? 'checked' : '' }}
                                   class="hidden">
                            @if(old('status', $item->status) == $val)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
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
                    class="flex-1 py-5 bg-[#1A1A31] text-white rounded-[2rem] font-black text-sm shadow-lg shadow-[#1A1A31]/20 hover:scale-[1.01] transition-all uppercase tracking-widest">
                {{ __('Save') }}
            </button>
            <a href="{{ route('admin.contracts.index') }}"
               class="px-8 py-5 bg-white text-slate-500 rounded-[2rem] font-black text-sm border border-slate-200 hover:bg-slate-50 transition-all uppercase tracking-widest text-center">
                {{ __('Cancel') }}
            </a>
        </div>

    </form>
</div>
@endsection
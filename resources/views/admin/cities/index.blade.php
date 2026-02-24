@extends('layouts.admin')

@section('title', __('Cities Management'))
@section('page_title', __('Cities Management'))

@section('content')
<div class="space-y-8" dir="rtl" x-data="{ 
    addModal: false, 
    editModal: false,
    addDistricts: [{ name_ar: '', name_en: '' }],
    editData: { id: '', name_ar: '', name_en: '', districts: [] },
    
    addDistrictRow() {
        this.addDistricts.push({ name_ar: '', name_en: '' });
    },
    removeDistrictRow(index) {
        this.addDistricts.splice(index, 1);
    },
    addEditDistrictRow() {
        this.editData.districts.push({ id: null, name_ar: '', name_en: '' });
    },
    removeEditDistrictRow(index) {
        this.editData.districts.splice(index, 1);
    },
    openEdit(item, districts) {
        this.editData = { 
            id: item.id, 
            name_ar: item.name_ar, 
            name_en: item.name_en,
            districts: JSON.parse(districts)
        };
        this.editModal = true;
    }
}">
    {{-- Header & Search (RTL Corrected) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <h2 class="text-2xl font-black text-[#1A1A31] dark:text-white">{{ __('Cities Management') }}</h2>

        <div class="flex items-center gap-4">
            <form action="{{ route('admin.cities.index') }}" method="GET" class="relative group flex-1 md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search city name...') }}" class="w-full pr-12 pl-6 py-4 rounded-2xl bg-white dark:bg-[#1A1A31] border border-slate-100 dark:border-white/5 text-sm font-bold text-[#1A1A31] dark:text-white focus:ring-4 focus:ring-[#1A1A31]/5 transition-all outline-none shadow-sm text-right">
                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-[#1A1A31] dark:group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>

            <button @click="addModal = true" class="px-8 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Add City') }}
            </button>
        </div>
    </div>

    {{-- Cities Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 shadow-sm border border-slate-50 dark:border-white/5 space-y-6 relative group hover:shadow-xl hover:shadow-[#1A1A31]/5 transition-all duration-500 text-right">
            <div class="flex flex-col items-center text-center space-y-2">
                <h4 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}</h4>
                <div class="w-12 h-1 bg-[#F1F3F9] dark:bg-white/5 rounded-full"></div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-y-6 pt-2 border-t border-slate-50 dark:border-white/5">
                {{-- Companies --}}
                <div class="flex items-center gap-3 text-right">
                    <div class="w-10 h-10 rounded-xl bg-[#F8F9FE] dark:bg-white/5 flex items-center justify-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px]  text-[#1A1A31] dark:text-white leading-tight">{{ $item->companies_count }} {{ __('Companies') }}</p>
                    </div>
                </div>

                {{-- Users --}}
                <div class="flex items-center gap-3 justify-end text-left">
                 
                    <div class="w-10 h-10 rounded-xl bg-[#F8F9FE] dark:bg-white/5 flex items-center justify-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                       <div>
                        <p class="text-[11px]  text-[#1A1A31] dark:text-white text-right leading-tight">{{ $item->users_count }} {{ __('Total Users') }}</p>
                    </div>
                </div>

                {{-- Orders --}}
                <div class="flex items-center gap-3 text-right">
                    <div class="w-10 h-10 rounded-xl bg-[#F8F9FE] dark:bg-white/5 flex items-center justify-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px]  text-[#1A1A31] dark:text-white leading-tight">{{ $item->orders_count }} {{ __('Orders') }}</p>
                    </div>
                </div>

                {{-- Services --}}
                <div class="flex items-center gap-3 justify-end text-left">
                  <div class="w-10 h-10 rounded-xl bg-[#F8F9FE] dark:bg-white/5 flex items-center justify-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>    
                <div>
                        <p class="text-[11px]  text-[#1A1A31] dark:text-white text-right leading-tight">{{ $item->services_count }} {{ __('Services') }}</p>
                    </div>
                  
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center gap-3 pt-2">
              
                <button @click="openEdit({ id: '{{ $item->id }}', name_ar: '{{ $item->name_ar }}', name_en: '{{ $item->name_en }}' }, '{{ json_encode($item->districts) }}')" class="flex-1 px-8 h-12 flex items-center justify-center gap-3 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm hover:scale-[1.02] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    {{ __('Edit') }}
                </button>
                  <form action="{{ route('admin.cities.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-[#F1F3F9] dark:bg-white/5 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 bg-white dark:bg-[#1A1A31] rounded-[2.5rem] text-center border border-slate-50 dark:border-white/5">
            <div class="w-20 h-20 bg-[#F8F9FE] dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <h5 class="text-lg font-black text-[#1A1A31] dark:text-white">{{ __('No cities found') }}</h5>
            <p class="text-sm font-bold text-slate-400 mt-2">{{ __('Try adjusting your search filters') }}</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="flex items-center justify-center pt-8">
        {{ $items->links('vendor.pagination.tailwind') }}
    </div>
    @endif

    {{-- Add City Modal --}}
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div x-show="addModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @click="addModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <div x-show="addModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-[#1A1A31] rounded-[2.5rem] w-full max-w-2xl shadow-2xl border border-white/5 overflow-hidden z-20">
            <form action="{{ route('admin.cities.store') }}" method="POST" class="p-10 space-y-8">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-[#1A1A31] dark:text-white">{{ __('Add New City') }}</h3>
                    <button type="button" @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('City Name (Arabic)') }}</label>
                            <input type="text" name="name_ar" required placeholder="{{ __('Enter city name in Arabic') }}" class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-right">
                        </div>
                        <div class="space-y-3">
                            <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('City Name (English)') }}</label>
                            <input type="text" name="name_en" required placeholder="{{ __('Enter city name in English') }}" class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left">
                        </div>
                    </div>

                    {{-- Districts Section --}}
                    <div class="space-y-4">
                        <template x-for="(district, index) in addDistricts" :key="index">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="index === 0">
                                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Region Name (Arabic)') }}</label>
                                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('Region Name (English)') }}</label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="addDistrictRow()" x-show="index === 0" class="px-6 h-14 flex items-center justify-center rounded-2xl bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] hover:scale-105 transition-all font-black text-sm whitespace-nowrap">
                                        {{ __('Add') }}
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1">
                                        <input type="text" :name="'districts['+index+'][name_ar]'" required x-model="district.name_ar" placeholder="{{ __('Enter region name') }}" class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-right">
                                        <input type="text" :name="'districts['+index+'][name_en]'" required x-model="district.name_en" placeholder="{{ __('Enter region name') }}" class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left">
                                    </div>
                                    <button type="button" @click="removeDistrictRow(index)" x-show="addDistricts.length > 1" class="w-14 h-14 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" @click="addModal = false" class="px-10 py-4 bg-[#F1F3F9] dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-sm hover:bg-[#E2E6F0] transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit City Modal --}}
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div x-show="editModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @click="editModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <div x-show="editModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-[#1A1A31] rounded-[2.5rem] w-full max-w-2xl shadow-2xl border border-white/5 overflow-hidden z-20">
            <form :action="'{{ url('admin/cities') }}/' + editData.id" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-[#1A1A31] dark:text-white">{{ __('Update City Data') }}</h3>
                    <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3 text-right">
                            <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('City Name (Arabic)') }}</label>
                            <input type="text" name="name_ar" x-model="editData.name_ar" required class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-right">
                        </div>
                        <div class="space-y-3 text-right">
                            <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('City Name (English)') }}</label>
                            <input type="text" name="name_en" x-model="editData.name_en" required class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left">
                        </div>
                    </div>

                    {{-- Districts Section --}}
                    <div class="space-y-4">
                        <template x-for="(district, index) in editData.districts" :key="index">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="index === 0">
                                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300">{{ __('Region Name (Arabic)') }}</label>
                                    <label class="block text-sm font-black text-[#1A1A31] dark:text-slate-300 text-left">{{ __('Region Name (English)') }}</label>
                                </div>
                                <div class="flex items-center gap-3 text-right">
                                    <input type="hidden" :name="'districts['+index+'][id]'" :value="district.id">
                                    <button type="button" @click="addEditDistrictRow()" x-show="index === 0" class="px-6 h-14 flex items-center justify-center rounded-2xl bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] hover:scale-105 transition-all font-black text-sm whitespace-nowrap">
                                        {{ __('Add') }}
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1">
                                        <input type="text" :name="'districts['+index+'][name_ar]'" required x-model="district.name_ar" placeholder="{{ __('Enter region name') }}" class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-right">
                                        <input type="text" :name="'districts['+index+'][name_en]'" required x-model="district.name_en" placeholder="{{ __('Enter region name') }}" class="w-full px-6 py-4 rounded-2xl bg-[#F8F9FE] dark:bg-white/5 border border-slate-100 dark:border-white/10 text-sm font-bold text-[#1A1A31] dark:text-white outline-none focus:ring-4 focus:ring-[#1A1A31]/5 transition-all text-left">
                                    </div>
                                    <button type="button" @click="removeEditDistrictRow(index)" x-show="editData.districts.length > 1" class="w-14 h-14 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-red-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.895-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" @click="editModal = false" class="px-10 py-4 bg-[#F1F3F9] dark:bg-white/5 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-sm hover:bg-[#E2E6F0] transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-12 py-4 bg-[#1A1A31] dark:bg-white text-white dark:text-[#1A1A31] rounded-2xl font-black text-sm shadow-xl shadow-[#1A1A31]/10 dark:shadow-white/5 hover:scale-[1.02] transition-all">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
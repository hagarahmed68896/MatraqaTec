@extends('layouts.admin')

@section('content')
<div x-data="{ 
    selectAll: false,
    permissions: [],
    toggleAll() {
        if (this.selectAll) {
            this.permissions = Array.from(document.querySelectorAll('input[name=\'permissions[]\']')).map(el => el.value);
        } else {
            this.permissions = [];
        }
    }
}" class="pb-20 text-right" dir="rtl">

    <div class="flex items-center justify-between mb-8 px-4">
        <div class="flex items-center gap-3">
             <h1 class="text-2xl font-bold text-[#1A1A31]">{{ __('إضافة دور') }}</h1>
             <a href="{{ route('admin.roles.index') }}" class="text-slate-400 hover:text-primary transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        
        <div class="space-y-6 px-4">
            <div class="bg-white rounded-3xl p-10 shadow-sm border border-slate-50">
                <div class="mb-8 border-b border-slate-50 pb-4">
                    <h2 class="text-lg font-bold text-slate-800">{{ __('بيانات الدور') }}</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 block">{{ __('اسم الدور (اللغة العربية)') }}</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar') }}" required 
                               placeholder="{{ __('أدخل اسم الدور') }}"
                               class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:border-primary text-right placeholder-slate-400">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 block">{{ __('اسم الدور (اللغة الإنجليزية)') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}" required 
                               placeholder="{{ __('أدخل اسم الدور') }}"
                               class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:border-primary text-right placeholder-slate-400">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 block">{{ __('الوصف (اللغة العربية)') }}</label>
                        <textarea name="description_ar" rows="3" placeholder="{{ __('أدخل الوصف') }}"
                                  class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:border-primary text-right placeholder-slate-400 h-28">{{ old('description_ar') }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 block">{{ __('الوصف (اللغة الإنجليزية)') }}</label>
                        <textarea name="description_en" rows="3" placeholder="{{ __('أدخل الوصف') }}"
                                  class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:outline-none focus:border-primary text-right placeholder-slate-400 h-28">{{ old('description_en') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-10 shadow-sm border border-slate-50">
                <div class="flex items-center justify-end mb-8 gap-3">
                    <span class="text-sm font-bold text-slate-700">{{ __('تحديد الكل') }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="sr-only peer">
                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[-1.5rem] after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1A1A31]"></div>
                    </label>
                </div>

                <div class="space-y-0 border border-slate-100 rounded-2xl overflow-hidden">
                    @php
                        // مثال للقسم الأول كما في الصورة (إدارة العملاء مثلاً)
                        $actionsRow1 = ['عرض', 'إضافة', 'تعديل', 'حذف'];
                        $actionsRow2 = ['حظر', 'الايقاف', 'تفعيل', 'تحميل'];
                    @endphp

                    <div class="grid grid-cols-4 bg-slate-50 border-b border-slate-100 py-4 px-6">
                        @foreach($actionsRow1 as $action)
                            <div class="text-center font-bold text-slate-600 text-sm">{{ $action }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-4 py-6 px-6 border-b border-slate-100">
                        @for($i=0; $i<4; $i++)
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="1" x-model="permissions" class="sr-only peer">
                                <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-[-1.5rem] after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1A1A31]"></div>
                            </label>
                        </div>
                        @endfor
                    </div>

                    <div class="grid grid-cols-4 bg-slate-50 border-b border-slate-100 py-4 px-6">
                        @foreach($actionsRow2 as $action)
                            <div class="text-center font-bold text-slate-600 text-sm">{{ $action }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-4 py-6 px-6">
                        @for($i=0; $i<4; $i++)
                        <div class="flex justify-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="2" x-model="permissions" class="sr-only peer">
                                <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-[-1.5rem] after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1A1A31]"></div>
                            </label>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-10">
                <button type="submit" class="px-12 py-3 bg-[#1A1A31] text-white text-sm font-bold rounded-xl hover:bg-opacity-90 transition-all">
                    {{ __('إضافة دور') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
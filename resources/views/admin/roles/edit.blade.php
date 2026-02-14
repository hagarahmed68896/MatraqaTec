@extends('layouts.admin')

@section('content')
<div x-data="{ 
    selectAll: false,
    permissions: {{ json_encode($item->permissions->pluck('id')->map(fn($id) => (string)$id)) }},
    toggleAll() {
        if (this.selectAll) {
            this.permissions = Array.from(document.querySelectorAll('input[name=\'permissions[]\']')).map(el => el.value);
        } else {
            this.permissions = [];
        }
    }
}" class="pb-20 text-right" dir="rtl">

    <!-- Header (Matching Screenshot 4 exactly) -->
    <div class="flex items-center gap-4 mb-10 mr-4">
        <a href="{{ route('admin.roles.index') }}" class="text-[#1A1A31] hover:text-primary transition-all">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
        <h1 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ __('تعديل دور') }}: <span class="text-primary">{{ $item->name_ar ?? $item->name }}</span></h1>
    </div>

    <form action="{{ route('admin.roles.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="space-y-8 px-4">
            <!-- بيانات الدور -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-12 border border-slate-100 dark:border-white/5 shadow-sm">
                <div class="mb-10 text-right">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ __('بيانات الدور') }}</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-12">
                    <!-- Arabic Name -->
                    <div class="space-y-4">
                        <label class="text-md font-black text-slate-900 dark:text-white block">{{ __('اسم الدور (اللغة العربية)') }}</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" required 
                               placeholder="{{ __('أدخل اسم الدور') }}"
                               class="w-full px-6 py-5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/10 dark:text-white text-md font-bold transition-all text-center placeholder-slate-300">
                    </div>

                    <!-- English Name -->
                    <div class="space-y-4">
                        <label class="text-md font-black text-slate-900 dark:text-white block">{{ __('اسم الدور (اللغة الإنجليزية)') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $item->name_en) }}" required 
                               placeholder="{{ __('أدخل اسم الدور') }}"
                               class="w-full px-6 py-5 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/10 dark:text-white text-md font-bold transition-all text-center placeholder-slate-300">
                    </div>

                    <!-- Arabic Description -->
                    <div class="space-y-4">
                        <label class="text-md font-black text-slate-900 dark:text-white block">{{ __('الوصف (اللغة العربية)') }}</label>
                        <textarea name="description_ar" rows="4" 
                                  placeholder="{{ __('أدخل الوصف') }}"
                                  class="w-full px-6 py-6 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[2.5rem] focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/10 dark:text-white text-md font-bold transition-all text-center placeholder-slate-300 h-32">{{ old('description_ar', $item->description_ar) }}</textarea>
                    </div>

                    <!-- English Description -->
                    <div class="space-y-4">
                        <label class="text-md font-black text-slate-900 dark:text-white block">{{ __('الوصف (اللغة الإنجليزية)') }}</label>
                        <textarea name="description_en" rows="4" 
                                  placeholder="{{ __('أدخل الوصف') }}"
                                  class="w-full px-6 py-6 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-[2.5rem] focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/10 dark:text-white text-md font-bold transition-all text-center placeholder-slate-300 h-32">{{ old('description_en', $item->description_en) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Permissions Card -->
            <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-12 border border-slate-100 dark:border-white/5 shadow-sm">
                <!-- Select All Row -->
                <div class="flex items-center justify-end mb-10">
                    <div class="flex items-center gap-4">
                        <span class="text-md font-black text-slate-900 dark:text-white">{{ __('تحديد الكل') }}</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="sr-only peer">
                            <div class="w-16 h-8 bg-slate-100 peer-focus:outline-none rounded-full peer dark:bg-white/5 peer-checked:after:translate-x-[-2rem] rtl:peer-checked:after:translate-x-[-2rem] after:content-[''] after:absolute after:top-[4px] after:right-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#1A1A31]"></div>
                        </label>
                    </div>
                </div>

                <div class="space-y-16">
                    @php
                        $modules = [
                            'individual customers' => 'إدارة العملاء الأفراد',
                            'corporate customers' => 'إدارة عملاء الشركات',
                            'technicians' => 'إدارة الفنيين',
                        ];
                        
                        $row1Actions = ['view' => 'عرض', 'add' => 'إضافة', 'edit' => 'تعديل', 'delete' => 'حذف'];
                        $row2Actions = ['block' => 'حظر', 'deactivate' => 'الايقاف', 'activate' => 'تفعيل', 'download' => 'تحميل'];
                    @endphp

                    @foreach($modules as $moduleKey => $moduleName)
                    <div class="space-y-8">
                        <div class="space-y-10">
                            <!-- Row 1 -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-4 bg-[#F9FAFB] dark:bg-white/5 rounded-2xl py-5 px-8">
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">عرض</div>
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">إضافة</div>
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">تعديل</div>
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">حذف</div>
                                </div>
                                <div class="grid grid-cols-4 px-8">
                                    @foreach(['view', 'add', 'edit', 'delete'] as $action)
                                    @php
                                        $permissionName = "{$action} {$moduleKey}";
                                        $permission = $permissions->where('name', $permissionName)->first();
                                    @endphp
                                    <div class="flex justify-center">
                                        @if($permission)
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                   x-model="permissions"
                                                   class="sr-only peer">
                                            <div class="w-16 h-8 bg-slate-100 peer-focus:outline-none rounded-full peer dark:bg-white/5 peer-checked:after:translate-x-[-2rem] rtl:peer-checked:after:translate-x-[-2rem] after:content-[''] after:absolute after:top-[4px] after:right-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#1A1A31]"></div>
                                        </label>
                                        @else
                                        <div class="w-16 h-8"></div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-4 bg-[#F9FAFB] dark:bg-white/5 rounded-2xl py-5 px-8">
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">حظر</div>
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">الايقاف</div>
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">تفعيل</div>
                                    <div class="text-center font-black text-slate-800 dark:text-white/80 text-md">تحميل</div>
                                </div>
                                <div class="grid grid-cols-4 px-8">
                                    @foreach(['block', 'deactivate', 'activate', 'download'] as $action)
                                    @php
                                        $permissionName = "{$action} {$moduleKey}";
                                        $permission = $permissions->where('name', $permissionName)->first();
                                    @endphp
                                    <div class="flex justify-center">
                                        @if($permission)
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                   x-model="permissions"
                                                   class="sr-only peer">
                                            <div class="w-16 h-8 bg-slate-100 peer-focus:outline-none rounded-full peer dark:bg-white/5 peer-checked:after:translate-x-[-2rem] rtl:peer-checked:after:translate-x-[-2rem] after:content-[''] after:absolute after:top-[4px] after:right-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#1A1A31]"></div>
                                        </label>
                                        @else
                                        <div class="w-16 h-8"></div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
                <button type="submit" class="px-20 py-5 bg-[#1A1A31] text-white text-md font-black rounded-2xl hover:bg-black transition-all shadow-2xl shadow-black/20">
                    {{ __('حفظ التعديلات') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
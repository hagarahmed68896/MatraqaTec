@extends('layouts.admin')

@section('content')

<style>
/* ===== PREMIUM SWITCH STYLE ===== */
.switch {
    position: relative;
    width: 60px;
    height: 30px;
}

.switch input {
    display: none;
}

.slider {
    position: absolute;
    inset: 0;
    background: #E2E8F0;
    border-radius: 999px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.slider:before {
    content: "";
    position: absolute;
    height: 22px;
    width: 22px;
    right: 4px;
    top: 4px;
    background: white;
    border-radius: 50%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.switch input:checked + .slider {
    background: #1A1A31;
}

.switch input:checked + .slider:before {
    transform: translateX(-30px);
}

/* RTL Adjustment for slider */
[dir="rtl"] .switch input:checked + .slider:before {
    transform: translateX(30px);
}
[dir="rtl"] .slider:before {
    left: 4px;
    right: auto;
}
</style>


<div
x-data="{
    selectAll:false,
    permissions: {{ json_encode($item->permissions->pluck('id')->map(fn($id) => (string)$id)) }},
    toggleAll(){
        if(this.selectAll){
            this.permissions =
                Array.from(document.querySelectorAll('input[name=\'permissions[]\']'))
                .map(el => el.value)
        }else{
            this.permissions=[]
        }
    }
}"
class="min-h-screen bg-[#F4F5F9] px-6 lg:px-10 py-8 text-right"
dir="rtl"
>

    <!-- PAGE TITLE -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.roles.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white shadow-sm border border-slate-200 text-[#1A1A31] hover:bg-slate-50 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <h1 class="text-2xl font-black text-[#1A1A31]">{{ __('تعديل دور') }}: <span class="text-primary">{{ $item->name_ar ?? $item->name }}</span></h1>
    </div>

    <form action="{{ route('admin.roles.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- MAIN CONTAINER -->
        <div class="space-y-8 max-w-6xl">

            <!-- ROLE DATA CARD -->
            <div class="bg-white rounded-[2.5rem] p-8 lg:p-12 shadow-sm border border-slate-100">
                <h2 class="text-xl font-bold text-[#1A1A31] mb-10 flex items-center gap-3">
                    <span class="w-2 h-8 bg-[#1A1A31] rounded-full"></span>
                    بيانات الدور
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                    <div class="space-y-3">
                        <label class="block text-md font-bold text-slate-700 pr-2">
                             اسم الدور (اللغة العربية)
                        </label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" 
                               class="w-full h-15 px-6 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 focus:bg-white transition-all text-md font-medium">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-md font-bold text-slate-700 pr-2">
                            اسم الدور (اللغة الإنجليزية)
                        </label>
                        <input type="text" name="name_en" value="{{ old('name_en', $item->name_en) }}" 
                               class="w-full h-15 px-6 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 focus:bg-white transition-all text-md font-medium text-left" dir="ltr">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-md font-bold text-slate-700 pr-2">
                            الوصف (اللغة العربية)
                        </label>
                        <textarea name="description_ar" rows="4" 
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 focus:bg-white transition-all text-md font-medium resize-none">{{ old('description_ar', $item->description_ar) }}</textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-md font-bold text-slate-700 pr-2">
                            الوصف (اللغة الإنجليزية)
                        </label>
                        <textarea name="description_en" rows="4" 
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#1A1A31]/5 focus:bg-white transition-all text-md font-medium resize-none text-left" dir="ltr">{{ old('description_en', $item->description_en) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- PERMISSIONS CARD -->
            <div class="bg-white rounded-[2.5rem] p-8 lg:p-12 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-10">
                    <h2 class="text-xl font-bold text-[#1A1A31] flex items-center gap-3">
                        <span class="w-2 h-8 bg-[#1A1A31] rounded-full"></span>
                        صلاحيات المستخدم
                    </h2>

                    <label class="flex items-center gap-4 cursor-pointer group">
                        <span class="text-md font-bold text-[#1A1A31] group-hover:text-primary transition-colors">تحديد الكل</span>
                        <div class="relative inline-flex items-center">
                            <div class="switch">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()">
                                <span class="slider"></span>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- PERMISSION GRID -->
                <div class="rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm overflow-x-auto ring-1 ring-slate-100">
                    <div class="min-w-[1100px]">
                        <!-- Header Row -->
                        <div class="grid grid-cols-[2fr_repeat(8,1fr)] bg-[#F8FAFC] border-b border-slate-100">
                            <div class="py-6 px-10 text-right text-xs font-black text-[#1A1A31] uppercase tracking-widest">الصلاحية / القسم</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">عرض</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">إضافة</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">تعديل</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">حذف</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">حظر</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">الإيقاف</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">تفعيل</div>
                            <div class="py-6 text-center text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">تحميل</div>
                        </div>

                        <!-- Rows -->
                        @php
                            $modules = [
                                'individual customers' => 'إدارة العملاء الأفراد',
                                'corporate customers' => 'إدارة عملاء الشركات',
                                'technicians' => 'إدارة الفنيين',
                                'services' => 'إدارة الخدمات',
                                'orders' => 'إدارة الطلبات',
                            ];
                            $actions = ['view', 'add', 'edit', 'delete', 'block', 'deactivate', 'activate', 'download'];
                        @endphp

                        @foreach($modules as $moduleKey => $moduleName)
                        <div class="grid grid-cols-[2fr_repeat(8,1fr)] bg-white divide-x divide-x-reverse divide-slate-50 border-b last:border-0 border-slate-100 permission-row">
                            <div class="py-8 px-10 flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-primary/20"></div>
                                <span class="text-md font-bold text-[#1A1A31]">{{ $moduleName }}</span>
                            </div>

                            @foreach($actions as $action)
                            @php
                                $permissionName = "{$action} {$moduleKey}";
                                $permission = $permissions->where('name', $permissionName)->first();
                            @endphp
                            <div class="flex justify-center py-8">
                                @if($permission)
                                <label class="switch scale-90">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" x-model="permissions">
                                    <span class="slider"></span>
                                </label>
                                @else
                                <div class="w-15 h-8"></div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="flex justify-end mt-16">
                    <button type="submit" class="group relative px-20 py-5 bg-[#1A1A31] text-white rounded-2xl font-black text-lg shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02] active:scale-[0.98] transition-all overflow-hidden">
                        <span class="relative z-10 flex items-center gap-3">
                            {{ __('حفظ التعديلات') }}
                            <svg class="w-6 h-6 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </span>
                        <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

@endsection
@extends('layouts.admin')

@section('content')

<style>
/* ===== PREMIUM SWITCH STYLE ===== */
.switch {
    position: relative;
    width: 50px;
    height: 26px;
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
}

.slider:before {
    content: "";
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.switch input:checked + .slider {
    background: #1A1A31;
}

.switch input:checked + .slider:before {
    transform: translateX(24px);
}

/* RTL Adjustment for slider */
[dir="rtl"] .switch input:checked + .slider:before {
    transform: translateX(-24px);
}
[dir="rtl"] .slider:before {
    right: 3px;
    left: auto;
}

/* Custom Card Styles */
.form-card {
    background: white;
    border-radius: 2rem;
    padding: 2.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.input-field {
    width: full;
    height: 3.5rem;
    padding: 0 1.5rem;
    background: #F8FAFC;
    border: 1px border-slate-200;
    border-radius: 1rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.input-field:focus {
    outline: none;
    border-color: #1A1A31;
    background: white;
}
</style>


<div
x-data="{
    selectAll:false,
    permissions:[],
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
class="max-w-6xl mx-auto py-4"
dir="rtl"
>

    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
            <h1 class="text-3xl font-black text-[#1A1A31]">إضافة دور</h1>
            <a href="{{ route('admin.roles.index') }}" class="text-[#1A1A31] hover:translate-x-1 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-8">
        @csrf

        <!-- ROLE DATA SECTION -->
        <div class="form-card">
            <h2 class="text-xl font-bold text-[#1A1A31] mb-8">بيانات الدور</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Arabic Name -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">اسم الدور (اللغة العربية)</label>
                    <input type="text" name="name_ar" placeholder="أدخل اسم الدور" 
                           class="w-full h-14 px-6 bg-[#F8FAFC] border border-slate-100 rounded-2xl focus:outline-none focus:border-[#1A1A31] focus:bg-white transition-all">
                    @error('name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- English Name -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">اسم الدور (اللغة الإنجليزية)</label>
                    <input type="text" name="name_en" placeholder="أدخل اسم الدور" 
                           class="w-full h-14 px-6 bg-[#F8FAFC] border border-slate-100 rounded-2xl focus:outline-none focus:border-[#1A1A31] focus:bg-white transition-all text-left" dir="ltr">
                    @error('name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Arabic Description -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">الوصف (اللغة العربية)</label>
                    <textarea name="description_ar" rows="3" placeholder="أدخل الوصف" 
                              class="w-full p-6 bg-[#F8FAFC] border border-slate-100 rounded-2xl focus:outline-none focus:border-[#1A1A31] focus:bg-white transition-all resize-none"></textarea>
                </div>

                <!-- English Description -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">الوصف (اللغة الإنجليزية)</label>
                    <textarea name="description_en" rows="3" placeholder="أدخل الوصف" 
                              class="w-full p-6 bg-[#F8FAFC] border border-slate-100 rounded-2xl focus:outline-none focus:border-[#1A1A31] focus:bg-white transition-all resize-none text-left" dir="ltr"></textarea>
                </div>
            </div>
        </div>

        <!-- PERMISSIONS SECTION -->
        <div class="form-card">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-xl font-bold text-[#1A1A31]">الصلاحيات</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-700">تحديد الكل</span>
                    <label class="switch">
                        <input type="checkbox" x-model="selectAll" @change="toggleAll()">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
<div class="space-y-6">
    <div class="bg-[#F8FAFC] rounded-3xl overflow-hidden border border-slate-50 shadow-sm">
        <div class="grid grid-cols-4">
            @foreach(['عرض' => 'view', 'إضافة' => 'add', 'تعديل' => 'edit', 'حذف' => 'delete'] as $label => $action)
            <div class="flex flex-col items-center py-6 gap-4">
                <span class="text-sm font-bold text-[#1A1A31]">{{ $label }}</span>
                <label class="switch scale-90">
                    @php $perm = $permissions->where('name', 'like', "%{$action}%")->first(); @endphp
                    <input type="checkbox" name="permissions[]" value="{{ $perm->id ?? $action }}" x-model="permissions">
                    <span class="slider"></span>
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-[#F8FAFC] rounded-3xl overflow-hidden border border-slate-50 shadow-sm">
        <div class="grid grid-cols-4">
            @foreach(['حظر' => 'block', 'الإيقاف' => 'deactivate', 'تفعيل' => 'activate', 'تحميل' => 'download'] as $label => $action)
            <div class="flex flex-col items-center py-6 gap-4">
                <span class="text-sm font-bold text-[#1A1A31]">{{ $label }}</span>
                <label class="switch scale-90">
                    @php $perm = $permissions->where('name', 'like', "%{$action}%")->first(); @endphp
                    <input type="checkbox" name="permissions[]" value="{{ $perm->id ?? $action }}" x-model="permissions">
                    <span class="slider"></span>
                </label>
            </div>
            @endforeach
        </div>
    </div>
</div>
        </div>

        <!-- SUBMIT BUTTON -->
        <div class="flex justify-end">
            <button type="submit" class="px-12 py-4 bg-[#1A1A31] text-white rounded-2xl font-bold text-lg hover:shadow-lg transition-all">
                إضافة دور
            </button>
        </div>
    </form>
</div>

@endsection

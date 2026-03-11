@extends('layouts.admin')

@section('title', __('Add Role'))
@section('page_title', __('Add Role'))

@section('content')
<style>
/* Exact Screenshot Switch Style */
.custom-switch {
    display: inline-block;
    width: 44px;
    height: 24px;
    position: relative;
    cursor: pointer;
}
.custom-switch input {
    opacity: 0;
    width: 0; height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #E2E8F0;
    transition: .3s;
    border-radius: 24px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 18px; width: 18px;
    left: 3px; bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #1A1A31;
}
input:checked + .slider:before {
    transform: translateX(20px);
}
/* RTL adjustment */
[dir="rtl"] input:checked + .slider:before {
    transform: translateX(-20px);
}
[dir="rtl"] .slider:before {
    right: 3px; left: auto;
}

/* Compact Table Styling */
.permissions-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.permissions-table th {
    background: #F8FAFC;
    padding: 1rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 900;
    color: #64748B;
    text-align: center;
    border-bottom: 1px solid #E2E8F0;
}
.dark .permissions-table th {
    background: rgba(255, 255, 255, 0.05);
    color: #94A3B8;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.permissions-table td {
    padding: 1.25rem 0.5rem;
    text-align: center;
    border-bottom: 1px solid #F1F5F9;
}
.dark .permissions-table td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.permissions-table tr:last-child td {
    border-bottom: none;
}
.module-name-td {
    text-align: right !important;
    font-weight: 800;
    color: #1E293B;
    width: 200px;
    padding-right: 1.5rem !important;
}
.dark .module-name-td {
    color: #F8FAFC;
}
</style>

@php
    $groupedModules = [
        __('User Management') => [
            __('Customers') => ['individual customers', 'corporate customers', 'blocked users'],
            __('Technicians') => ['technicians', 'technician requests'],
            __('Maintenance Companies') => ['maintenance companies'],
        ],
        __('Operations Center') => [
            __('Orders') => ['orders'],
            __('Contracts') => ['contracts'],
            __('Appointments') => ['appointments'],
            __('Inventory') => ['inventory'],
            __('Services') => ['services'],
        ],
        __('Financial Affairs') => [
            __('Financial Management') => ['financial reports', 'refunds', 'contract receipts', 'financial settlements', 'wallet transactions', 'reports'],
        ],
        __('Content & Support') => [
            __('Content & Support') => ['complaints', 'inquiry and support', 'faqs', 'contents', 'social links', 'notifications'],
        ],
    ];
    $actions = ['view', 'add', 'edit', 'delete', 'block', 'activate', 'deactivate', 'download'];
    
    // Prepare group to ID mapping for Alpine.js
    $groupMap = [];
    foreach($groupedModules as $groupName => $modules) {
        $ids = [];
        foreach($modules as $moduleName => $moduleKeys) {
            foreach($moduleKeys as $moduleKey) {
                foreach($actions as $action) {
                    $p = $permissions->where('name', "{$action} {$moduleKey}")->first();
                    if($p) $ids[] = (string)$p->id;
                }
            }
        }
        $groupMap[$groupName] = $ids;
    }
@endphp

<div x-data="{
    permissions: [],
    groupMap: {{ json_encode($groupMap) }},
    masterGroups: {},
    selectAll: false,
    showAdvanced: false,

    init() {
        Object.keys(this.groupMap).forEach(group => {
            this.masterGroups[group] = false;
        });
    },

    toggleGroup(group) {
        const ids = this.groupMap[group];
        if (this.masterGroups[group]) {
            ids.forEach(id => {
                if (!this.permissions.includes(id)) this.permissions.push(id)
            });
        } else {
            this.permissions = this.permissions.filter(id => !ids.includes(id));
        }
        this.checkIfAllSelected();
    },

    updateMasterStates() {
        Object.keys(this.groupMap).forEach(group => {
            const ids = this.groupMap[group];
            this.masterGroups[group] = ids.length > 0 && ids.every(id => this.permissions.includes(id));
        });
    },

    toggleAll() {
        const allIds = [].concat(...Object.values(this.groupMap));
        if (this.selectAll) {
            this.permissions = [...new Set(allIds)];
        } else {
            this.permissions = [];
        }
        this.updateMasterStates();
    },

    checkIfAllSelected() {
        const allIds = [].concat(...Object.values(this.groupMap));
        this.selectAll = allIds.length > 0 && allIds.every(id => this.permissions.includes(id));
        this.updateMasterStates();
    }
}" class="space-y-6 pb-20 text-right" dir="rtl">

    <!-- Header Section -->
    <div class="flex items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.roles.index') }}" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white dark:bg-white/5 shadow-sm border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white">{{ __('Add Role') }}</h1>
        </div>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Role Basic Info Card -->
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 lg:p-10 border border-slate-100 dark:border-white/5 shadow-sm">
            <div class="flex items-center gap-3 mb-8 border-b border-slate-50 dark:border-white/5 pb-6">
                <div class="w-2 h-8 bg-[#1A1A31] dark:bg-primary rounded-full"></div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Role Details') }}</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Role Name Arabic -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">
                        {{ __('Role Name (Arabic)') }}
                    </label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" required placeholder="أدخل اسم الدور"
                           class="w-full h-14 px-6 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold transition-all">
                </div>

                <!-- Role Name English -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">
                        {{ __('Role Name (English)') }}
                    </label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" required placeholder="أدخل اسم الدور"
                           class="w-full h-14 px-6 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold transition-all text-left" dir="ltr">
                </div>

                <!-- Arabic Description -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">
                        {{ __('Description (Arabic)') }}
                    </label>
                    <textarea name="description_ar" rows="4" placeholder="{{ __('Enter description') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold transition-all resize-none">{{ old('description_ar') }}</textarea>
                </div>

                <!-- English Description -->
                <div class="space-y-3">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">
                        {{ __('Description (English)') }}
                    </label>
                    <textarea name="description_en" rows="4" placeholder="{{ __('Enter description') }}"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/20 dark:text-white text-md font-bold transition-all resize-none text-left" dir="ltr">{{ old('description_en') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 lg:p-10 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-6 bg-primary rounded-full"></div>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white">{{ __('Permissions Control') }}</h2>
                </div>
                <div class="flex items-center gap-4 bg-slate-50 dark:bg-white/5 px-6 py-3 rounded-2xl border border-slate-100 dark:border-white/5">
                    <span class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase">{{ __('Select All') }}</span>
                    <label class="custom-switch">
                        <input type="checkbox" x-model="selectAll" @change="toggleAll()">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach($groupedModules as $groupName => $modules)
                    @php
                        $icons = [
                            __('User Management') => 'users',
                            __('Operations Center') => 'package',
                            __('Financial Affairs') => 'coins',
                            __('Content & Support') => 'life-buoy',
                            __('System Settings') => 'settings',
                        ];
                        $icon = $icons[$groupName] ?? 'shield';
                        $colorClass = match($groupName) {
                            __('User Management') => 'text-blue-500 bg-blue-500/10',
                            __('Operations Center') => 'text-emerald-500 bg-emerald-500/10',
                            __('Financial Affairs') => 'text-amber-500 bg-amber-500/10',
                            __('Content & Support') => 'text-indigo-500 bg-indigo-500/10',
                            __('System Settings') => 'text-red-500 bg-red-500/10',
                            default => 'text-slate-500 bg-slate-500/10'
                        };
                    @endphp
                    <div @click="masterGroups['{{ $groupName }}'] = !masterGroups['{{ $groupName }}']; toggleGroup('{{ $groupName }}')"
                         class="relative group cursor-pointer p-6 rounded-[2rem] border-2 transition-all duration-300 overflow-hidden"
                         :class="masterGroups['{{ $groupName }}'] ? 'bg-white dark:bg-white/5 border-primary shadow-xl shadow-primary/10' : 'bg-slate-50 dark:bg-transparent border-slate-100 dark:border-white/5 hover:border-slate-200'">
                        
                        <div class="flex flex-col items-center gap-4 relative z-10">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 {{ $colorClass }}">
                                @switch($icon)
                                    @case('users')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        @break
                                    @case('package')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @break
                                    @case('coins')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @break
                                    @case('life-buoy')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        @break
                                    @case('settings')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        @break
                                @endswitch
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white text-center">{{ $groupName }}</span>
                            
                            <label class="custom-switch mt-2" @click.stop>
                                <input type="checkbox" x-model="masterGroups['{{ $groupName }}']" @change="toggleGroup('{{ $groupName }}')">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 flex flex-col items-center">
                <button type="button" @click="showAdvanced = !showAdvanced" 
                        class="flex items-center gap-2 px-8 py-3 bg-slate-100 dark:bg-white/5 rounded-2xl text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                    <span>{{ __('Customize Permissions (Advanced)') }}</span>
                    <svg class="w-5 h-5 transition-transform duration-300" :class="showAdvanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="showAdvanced" x-collapse x-transition:enter="transition ease-out duration-300" class="w-full mt-10">
                    <div class="overflow-x-auto rounded-[2rem] border border-slate-100 dark:border-white/5">
                        <table class="permissions-table w-full">
                            <thead>
                                <tr>
                                    <th class="module-name-td">{{ __('Section') }}</th>
                                    <th>{{ __('View') }}</th>
                                    <th>{{ __('Add') }}</th>
                                    <th>{{ __('Edit') }}</th>
                                    <th>{{ __('Delete') }}</th>
                                    <th>{{ __('Block') }}</th>
                                    <th>{{ __('Activate') }}</th>
                                    <th>{{ __('Deactivate') }}</th>
                                    <th>{{ __('Download') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedModules as $groupName => $modules)
                                    <tr class="group-header">
                                        <td colspan="9" class="bg-slate-50 dark:bg-white/5 py-4 px-6 text-right">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                                                <span class="text-xs font-black text-slate-800 dark:text-white uppercase">{{ $groupName }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($modules as $moduleName => $moduleKeys)
                                        <tr>
                                            <td class="module-name-td">{{ $moduleName }}</td>
                                            @foreach($actions as $action)
                                                @php
                                                    $rowPermissionIds = [];
                                                    foreach($moduleKeys as $moduleKey) {
                                                        $p = $permissions->where('name', "{$action} {$moduleKey}")->first();
                                                        if($p) $rowPermissionIds[] = (string)$p->id;
                                                    }
                                                    $jsonIds = json_encode($rowPermissionIds);
                                                @endphp
                                                <td>
                                                    @if(count($rowPermissionIds) > 0)
                                                        <label class="custom-switch">
                                                            <input type="checkbox" 
                                                                   :checked="{{ $jsonIds }}.every(id => permissions.includes(id))"
                                                                   @change="
                                                                       if($event.target.checked) {
                                                                           {{ $jsonIds }}.forEach(id => { if(!permissions.includes(id)) permissions.push(id) });
                                                                       } else {
                                                                           permissions = permissions.filter(id => !{{ $jsonIds }}.includes(id));
                                                                       }
                                                                       checkIfAllSelected();
                                                                   ">
                                                            <span class="slider"></span>
                                                        </label>
                                                        {{-- Hidden inputs for form submission since we are not using x-model directly on the checkbox --}}
                                                        <template x-for="id in {{ $jsonIds }}">
                                                            <input type="hidden" name="permissions[]" :value="id" x-if="permissions.includes(id)">
                                                        </template>
                                                    @else
                                                        <div class="w-8 h-px bg-slate-200 dark:bg-white/10 rounded-full mx-auto opacity-30"></div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Button Section -->
            <div class="flex justify-end mt-16 border-t border-slate-50 dark:border-white/5 pt-10">
                <button type="submit" 
                        class="group relative px-8 py-4 bg-[#1A1A31] dark:bg-white/5 text-white rounded-[2rem] font-black text-lg shadow-2xl shadow-[#1A1A31]/30 hover:scale-[1.02] active:scale-[0.98] transition-all overflow-hidden uppercase tracking-widest">
                    <span class="relative z-10 flex items-center gap-3">
                        {{ __('Add Role') }}
                    
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

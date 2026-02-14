@extends('layouts.admin')

@section('title', __('Edit Technician'))
@section('page_title', __('Edit Technician'))

@section('content')
<div class="max-w-4xl mx-auto pb-20" x-data="{ 
    profileImage: '{{ $item->image ? asset('storage/' . $item->image) : null }}',
    residenceImage: '{{ $item->national_id_image ? asset('storage/' . $item->national_id_image) : null }}',
    selectedCity: '{{ $item->user->city_id ?? '' }}',
    selectedCategory: '{{ $item->category_id ?? '' }}',
    previewImage(event, type) {
        const file = event.target.files[0];
        if (file) {
            this[type] = URL.createObjectURL(file);
        }
    },
    removeImage(type) {
        this[type] = null;
        document.getElementById(type + 'Input').value = '';
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('admin.technicians.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-primary transition-colors font-bold group">
            <div class="w-10 h-10 rounded-xl bg-white dark:bg-white/5 flex items-center justify-center border border-slate-100 dark:border-white/5 group-hover:bg-primary/10">
                <svg class="w-5 h-5 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </div>
            {{ __('Back to Technicians') }}
        </a>
    </div>

    <form action="{{ route('admin.technicians.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#1A1A31] rounded-[2.5rem] p-8 md:p-12 border border-slate-100 dark:border-white/5 shadow-sm overflow-hidden">
            <div class="mb-10 text-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}">
                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">{{ __('Edit Account Data') }}</h3>
                <div class="h-1.5 w-12 bg-primary rounded-full"></div>
            </div>

            <!-- Images Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <!-- Profile Image -->
                <div class="space-y-4">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Technician Image') }}</label>
                    <div class="relative group h-48 bg-slate-50 dark:bg-white/5 rounded-3xl border-2 border-dashed border-slate-200 dark:border-white/10 flex items-center justify-center transition-all hover:border-primary overflow-hidden">
                        <template x-if="profileImage">
                            <div class="relative w-full h-full">
                                <img :src="profileImage" class="w-full h-full object-cover">
                                <button type="button" @click="removeImage('profileImage')" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center shadow-lg hover:bg-red-600 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!profileImage">
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Upload Photo') }}</p>
                            </div>
                        </template>
                        <input type="file" name="image" id="profileImageInput" class="absolute inset-0 opacity-0 cursor-pointer" @change="previewImage($event, 'profileImage')">
                    </div>
                </div>

                <!-- Residence Image -->
                <div class="space-y-4">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Residence Image') }}</label>
                    <div class="relative group h-48 bg-slate-50 dark:bg-white/5 rounded-3xl border-2 border-dashed border-slate-200 dark:border-white/10 flex items-center justify-center transition-all hover:border-primary overflow-hidden">
                        <template x-if="residenceImage">
                            <div class="relative w-full h-full">
                                <img :src="residenceImage" class="w-full h-full object-cover">
                                <button type="button" @click="removeImage('residenceImage')" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center shadow-lg hover:bg-red-600 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!residenceImage">
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Upload IQAMA') }}</p>
                            </div>
                        </template>
                        <input type="file" name="national_id_image" id="residenceImageInput" class="absolute inset-0 opacity-0 cursor-pointer" @change="previewImage($event, 'residenceImage')">
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <!-- Names Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Technician Name (Arabic)') }}</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" required 
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                    <div class="space-y-3 text-left">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Technician Name (English)') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $item->name_en) }}" required dir="ltr"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                </div>

                <!-- Contact Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $item->user->phone ?? '') }}" required placeholder="5XXXXXXXX"
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Email Address') }}</label>
                        <input type="email" name="email" value="{{ old('email', $item->user->email ?? '') }}" required 
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                </div>

                <!-- Services Logic -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Category') }}</label>
                        <select name="category_id" required x-model="selectedCategory"
                                class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent rounded-2xl text-sm font-bold transition-all outline-none border-r-[16px] border-r-transparent">
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach($services as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                            @endforeach
                        </select>

                        <!-- Sub-services (Checkboxes) -->
                        <div class="mt-4 grid grid-cols-1 gap-3 p-4 bg-slate-50/50 dark:bg-white/5 rounded-3xl" x-show="selectedCategory">
                            @foreach($services as $cat)
                                <div x-show="selectedCategory == {{ $cat->id }}" class="space-y-3">
                                    @foreach($cat->children as $child)
                                        <label class="flex items-center gap-3 p-3 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-100 dark:border-white/5 transition-all hover:border-primary group cursor-pointer">
                                            <input type="radio" name="service_id" value="{{ $child->id }}" {{ $item->service_id == $child->id ? 'checked' : '' }} class="w-5 h-5 border-2 border-slate-200 dark:border-white/10 text-primary focus:ring-primary rounded-lg">
                                            <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors">{{ $child->name_ar }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Regions Logic -->
                    <div class="space-y-4">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Working Regions') }}</label>
                        <select name="city_id" x-model="selectedCity"
                                class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent rounded-2xl text-sm font-bold transition-all outline-none border-r-[16px] border-r-transparent">
                            <option value="">{{ __('Select City') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name_ar }}</option>
                            @endforeach
                        </select>

                        <!-- Districts (Checkboxes) -->
                        <div class="mt-4 grid grid-cols-1 gap-3 p-4 bg-slate-50/50 dark:bg-white/5 rounded-3xl" x-show="selectedCity">
                            @foreach($cities as $city)
                                <div x-show="selectedCity == {{ $city->id }}" class="space-y-3">
                                    @foreach($city->districts as $district)
                                        <label class="flex items-center justify-between p-3 bg-white dark:bg-[#1A1A31] rounded-xl border border-slate-100 dark:border-white/5 transition-all hover:border-primary group cursor-pointer">
                                            <span class="text-sm font-bold text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors">{{ $district->name_ar }}</span>
                                            <input type="checkbox" name="districts[]" value="{{ $district->id }}" {{ in_array($district->id, $item->districts ?? []) ? 'checked' : '' }} class="w-5 h-5 border-2 border-slate-200 dark:border-white/10 text-primary focus:ring-primary rounded-lg transition-all">
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Exp & Bio -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Years of Experience') }}</label>
                        <input type="number" name="years_experience" value="{{ old('years_experience', $item->years_experience) }}" 
                               class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Bio (English)') }}</label>
                        <textarea name="bio_en" rows="3" dir="ltr" class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none resize-none">{{ old('bio_en', $item->bio_en) }}</textarea>
                    </div>
                    <div class="md:col-span-2 space-y-3">
                        <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2">{{ __('Bio (Arabic)') }}</label>
                        <textarea name="bio_ar" rows="3" class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border border-transparent focus:border-primary focus:bg-white dark:focus:bg-[#1A1A31] rounded-2xl text-sm font-bold transition-all outline-none resize-none">{{ old('bio_ar', $item->bio_ar) }}</textarea>
                    </div>
                </div>

                <!-- Status -->
                <div class="pt-8">
                    <label class="block text-sm font-black text-slate-700 dark:text-slate-300 pr-2 mb-6">{{ __('Account Status') }}</label>
                    <div class="grid grid-cols-2 gap-4" x-data="{ status: '{{ $item->user->status ?? 'active' }}' }">
                        <label class="relative flex items-center gap-4 p-5 rounded-3xl border-2 cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-white/5 border-transparent bg-slate-50 dark:bg-white/5"
                               :class="status === 'active' ? 'border-primary ring-4 ring-primary/10' : ''"
                               @click="status = 'active'">
                            <input type="radio" name="status" value="active" class="hidden" x-model="status">
                            <div class="w-10 h-10 rounded-2xl bg-green-500/10 text-green-500 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('Active') }}</span>
                        </label>
                        <label class="relative flex items-center gap-4 p-5 rounded-3xl border-2 cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-white/5 border-transparent bg-slate-50 dark:bg-white/5"
                               :class="status === 'blocked' ? 'border-red-500 ring-4 ring-red-500/10' : ''"
                               @click="status = 'blocked'">
                            <input type="radio" name="status" value="blocked" class="hidden" x-model="status">
                            <div class="w-10 h-10 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <span class="text-sm font-black text-slate-800 dark:text-white">{{ __('Blocked') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 mt-12 pt-8 border-t border-slate-100 dark:border-white/5">
                <button type="submit" class="flex-1 py-5 bg-[#1A1A31] text-white rounded-[2rem] font-black hover:bg-black transition-all shadow-xl shadow-black/20 uppercase tracking-widest text-sm">
                    {{ __('Save Changes') }}
                </button>
                <a href="{{ route('admin.technicians.index') }}" class="px-12 py-5 bg-slate-100 dark:bg-white/5 text-slate-500 rounded-[2rem] font-black hover:bg-slate-200 transition-all uppercase tracking-widest text-sm flex items-center justify-center">
                    {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
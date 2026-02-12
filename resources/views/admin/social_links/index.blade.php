@extends('layouts.admin')

@section('title', __('Social Links & Contact'))
@section('page_title', __('Social Links & Contact'))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Contact Settings -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-6">{{ __('Core Contact') }}</h3>
            <form action="{{ route('admin.social-links.update-contact') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Business Mobile') }}</label>
                    <input type="text" name="contact_mobile" value="{{ $contact_mobile }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Business Email') }}</label>
                    <input type="email" name="contact_email" value="{{ $contact_email }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
                </div>
                <button type="submit" class="w-full py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                    {{ __('Update Contact Info') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Social Links -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Social Media Profiles') }}</h3>
                <button onclick="document.getElementById('add-social-modal').classList.remove('hidden')" class="px-4 py-2 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white rounded-lg text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('Add Link') }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($items as $item)
                <div class="p-4 rounded-2xl border border-slate-50 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            @if($item->icon)
                            <i class="{{ $item->icon }}"></i>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            @endif
                        </div>
                        <div>
                            <span class="block font-bold dark:text-white">{{ $item->name }}</span>
                            <a href="{{ $item->url }}" target="_blank" class="text-[10px] text-primary hover:underline">{{ $item->url }}</a>
                        </div>
                    </div>
                    <form action="{{ route('admin.social-links.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete this link?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
                @empty
                <div class="col-span-2 py-12 text-center text-slate-400 font-bold bg-slate-50 dark:bg-white/5 rounded-2xl border border-dashed border-slate-200 dark:border-white/10">
                    {{ __('No social links found') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Simple Modal (Hidden by default) -->
<div id="add-social-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#1A1A31] rounded-[2rem] border border-slate-100 dark:border-white/5 p-8 w-full max-w-md shadow-2xl">
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6">{{ __('Add Social Media Link') }}</h3>
        <form action="{{ route('admin.social-links.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Platform Name') }}</label>
                <input type="text" name="name" placeholder="e.g. WhatsApp, Instagram" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">{{ __('Profile URL') }}</label>
                <input type="url" name="url" placeholder="https://..." required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 dark:text-white">
            </div>
            <div class="flex gap-4 pt-4">
                <button type="button" onclick="document.getElementById('add-social-modal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white rounded-xl font-bold hover:bg-slate-200 transition-all">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary-light transition-all">
                    {{ __('Add Link') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
<!-- ACCEPT ORDER MODAL -->
<div x-show="showAcceptModal" x-cloak 
     class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-[#1A1A31]/40 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    
    <div class="bg-white rounded-[3rem] w-full max-w-2xl shadow-2xl relative overflow-hidden"
         @click.away="showAcceptModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <!-- Close Button -->
        <button @click="showAcceptModal = false" class="absolute top-8 left-8 text-slate-300 hover:text-slate-500 transition-colors z-[1001]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="p-12 text-center space-y-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('Accept Order') }}</h2>
                <button @click="toggleViewMode()" class="px-6 py-2 rounded-xl border border-slate-100 font-bold text-xs text-[#1A1A31] hover:bg-slate-50 transition-all flex items-center gap-2">
                    <template x-if="viewMode === 'list'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ __('Show Map') }}
                        </span>
                    </template>
                    <template x-if="viewMode === 'map'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            {{ __('Show List') }}
                        </span>
                    </template>
                </button>
            </div>

            <!-- LIST VIEW -->
            <div x-show="viewMode === 'list'" class="space-y-8">
                <!-- Tabs -->
                <div class="flex p-1.5 bg-slate-50 rounded-2xl">
                    <button @click="modalTab = 'platform'; fetchTechnicians()" 
                            :class="modalTab === 'platform' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400'"
                            class="flex-1 py-3 rounded-xl font-bold text-md transition-all whitespace-nowrap px-4">
                        {{ __('Assign Platform Technician') }}
                    </button>
                    <button @click="modalTab = 'company'; fetchCompanies()" 
                            :class="modalTab === 'company' ? 'bg-[#1A1A31] text-white shadow-lg' : 'text-slate-400'"
                            class="flex-1 py-3 rounded-xl font-bold text-md transition-all whitespace-nowrap px-4">
                        {{ __('Send Order to Maintenance Company') }}
                    </button>
                </div>

                <p class="text-slate-400 font-bold text-md leading-relaxed" x-text="modalTab === 'platform' ? '{{ __('Select an available technician to perform the maintenance request') }}' : '{{ __('Select an available maintenance company to perform the request') }}'"></p>

                <!-- Items Container -->
                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    <template x-if="(modalTab === 'platform' && loadingTechs) || (modalTab === 'company' && loadingCompanies)">
                        <div class="py-10 text-center">
                            <div class="inline-block w-8 h-8 border-4 border-[#1A1A31] border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </template>

                    <template x-if="modalTab === 'platform' && !loadingTechs">
                        <div class="space-y-4">
                            <template x-for="tech in technicians" :key="tech.id">
                                <label class="flex items-center gap-6 p-6 rounded-[2rem] border-2 transition-all cursor-pointer group text-right"
                                       :class="selectedTechId === tech.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-50 hover:border-slate-100'">
                                    <div class="flex-1 flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100">
                                            <img :src="tech.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="font-black text-[#1A1A31] group-hover:text-primary transition-colors" x-text="tech.name"></h4>
                                            <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                <span x-text="tech.service_name"></span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                    <span x-text="`{{ __('Rating:') }} ${tech.rating}`"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                         :class="selectedTechId === tech.id ? 'border-primary bg-primary' : 'border-slate-200 group-hover:border-primary/50'">
                                        <input type="radio" x-model="selectedTechId" :value="tech.id" class="hidden">
                                        <svg x-show="selectedTechId === tech.id" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </template>

                    <template x-if="modalTab === 'company' && !loadingCompanies">
                        <div class="space-y-4">
                            <template x-for="comp in companies" :key="comp.id">
                                <label class="flex items-center gap-6 p-6 rounded-[2rem] border-2 transition-all cursor-pointer group text-right"
                                       :class="selectedCompanyId === comp.id ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-50 hover:border-slate-100'">
                                    <div class="flex-1 flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-slate-100">
                                            <img :src="comp.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="font-black text-[#1A1A31] group-hover:text-primary transition-colors" x-text="comp.name"></h4>
                                            <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                    <span x-text="`{{ __('Rating:') }} ${comp.rating}`"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="relative w-6 h-6 border-2 rounded-full transition-all flex items-center justify-center"
                                         :class="selectedCompanyId === comp.id ? 'border-primary bg-primary' : 'border-slate-200 group-hover:border-primary/50'">
                                        <input type="radio" x-model="selectedCompanyId" :value="comp.id" class="hidden">
                                        <svg x-show="selectedCompanyId === comp.id" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </template>
                </div>

                <form :action="`/admin/orders/${selectedOrder}/accept`" method="POST" class="pt-6">
                    @csrf
                    <input type="hidden" name="technician_id" :value="selectedTechId">
                    <input type="hidden" name="maintenance_company_id" :value="selectedCompanyId">
                    

                    <button type="submit" 
                            :disabled="!selectedTechId && !selectedCompanyId"
                            :class="selectedTechId || selectedCompanyId ? 'bg-[#1A1A31] shadow-xl shadow-[#1A1A31]/20 hover:scale-[1.02]' : 'bg-slate-300 cursor-not-allowed'"
                            class="w-full py-5 text-white rounded-[1.5rem] font-black text-md transition-all uppercase tracking-widest">
                        {{ __('Assign & Accept') }}
                    </button>
                </form>
            </div>

            <!-- MAP VIEW -->
            <div x-show="viewMode === 'map'" class="relative">
                <div class="absolute top-6 left-6 right-6 z-[1000] flex gap-4 pointer-events-none">
                    <div class="flex-1 max-w-sm pointer-events-auto">
                        <div class="relative text-right">
                            <input type="text" x-model="searchMap" @input="renderMarkers()" placeholder="{{ __('Search...') }}" class="w-full h-12 pr-12 pl-4 bg-white/90 backdrop-blur-md border border-slate-100 rounded-2xl shadow-lg focus:outline-none font-bold text-md text-right">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tech-map" class="z-[1]"></div>
                
                <template x-if="selectedMapTech">
                    <div class="absolute bottom-6 left-6 right-6 z-[1000] bg-white rounded-3xl shadow-2xl p-6 text-right animate-fade-in-up">
                        <div class="flex items-center justify-between mb-4">
                            <button @click="selectedMapTech.type === 'technician' ? selectedTechId = selectedMapTech.id : selectedCompanyId = selectedMapTech.id; viewMode = 'list'" 
                                    class="px-6 py-2 bg-[#1A1A31] text-white rounded-xl font-bold text-xs hover:scale-105 transition-all">
                                {{ __('Select') }}
                            </button>
                            <div class="flex items-center gap-4">
                                <div>
                                    <h4 class="font-black text-[#1A1A31]" x-text="selectedMapTech.name"></h4>
                                    <p class="text-[10px] font-bold text-slate-400" x-text="selectedMapTech.service_name"></p>
                                </div>
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100">
                                    <img :src="selectedMapTech.avatar || '/assets/admin/images/avatar-placeholder.png'" class="w-full h-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- REFUSE ORDER MODAL -->
<div x-show="showRefuseModal" x-cloak 
     class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-[#1A1A31]/40 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    
    <div class="bg-white rounded-[3rem] w-full max-w-lg shadow-2xl relative overflow-hidden"
         @click.away="showRefuseModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="p-12 text-center space-y-8">
            <div class="w-20 h-20 bg-red-50 rounded-[2rem] flex items-center justify-center mx-auto text-red-500">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl font-black text-[#1A1A31]">{{ __('Refuse Order') }}</h2>
                <p class="text-slate-400 font-bold text-md">{{ __('Please state the reason for rejection to clarify to the customer') }}</p>
            </div>

            <form :action="`/admin/orders/${selectedOrder}/refuse`" method="POST" class="space-y-8">
                @csrf
                <textarea name="rejection_reason" x-model="rejectionReason" required
                          placeholder="{{ __('Write the rejection reason here...') }}"
                          class="w-full h-40 p-6 bg-slate-50 border-none rounded-[2rem] focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-md text-[#1A1A31] resize-none text-right"></textarea>

                <div class="flex gap-4">
                    <button type="submit" 
                            :disabled="!rejectionReason.trim()"
                            :class="rejectionReason.trim() ? 'bg-red-500 shadow-xl shadow-red-500/20 hover:scale-[1.02]' : 'bg-slate-300 cursor-not-allowed'"
                            class="flex-1 py-5 text-white rounded-[1.5rem] font-black text-md transition-all transform capitalize tracking-widest">
                        {{ __('Confirm Rejection') }}
                    </button>
                    <button type="button" @click="showRefuseModal = false" class="flex-[0.5] py-5 bg-slate-100 text-slate-400 rounded-[1.5rem] font-bold text-md hover:bg-slate-200 transition-all">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="/dashboard/" class="text-indigo-600 hover:scale-110 transition-transform">
                <i class="fas fa-house"></i>
            </a>
            <h1 class="font-black text-xl text-indigo-950 tracking-tight italic uppercase">
                CAAS <span class="text-slate-300 font-light mx-2">|</span> 
                <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
            </h1>
        </div>
        
        <div class="flex items-center gap-6 text-sm font-medium text-slate-500 italic">
            <a href="/user/" class="group flex items-center gap-3 hover:text-indigo-600 transition">
                <div class="hidden sm:block text-right">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400 group-hover:text-indigo-400 transition">Din profil</span>
                    <span class="text-slate-900 font-bold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition border border-slate-200 shadow-sm">
                    <i class="fas fa-user-gear text-xs"></i>
                </div>
            </a>

            <a href="/logout/" class="text-slate-500 hover:text-red-500 transition">
                <i class="fas fa-power-off text-xs"></i>
            </a>
        </div>
    </div>
</nav>
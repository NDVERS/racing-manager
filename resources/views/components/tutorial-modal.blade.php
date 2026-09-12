<!-- Pit-Wall Operations Manual Modal -->
<div
    id="tutorialModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modalTitle"
>
    <div class="relative w-full max-w-2xl bg-zinc-900 border border-zinc-800 rounded-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Top Accent Bar -->
        <div class="h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-red-600"></div>

        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/60">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded bg-orange-600/20 border border-orange-500/40 text-orange-400 flex items-center justify-center text-xs font-bold font-telemetry">
                    RM
                </span>
                <div>
                    <h3 id="modalTitle" class="text-sm font-bold text-white uppercase tracking-wide">
                        Pit-Wall Operations Manual
                    </h3>
                    <p class="text-[11px] text-zinc-400 font-telemetry">
                        TEAM PRINCIPAL FIELD GUIDE &bull; SLIDE <span id="slideIndicator">1</span> / 4
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeTutorialModal()"
                class="text-zinc-400 hover:text-white p-1 rounded hover:bg-zinc-800 transition-colors cursor-pointer"
                aria-label="Close modal"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Slide Content Area -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">
            <!-- Slide 1: Welcome & Constructor Treasury -->
            <div class="tutorial-slide space-y-4" data-slide="1">
                <div class="w-12 h-12 rounded-xl bg-orange-950/60 border border-orange-500/40 text-orange-400 flex items-center justify-center text-2xl">
                    🏢
                </div>
                <div>
                    <span class="text-[10px] font-telemetry font-bold text-orange-400 uppercase tracking-widest bg-orange-950/80 px-2 py-0.5 rounded border border-orange-500/30">
                        STEP 01 // FOUNDATION
                    </span>
                    <h4 class="text-lg font-bold text-white mt-1.5 uppercase">Constructor Operations & Treasury</h4>
                    <p class="text-xs text-zinc-300 leading-relaxed mt-1">
                        Selamat datang di paddock! Sebagai <strong class="text-white font-bold">Team Principal</strong>, tugas utama Anda adalah mengelola keuangan tim (<span class="text-amber-400 font-bold">Credits</span>), menaikkan pamor (<span class="text-cyan-400 font-bold">Reputation</span>), dan memastikan kesiapan armada balap sebelum setiap Grand Prix.
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <div class="bg-zinc-950/80 border border-zinc-800/80 rounded p-3 text-xs">
                        <div class="font-bold text-amber-400 uppercase font-telemetry text-[11px]">Team Credits (CR)</div>
                        <div class="text-zinc-400 text-[11px] mt-0.5">Digunakan untuk membayar entry fee balap, kontrak gaji pembalap, dan upgrade engineering mobil.</div>
                    </div>
                    <div class="bg-zinc-950/80 border border-zinc-800/80 rounded p-3 text-xs">
                        <div class="font-bold text-cyan-400 uppercase font-telemetry text-[11px]">Reputation Points (REP)</div>
                        <div class="text-zinc-400 text-[11px] mt-0.5">Mencerminkan reputasi tim Anda. Semakin tinggi reputasi, semakin besar potensi prestise di kejuaraan.</div>
                    </div>
                </div>
            </div>

            <!-- Slide 2: Garage & R&D Upgrades -->
            <div class="tutorial-slide hidden space-y-4" data-slide="2">
                <div class="w-12 h-12 rounded-xl bg-amber-950/60 border border-amber-500/40 text-amber-400 flex items-center justify-center text-2xl">
                    🔧
                </div>
                <div>
                    <span class="text-[10px] font-telemetry font-bold text-amber-400 uppercase tracking-widest bg-amber-950/80 px-2 py-0.5 rounded border border-amber-500/30">
                        STEP 02 // ENGINEERING
                    </span>
                    <h4 class="text-lg font-bold text-white mt-1.5 uppercase">Garage Fleet & R&D Workshop</h4>
                    <p class="text-xs text-zinc-300 leading-relaxed mt-1">
                        Kunjungi lembar inspeksi garasi untuk mengoptimalkan performa kendaraan melalui 5 paket komponen modifikasi bertingkat:
                    </p>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between bg-zinc-950/80 border border-zinc-800 p-2 rounded">
                        <span class="text-zinc-200 font-semibold">&bull; Powertrain & Turbo</span>
                        <span class="text-blue-400 font-telemetry font-bold">+Top Speed</span>
                    </div>
                    <div class="flex items-center justify-between bg-zinc-950/80 border border-zinc-800 p-2 rounded">
                        <span class="text-zinc-200 font-semibold">&bull; Drivetrain & Gearbox</span>
                        <span class="text-cyan-400 font-telemetry font-bold">+Acceleration</span>
                    </div>
                    <div class="flex items-center justify-between bg-zinc-950/80 border border-zinc-800 p-2 rounded">
                        <span class="text-zinc-200 font-semibold">&bull; Aero & Downforce</span>
                        <span class="text-emerald-400 font-telemetry font-bold">+Handling</span>
                    </div>
                    <div class="flex items-center justify-between bg-zinc-950/80 border border-zinc-800 p-2 rounded">
                        <span class="text-zinc-200 font-semibold">&bull; Braking System & Durability</span>
                        <span class="text-amber-400 font-telemetry font-bold">+Braking / Reliability</span>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Driver Lineup & Race Strategy -->
            <div class="tutorial-slide hidden space-y-4" data-slide="3">
                <div class="w-12 h-12 rounded-xl bg-cyan-950/60 border border-cyan-500/40 text-cyan-400 flex items-center justify-center text-2xl">
                    🏎️
                </div>
                <div>
                    <span class="text-[10px] font-telemetry font-bold text-cyan-400 uppercase tracking-widest bg-cyan-950/80 px-2 py-0.5 rounded border border-cyan-500/30">
                        STEP 03 // TACTICS
                    </span>
                    <h4 class="text-lg font-bold text-white mt-1.5 uppercase">Driver Lineup & Tactics</h4>
                    <p class="text-xs text-zinc-300 leading-relaxed mt-1">
                        Pembalap utama Anda menentukan performa di lintasan. Sebelum balapan dimulai, tentukan strategi yang selaras dengan cuaca sirkuit:
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
                    <div class="bg-red-950/40 border border-red-500/30 p-2.5 rounded text-center">
                        <div class="text-red-400 font-bold uppercase">Aggressive</div>
                        <div class="text-[11px] text-zinc-400 mt-1">Pace +5% lebih kencang, resiko insiden/spin lebih tinggi.</div>
                    </div>
                    <div class="bg-zinc-950/80 border border-zinc-800 p-2.5 rounded text-center">
                        <div class="text-zinc-200 font-bold uppercase">Balanced</div>
                        <div class="text-[11px] text-zinc-400 mt-1">Kombinasi standar optimal antara kecepatan dan stabilitas.</div>
                    </div>
                    <div class="bg-emerald-950/40 border border-emerald-500/30 p-2.5 rounded text-center">
                        <div class="text-emerald-400 font-bold uppercase">Conserve</div>
                        <div class="text-[11px] text-zinc-400 mt-1">Pencegahan insiden maksimal, ideal di lintasan basah (<span class="text-cyan-300 font-semibold">Wet</span>).</div>
                    </div>
                </div>
            </div>

            <!-- Slide 4: Simulation, Debrief & Archives -->
            <div class="tutorial-slide hidden space-y-4" data-slide="4">
                <div class="w-12 h-12 rounded-xl bg-purple-950/60 border border-purple-500/40 text-purple-400 flex items-center justify-center text-2xl">
                    🏁
                </div>
                <div>
                    <span class="text-[10px] font-telemetry font-bold text-purple-400 uppercase tracking-widest bg-purple-950/80 px-2 py-0.5 rounded border border-purple-500/30">
                        STEP 04 // RESULTS
                    </span>
                    <h4 class="text-lg font-bold text-white mt-1.5 uppercase">Live Race Simulation & Debrief</h4>
                    <p class="text-xs text-zinc-300 leading-relaxed mt-1">
                        Saksikan balapan secara langsung di layar <strong class="text-white font-bold">Live Telemetry</strong>, rebut podium <span class="text-amber-400 font-bold">P1-P3</span>, raih poin kejuaraan FIA, dan dapatkan hadiah uang tunai untuk memperbesar tim Anda!
                    </p>
                </div>
                <div class="bg-zinc-950/80 border border-zinc-800 p-3 rounded text-xs space-y-1.5">
                    <div class="flex items-center gap-2 text-zinc-300">
                        <span class="text-emerald-400 font-bold">&check;</span>
                        <span>Selesaikan 4 <strong class="text-white font-bold">Directives</strong> di Dashboard untuk klaim <span class="text-amber-400 font-bold font-telemetry">+5,000 CR</span> bonus!</span>
                    </div>
                    <div class="flex items-center gap-2 text-zinc-300">
                        <span class="text-cyan-400 font-bold">&check;</span>
                        <span>Akses buku panduan lengkap kapan saja di menu <span class="text-white font-semibold">Team Handbook (/guide)</span>.</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Navigation Controls -->
        <div class="px-6 py-3.5 border-t border-zinc-800 bg-zinc-950/80 flex items-center justify-between">
            <!-- Dots Indicator -->
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="goToSlide(1)" class="w-2.5 h-2.5 rounded-full dot-indicator bg-orange-500 transition-all cursor-pointer" aria-label="Slide 1"></button>
                <button type="button" onclick="goToSlide(2)" class="w-2.5 h-2.5 rounded-full dot-indicator bg-zinc-700 transition-all cursor-pointer" aria-label="Slide 2"></button>
                <button type="button" onclick="goToSlide(3)" class="w-2.5 h-2.5 rounded-full dot-indicator bg-zinc-700 transition-all cursor-pointer" aria-label="Slide 3"></button>
                <button type="button" onclick="goToSlide(4)" class="w-2.5 h-2.5 rounded-full dot-indicator bg-zinc-700 transition-all cursor-pointer" aria-label="Slide 4"></button>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="prevSlideBtn"
                    onclick="prevSlide()"
                    disabled
                    class="px-3 py-1.5 rounded bg-zinc-800 text-zinc-500 text-xs font-semibold uppercase transition cursor-not-allowed"
                >
                    &larr; Prev
                </button>

                <button
                    type="button"
                    id="nextSlideBtn"
                    onclick="nextSlide()"
                    class="px-4 py-1.5 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-bold uppercase transition cursor-pointer shadow"
                >
                    Next &rarr;
                </button>

                <button
                    type="button"
                    id="finishSlideBtn"
                    onclick="closeTutorialModal()"
                    class="hidden px-4 py-1.5 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase transition cursor-pointer shadow"
                >
                    Got It &bull; Start Managing
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSlide = 1;
    const totalSlides = 4;

    function openTutorialModal() {
        const modal = document.getElementById('tutorialModal');
        if (!modal) return;
        modal.classList.remove('opacity-0', 'pointer-events-none');
        goToSlide(1);
    }

    function closeTutorialModal() {
        const modal = document.getElementById('tutorialModal');
        if (!modal) return;
        modal.classList.add('opacity-0', 'pointer-events-none');
        try {
            localStorage.setItem('rm_tutorial_seen', 'true');
        } catch (e) {}
    }

    function goToSlide(slideNum) {
        currentSlide = Math.max(1, Math.min(totalSlides, slideNum));
        document.querySelectorAll('.tutorial-slide').forEach(slide => {
            const num = parseInt(slide.getAttribute('data-slide'));
            if (num === currentSlide) {
                slide.classList.remove('hidden');
            } else {
                slide.classList.add('hidden');
            }
        });

        // Update Dots
        const dots = document.querySelectorAll('.dot-indicator');
        dots.forEach((dot, idx) => {
            if (idx + 1 === currentSlide) {
                dot.classList.remove('bg-zinc-700');
                dot.classList.add('bg-orange-500', 'w-5');
            } else {
                dot.classList.remove('bg-orange-500', 'w-5');
                dot.classList.add('bg-zinc-700');
            }
        });

        // Update Indicator Text
        const indicator = document.getElementById('slideIndicator');
        if (indicator) indicator.textContent = currentSlide;

        // Button states
        const prevBtn = document.getElementById('prevSlideBtn');
        const nextBtn = document.getElementById('nextSlideBtn');
        const finishBtn = document.getElementById('finishSlideBtn');

        if (prevBtn) {
            prevBtn.disabled = (currentSlide === 1);
            if (currentSlide === 1) {
                prevBtn.classList.add('bg-zinc-800', 'text-zinc-500', 'cursor-not-allowed');
                prevBtn.classList.remove('hover:bg-zinc-700', 'text-zinc-200');
            } else {
                prevBtn.classList.remove('bg-zinc-800', 'text-zinc-500', 'cursor-not-allowed');
                prevBtn.classList.add('hover:bg-zinc-700', 'text-zinc-200');
            }
        }

        if (currentSlide === totalSlides) {
            if (nextBtn) nextBtn.classList.add('hidden');
            if (finishBtn) finishBtn.classList.remove('hidden');
        } else {
            if (nextBtn) nextBtn.classList.remove('hidden');
            if (finishBtn) finishBtn.classList.add('hidden');
        }
    }

    function nextSlide() {
        if (currentSlide < totalSlides) {
            goToSlide(currentSlide + 1);
        }
    }

    function prevSlide() {
        if (currentSlide > 1) {
            goToSlide(currentSlide - 1);
        }
    }

    // Keyboard support (Escape to close, Arrows to paginate)
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('tutorialModal');
        if (!modal || modal.classList.contains('opacity-0')) return;

        if (e.key === 'Escape') {
            closeTutorialModal();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
        } else if (e.key === 'ArrowLeft') {
            prevSlide();
        }
    });
</script>

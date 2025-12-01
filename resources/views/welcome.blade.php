<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bharatagrolink - Logistics Infrastructure API</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* YOUR EXACT COLOR SYSTEM */
        :root {
            --color-f53003: #f53003;
            --color-F61500: #F61500;
            --color-1b1b18: #1b1b18;
            --color-706f6c: #706f6c;
            --color-e3e3e0: #e3e3e0;
            --color-FDFDFC: #FDFDFC;
            --color-161615: #161615;
            --color-1D0002: #1D0002;
            --color-3E3E3A: #3E3E3A;
            --color-EDEDEC: #EDEDEC;
            --color-A1A09A: #A1A09A;
        }

        .bg-page { background-color: var(--color-FDFDFC); }
        .dark .bg-page-dark { background-color: #0a0a0a; }
        .text-primary { color: var(--color-1b1b18); }
        .dark .text-primary-dark { color: var(--color-EDEDEC); }
        .text-secondary { color: var(--color-706f6c); }
        .dark .text-secondary-dark { color: var(--color-A1A09A); }
        .border-base { border-color: var(--color-e3e3e0); }
        .dark .border-base-dark { border-color: var(--color-3E3E3A); }
        .bg-brand { background-color: var(--color-f53003); }
        .bg-brand-hover:hover { background-color: var(--color-F61500); }
        .text-brand { color: var(--color-f53003); }

        body { font-family: 'Instrument Sans', sans-serif; }

        .shadow-inset { box-shadow: inset 0 0 0 1px rgba(26,26,0,0.08); }
        .dark .shadow-inset { box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1); }

        .code-window { background: #1e1e1e; box-shadow: 0 20px 50px -10px rgba(0,0,0,0.3); }

        /* Grid Background Pattern */
        .bg-grid {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, var(--color-e3e3e0) 1px, transparent 1px),
            linear-gradient(to bottom, var(--color-e3e3e0) 1px, transparent 1px);
            mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
        }
        .dark .bg-grid {
            background-image: linear-gradient(to right, var(--color-3E3E3A) 1px, transparent 1px),
            linear-gradient(to bottom, var(--color-3E3E3A) 1px, transparent 1px);
        }

        /* Timeline styling */
        .timeline-line::before {
            content: '';
            position: absolute;
            top: 8px;
            bottom: 0;
            left: 7px;
            width: 2px;
            background: var(--color-e3e3e0);
            z-index: 0;
        }
        .dark .timeline-line::before { background: var(--color-3E3E3A); }

        .token-key { color: #9cdcfe; }
        .token-string { color: #ce9178; }
        .token-number { color: #b5cea8; }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {} }
        }
    </script>
</head>
<body class="bg-page dark:bg-page-dark transition-colors duration-300 overflow-x-hidden">

<nav class="fixed w-full z-50 top-0 border-b border-base dark:border-base-dark bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-brand rounded-md flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="font-bold text-lg text-primary dark:text-primary-dark tracking-tight">Bharatagrolink</span>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm font-medium text-secondary dark:text-secondary-dark">
            <a href="#track" class="text-primary dark:text-primary-dark font-semibold">Track Shipment</a>
            <a href="#" class="hover:text-primary dark:hover:text-primary-dark transition-colors">Products</a>
            <a href="#" class="hover:text-primary dark:hover:text-primary-dark transition-colors">Developers</a>
            <a href="#" class="hover:text-primary dark:hover:text-primary-dark transition-colors">Pricing</a>
        </div>

        <div class="flex items-center gap-4">
            <button onclick="toggleDarkMode()" class="p-2 text-secondary dark:text-secondary-dark hover:bg-gray-100 dark:hover:bg-white/5 rounded-full transition-all">
                <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"></path></svg>
            </button>
            <a href="#" class="px-4 py-2 bg-brand bg-brand-hover text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-brand/50 transition-all">Get API Keys</a>
        </div>
    </div>
</nav>

<section class="relative pt-32 pb-32 lg:pt-48 lg:pb-56 overflow-hidden">
    <div class="absolute inset-0 bg-grid z-[-1]"></div>

    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-base dark:border-base-dark bg-white dark:bg-white/5 text-xs font-medium text-secondary dark:text-secondary-dark mb-6">
                <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                v2.0 API is now live
            </div>
            <h1 class="text-5xl lg:text-7xl font-semibold tracking-tight text-primary dark:text-primary-dark mb-6 leading-[1.1]">
                Logistics infrastructure for <span class="text-brand">India</span>
            </h1>
            <p class="text-lg text-secondary dark:text-secondary-dark mb-8 leading-relaxed max-w-lg">
                Programmatically schedule pickups, track shipments in real-time, and manage returns.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="#" class="px-8 py-3.5 bg-brand bg-brand-hover text-white font-semibold rounded-lg shadow-lg hover:-translate-y-1 transition-all text-center">Start Building</a>
                <a href="#" class="px-8 py-3.5 bg-white dark:bg-white/5 border border-base dark:border-base-dark text-primary dark:text-primary-dark font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-white/10 transition-all text-center">Read Documentation</a>
            </div>
        </div>

        <div class="relative lg:h-[400px] flex items-center hidden lg:flex">
            <div class="absolute inset-0 bg-brand/5 blur-3xl rounded-full"></div>
            <div class="code-window w-full rounded-xl overflow-hidden border border-white/10 relative z-10 transform rotate-1 hover:rotate-0 transition-transform duration-500">
                <div class="bg-[#2d2d2d] px-4 py-3 flex items-center justify-between border-b border-white/5">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="text-[10px] text-gray-400 font-mono">POST /v1/shipments</div>
                </div>
                <div class="p-6 overflow-x-auto">
<pre class="font-mono text-sm leading-6">
<span class="text-[#c586c0]">const</span> shipment <span class="text-[#c586c0]">=</span> <span class="text-[#c586c0]">await</span> client.<span class="text-[#dcdcaa]">createShipment</span>({
  <span class="token-key">origin</span>: <span class="token-string">"462023"</span>,
  <span class="token-key">destination</span>: <span class="token-string">"110001"</span>,
  <span class="token-key">weight</span>: <span class="token-number">1.5</span>
});
</pre>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="track" class="px-6 -mt-20 relative z-20">
    <div class="max-w-4xl mx-auto bg-white dark:bg-[#161615] rounded-2xl shadow-2xl border border-base dark:border-base-dark overflow-hidden">
        <div class="bg-[#fafafa] dark:bg-[#1D0002] border-b border-base dark:border-base-dark px-6 py-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <h3 class="font-semibold text-primary dark:text-primary-dark">Track Shipment</h3>
        </div>

        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="relative flex-1">
                    <input type="text" id="trackInput" placeholder="Enter AWB Number (e.g., 12345678)" class="w-full pl-12 pr-4 py-4 rounded-lg bg-page dark:bg-page-dark border border-base dark:border-base-dark focus:border-brand focus:ring-1 focus:ring-brand outline-none transition-all text-primary dark:text-primary-dark font-mono placeholder-gray-400">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary dark:text-secondary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button onclick="simulateTracking()" class="px-8 py-4 bg-brand hover:bg-[#F61500] text-white font-bold rounded-lg shadow-lg transition-all flex items-center justify-center gap-2">
                    <span>Track Order</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>

            <div id="trackResult" class="hidden border-t border-base dark:border-base-dark pt-6 animate-[fadeInUp_0.5s_ease-out]">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <div class="text-sm text-secondary dark:text-secondary-dark mb-1">Status</div>
                        <div class="text-2xl font-bold text-green-500">Out for Delivery</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-secondary dark:text-secondary-dark mb-1">Estimated Delivery</div>
                        <div class="text-xl font-semibold text-primary dark:text-primary-dark">Today, by 6:00 PM</div>
                    </div>
                </div>

                <div class="timeline-line relative space-y-8 pl-1">
                    <div class="relative flex gap-4 items-start z-10">
                        <div class="w-4 h-4 rounded-full bg-green-500 border-2 border-white dark:border-[#161615] shadow-lg mt-1 shrink-0"></div>
                        <div>
                            <div class="font-semibold text-primary dark:text-primary-dark">Out for Delivery</div>
                            <div class="text-sm text-secondary dark:text-secondary-dark">Bhopal Hub, MP</div>
                            <div class="text-xs text-secondary dark:text-secondary-dark mt-1">Today, 09:30 AM</div>
                        </div>
                    </div>
                    <div class="relative flex gap-4 items-start z-10">
                        <div class="w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-[#161615] mt-1 shrink-0"></div>
                        <div>
                            <div class="font-semibold text-primary dark:text-primary-dark">Arrived at Destination Hub</div>
                            <div class="text-sm text-secondary dark:text-secondary-dark">Bhopal Hub, MP</div>
                            <div class="text-xs text-secondary dark:text-secondary-dark mt-1">Yesterday, 11:45 PM</div>
                        </div>
                    </div>
                    <div class="relative flex gap-4 items-start z-10">
                        <div class="w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600 border-2 border-white dark:border-[#161615] mt-1 shrink-0"></div>
                        <div>
                            <div class="font-semibold text-primary dark:text-primary-dark">Shipped</div>
                            <div class="text-sm text-secondary dark:text-secondary-dark">New Delhi Central</div>
                            <div class="text-xs text-secondary dark:text-secondary-dark mt-1">Nov 26, 04:00 PM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mt-20 border-y border-base dark:border-base-dark bg-white/50 dark:bg-white/5 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl font-bold text-primary dark:text-primary-dark mb-1">20K+</div>
                <div class="text-sm text-secondary dark:text-secondary-dark">Pincodes</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-primary dark:text-primary-dark mb-1">99.9%</div>
                <div class="text-sm text-secondary dark:text-secondary-dark">Uptime</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-primary dark:text-primary-dark mb-1">50ms</div>
                <div class="text-sm text-secondary dark:text-secondary-dark">Latency</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-primary dark:text-primary-dark mb-1">24/7</div>
                <div class="text-sm text-secondary dark:text-secondary-dark">Support</div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 max-w-7xl mx-auto px-6">
    <div class="text-center mb-16">
        <h2 class="text-3xl font-semibold text-primary dark:text-primary-dark mb-4">Everything you need to ship</h2>
        <p class="text-secondary dark:text-secondary-dark">A complete suite of APIs designed for scale.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="p-8 rounded-xl bg-white dark:bg-[#161615] shadow-inset border border-base dark:border-base-dark transition-all hover:-translate-y-1">
            <div class="w-12 h-12 bg-[#fff2f2] dark:bg-[#1D0002] rounded-lg flex items-center justify-center text-brand mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-xl font-medium text-primary dark:text-primary-dark mb-3">Real-time Tracking</h3>
            <p class="text-secondary dark:text-secondary-dark text-sm leading-relaxed">Granular tracking events via Webhooks.</p>
        </div>
        <div class="p-8 rounded-xl bg-white dark:bg-[#161615] shadow-inset border border-base dark:border-base-dark transition-all hover:-translate-y-1">
            <div class="w-12 h-12 bg-[#fff2f2] dark:bg-[#1D0002] rounded-lg flex items-center justify-center text-brand mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-xl font-medium text-primary dark:text-primary-dark mb-3">Label Generation</h3>
            <p class="text-secondary dark:text-secondary-dark text-sm leading-relaxed">Generate shipping labels instantly in PDF or ZPL.</p>
        </div>
        <div class="p-8 rounded-xl bg-white dark:bg-[#161615] shadow-inset border border-base dark:border-base-dark transition-all hover:-translate-y-1">
            <div class="w-12 h-12 bg-[#fff2f2] dark:bg-[#1D0002] rounded-lg flex items-center justify-center text-brand mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-medium text-primary dark:text-primary-dark mb-3">Rate Optimization</h3>
            <p class="text-secondary dark:text-secondary-dark text-sm leading-relaxed">Automatically select the cheapest or fastest carrier.</p>
        </div>
    </div>
</section>

<footer class="border-t border-base dark:border-base-dark bg-white dark:bg-[#161615] pt-16 pb-8 mt-12">
    <div class="max-w-7xl mx-auto px-6 text-center text-sm text-secondary dark:text-secondary-dark">
        <p>&copy; 2025 Bharatagrolink Pvt Ltd. All rights reserved.</p>
    </div>
</footer>

<script>
    // Theme Logic
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }

    function toggleDarkMode() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }

    // Mock Tracking Logic
    function simulateTracking() {
        const btn = document.querySelector('button[onclick="simulateTracking()"]');
        const input = document.getElementById('trackInput');
        const result = document.getElementById('trackResult');

        if(!input.value) {
            input.focus();
            input.classList.add('ring-2', 'ring-red-500');
            setTimeout(() => input.classList.remove('ring-2', 'ring-red-500'), 1000);
            return;
        }

        // Loading state
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        setTimeout(() => {
            btn.innerHTML = originalText;
            result.classList.remove('hidden');
        }, 800);
    }
</script>
</body>
</html>

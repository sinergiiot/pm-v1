<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Filament PM') }} - Modern Project Management</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|outfit:400,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            amber: {
                                500: '#f59e0b',
                                600: '#d97706',
                            }
                        },
                        fontFamily: {
                            sans: ['Instrument Sans', 'sans-serif'],
                            display: ['Outfit', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .text-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(245, 158, 11, 0.15);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
        }
    </style>
</head>
<body class="bg-[#0a0a0a] text-white selection:bg-amber-500/30 selection:text-amber-200 overflow-x-hidden">
    <!-- Background Elements -->
    <div class="blob top-[-100px] left-[-100px] animate-pulse"></div>
    <div class="blob bottom-[-100px] right-[-100px] animate-pulse" style="animation-delay: 2s"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass border-b border-white/5 py-4">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center gap-2 group cursor-pointer">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/20 rotate-3 group-hover:rotate-12 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <span class="text-2xl font-bold font-display tracking-tight">{{ config('app.name', 'Filament PM') }}</span>
            </div>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ url('/admin') }}" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold rounded-full transition-all shadow-lg shadow-amber-500/20 text-sm">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ url('/admin/login') }}" class="text-sm font-medium hover:text-amber-500 transition-colors">Log in</a>
                    <a href="{{ url('/admin/login') }}" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold rounded-full transition-all shadow-lg shadow-amber-500/20 text-sm">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-40 pb-20 px-6">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-500 text-xs font-bold tracking-widest uppercase mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    Introducing Project Management Next-Gen
                </div>
                <h1 class="text-6xl lg:text-7xl font-bold font-display leading-[1.1] mb-8">
                    Organize your <br> <span class="text-gradient">Projects & Finances</span> in one place.
                </h1>
                <p class="text-xl text-gray-400 mb-10 leading-relaxed max-w-xl">
                    The most powerful, intuitive project management platform built on Laravel 12. 
                    Manage tasks, track gantt charts, sync with Google Calendar, and monitor 
                    your profit & loss in real-time.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ url('/admin/login') }}" class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-black font-bold rounded-2xl transition-all shadow-xl shadow-amber-500/20 text-center text-lg">
                        Start for Free
                    </a>
                    <a href="#features" class="px-8 py-4 glass hover:bg-white/5 font-bold rounded-2xl transition-all text-center text-lg">
                        Explore Features
                    </a>
                </div>
            </div>
            <div class="order-1 lg:order-2 relative">
                <div class="absolute inset-0 bg-amber-500/20 blur-[100px] -z-10 rotate-12 scale-75"></div>
                <img src="{{ asset('/storage/pm_dashboard_mockup_1771869549589.png') }}" alt="Dashboard Mockup" class="w-full h-auto rounded-3xl shadow-2xl border border-white/10 transform lg:rotate-2 hover:rotate-0 transition-transform duration-700">
                
                <!-- Floating Mini Widgets -->
                <div class="absolute -bottom-6 -left-6 glass p-6 rounded-2xl shadow-2xl border border-white/10 hidden xl:block animate-bounce" style="animation-duration: 4s">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold">Revenue Up</div>
                            <div class="text-xs text-gray-400">+24% this month</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-6 relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h2 class="text-4xl lg:text-5xl font-bold font-display mb-6">Everything you need to <span class="text-gradient">Scale</span>.</h2>
                <p class="text-gray-400 max-w-2xl mx-auto text-lg">Built with modern tech for high performance and seamless user experience.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition-all border border-white/5 group">
                    <div class="w-14 h-14 bg-amber-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Kanban & Gantt</h3>
                    <p class="text-gray-400 leading-relaxed">Visualize your project flow with advanced Kanban boards and professional Gantt charts for accurate scheduling.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition-all border border-white/5 group">
                    <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Google Calendar Sync</h3>
                    <p class="text-gray-400 leading-relaxed">Automatic synchronization with your Google Calendar. Never miss a deadline again with real-time updates.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition-all border border-white/5 group">
                    <div class="w-14 h-14 bg-green-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Financial Reports</h3>
                    <p class="text-gray-400 leading-relaxed">Integrated accounting system providing instant P&L and Cash Flow statements for every project you manage.</p>
                </div>

                <!-- Feature 4 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition-all border border-white/5 group">
                    <div class="w-14 h-14 bg-purple-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">File Management</h3>
                    <p class="text-gray-400 leading-relaxed">Secure document storage with powerful versioning and approval workflows to keep your files organized.</p>
                </div>

                <!-- Feature 5 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition-all border border-white/5 group">
                    <div class="w-14 h-14 bg-red-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Zoom Integration</h3>
                    <p class="text-gray-400 leading-relaxed">Schedule and launch Zoom meetings directly from your project dashbord. Collaborative meetings made simple.</p>
                </div>

                <!-- Feature 6 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition-all border border-white/5 group">
                    <div class="w-14 h-14 bg-amber-500/20 rounded-2xl flex items-center justify-center mb-6 border border-amber-500/30 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">RBAC Security</h3>
                    <p class="text-gray-400 leading-relaxed">Advanced Role-Based Access Control (Shield) ensures that only authorized personnel can access sensitive data.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="pt-20 pb-10 border-t border-white/5 bg-[#0a0a0a]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-20">
                <div class="col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold font-display tracking-tight">{{ config('app.name', 'Filament PM') }}</span>
                    </div>
                    <p class="text-gray-500 max-w-sm">
                        Streamline your workflow, manage your team, and control your project finances with a state-of-the-art platform.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-6">Product</h4>
                    <ul class="space-y-4 text-gray-500 text-sm">
                        <li><a href="#features" class="hover:text-amber-500">Features</a></li>
                        <li><a href="#" class="hover:text-amber-500">Integrations</a></li>
                        <li><a href="#" class="hover:text-amber-500">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-6">Legal</h4>
                    <ul class="space-y-4 text-gray-500 text-sm">
                        <li><a href="{{ url('/privacy-policy') }}" class="hover:text-amber-500">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-amber-500">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col md:flex-row items-center justify-between pt-10 border-t border-white/5 text-gray-500 text-sm gap-4">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Filament PM') }}. Created by Sinergi IoT Team.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-amber-500">Twitter</a>
                    <a href="#" class="hover:text-amber-500">GitHub</a>
                    <a href="#" class="hover:text-amber-500">LinkedIn</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ config('app.name', 'Filament PM') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        </style>
    @endif
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen selection:bg-amber-100 selection:text-amber-900">
    <div class="max-w-4xl mx-auto px-6 py-12 lg:py-20">
        <header class="mb-12">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-amber-600 hover:text-amber-700 transition-colors mb-8">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back to Home</span>
            </a>
            <h1 class="text-4xl lg:text-5xl font-bold tracking-tight mb-4">Privacy Policy</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Last updated: {{ now()->format('F j, Y') }}</p>
        </header>

        <main class="prose prose-amber dark:prose-invert max-w-none">
            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">1. Introduction</h2>
                <p>Welcome to <strong>{{ config('app.name', 'Filament PM') }}</strong>. We respect your privacy and are committed to protecting your personal data. This Privacy Policy informs you how we look after your personal data when you visit our website and tell you about your privacy rights and how the law protects you.</p>
            </section>

            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">2. Information We Collect</h2>
                <p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Identity Data:</strong> includes first name, last name, username or similar identifier.</li>
                    <li><strong>Contact Data:</strong> includes email address and telephone numbers.</li>
                    <li><strong>Technical Data:</strong> includes internet protocol (IP) address, your login data, browser type and version, time zone setting and location, browser plug-in types and versions, operating system and platform, and other technology on the devices you use to access this website.</li>
                    <li><strong>OAuth Data:</strong> When you login via Google, we collect your Google ID, name, email, and avatar from your public profile. We also request access to your Google Calendar to synchronize tasks, which we only use to provide the calendar integration feature.</li>
                </ul>
            </section>

            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">3. How We Use Your Information</h2>
                <p>We use your information to:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Register you as a new user.</li>
                    <li>Manage our relationship with you.</li>
                    <li>Enable you to participate in project management features.</li>
                    <li>Integrate with third-party services like Google Calendar to sync your tasks.</li>
                    <li>Improve our website, services, and user experience.</li>
                </ul>
            </section>

            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">4. Google Workspace Data</h2>
                <p>Specifically regarding Google APIs:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Our app requests access to your Google Calendar specifically to create, update, and delete events tied to your project tasks.</li>
                    <li>We do not share your Google Calendar data with any third parties.</li>
                    <li>We do not use your Google Workspace data for advertising or marketing purposes.</li>
                </ul>
            </section>

            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">5. Data Retention</h2>
                <p>We will only retain your personal data for as long as necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, accounting, or reporting requirements.</p>
            </section>

            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">6. Your Legal Rights</h2>
                <p>Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to request access, correction, erasure, or restriction of your personal data.</p>
            </section>

            <section class="mb-10">
                <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-800 pb-2">7. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                <div class="bg-gray-50 dark:bg-[#161615] p-6 rounded-lg border border-gray-200 dark:border-gray-800">
                    <p class="font-medium text-amber-600">Email: support@sinergiiot.com</p>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-2">Sinergi IoT Project Management Team</p>
                </div>
            </section>
        </main>

        <footer class="mt-20 pt-8 border-t border-gray-200 dark:border-gray-800 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Filament PM') }}. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

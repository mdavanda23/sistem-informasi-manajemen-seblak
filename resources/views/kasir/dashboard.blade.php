<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasir · SEPARMAN</title>
    @include('partials.head')
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 font-sans">
    <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center">
        <h1 class="text-2xl font-bold text-slate-900">Halo, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-slate-500">Halaman kasir sedang dikerjakan.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button class="rounded-xl bg-brand-700 px-6 py-3 font-semibold text-white">Keluar</button>
        </form>
    </div>
</body>
</html>
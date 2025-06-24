@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <h2 class="text-2xl font-bold mb-6">Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- tarjeta documentos -->
        <a href="{{ route('documents.index') }}" class="block bg-white shadow rounded p-6 hover:bg-gray-50">
            <p class="text-sm text-gray-500">Documentos subidos</p>
            <p class="text-3xl font-bold mt-1">{{ \App\Models\Document::where('user_id', auth()->id())->count() }}</p>
        </a>

        <!-- tarjeta llaves -->
        <a href="{{ route('keys.index') }}" class="block bg-white shadow rounded p-6 hover:bg-gray-50">
            <p class="text-sm text-gray-500">Llaves públicas</p>
            <p class="text-3xl font-bold mt-1">{{ \App\Models\KeyPair::where('user_id', auth()->id())->count() }}</p>
        </a>

        <!-- tarjeta última firma -->
        <div class="bg-white shadow rounded p-6">
            <p class="text-sm text-gray-500">Última firma</p>
            @php
                $ultima = \App\Models\Signature::where('user_id', auth()->id())->latest()->first();
            @endphp
            <p class="text-lg mt-1">
                {{ $ultima ? $ultima->created_at->format('d/m/Y H:i') : '—' }}
            </p>
        </div>
    </div>
</div>
@endsection

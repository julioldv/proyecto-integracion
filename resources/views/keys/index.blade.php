@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Mis llaves públicas</h2>

    {{-- Mensajes flash --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Generar llave y descargar en nueva pestaña --}}
    <form action="{{ route('keys.store') }}" method="POST" target="_blank" class="inline">
        @csrf
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Generar nueva llave
        </button>
    </form>

    <table class="w-full bg-white shadow rounded mt-6">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-3">ID</th>
                <th class="p-3">Llave pública (abreviada)</th>
                <th class="p-3">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($keys as $k)
            <tr class="border-t hover:bg-gray-50">
                <td class="p-3">{{ $k->id }}</td>
                <td class="p-3 text-xs truncate">{{ Str::limit($k->public_key, 80) }}</td>
                <td class="p-3">{{ $k->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

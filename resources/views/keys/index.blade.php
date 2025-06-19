@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Mis llaves públicas</h2>

    {{-- Botón para generar y descargar en una pestaña nueva --}}
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
            <tr class="border-t">
                <td class="p-3">{{ $k->id }}</td>
                <td class="p-3 text-xs truncate">{{ \Illuminate\Support\Str::limit($k->public_key, 80) }}</td>
                <td class="p-3">{{ $k->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

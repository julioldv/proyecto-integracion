@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Mi par de llaves</h2>

    {{-- Mensajes flash --}}
    @foreach (['success' => 'green', 'error' => 'red'] as $t => $c)
        @if (session($t))
            <div class="bg-{{ $c }}-100 text-{{ $c }}-800 p-4 rounded mb-4">
                {{ session($t) }}
            </div>
        @endif
    @endforeach

    @if ($keys->isEmpty())
        {{-- ╷  No existe aún un par de llaves  ╷ --}}
        <form action="{{ route('keys.store') }}" method="POST" target="_blank">
            @csrf
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Generar par de llaves
            </button>
            <p class="text-sm text-gray-500 mt-2">
                Se descargará tu <strong>clave&nbsp;privada</strong>; guárdala con seguridad.
            </p>
        </form>
    @else
        {{-- ╷  Mostrar la llave pública existente  ╷ --}}
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3">ID</th>
                    <th class="p-3">Llave pública&nbsp;(abreviada)</th>
                    <th class="p-3">Creada</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($keys as $k)
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-3">{{ $k->id }}</td>
                    <td class="p-3 text-xs truncate">
                        {{ \Illuminate\Support\Str::limit($k->public_key, 80) }}
                    </td>
                    <td class="p-3">{{ $k->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- ─ Reemplazar el par (invalidará firmas previas) ─ --}}
        <form action="{{ route('keys.store') }}" method="POST" target="_blank" class="mt-6">
            @csrf
            <button type="submit"
                    class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                Reemplazar par de llaves
            </button>
            <p class="text-sm text-gray-500 mt-1">
                * Reemplazar invalidará las firmas que hayas hecho con la llave anterior.
            </p>
        </form>
    @endif
</div>
@endsection

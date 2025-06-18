@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Mis Documentos</h2>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($documents->isEmpty())
        <p class="text-gray-600">Aún no has subido documentos.</p>
    @else
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Hash</th>
                    <th class="p-3">Fecha</th>
                    <th class="p-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $doc)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $doc->original_name }}</td>
                        <td class="p-3 text-xs text-gray-500 truncate">{{ $doc->file_hash }}</td>
                        <td class="p-3">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                               class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

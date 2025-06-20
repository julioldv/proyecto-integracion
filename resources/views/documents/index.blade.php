@extends('layouts.app')

{{-- helper para abreviar texto --}}
@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Mis Documentos</h2>

    {{-- Búsqueda + botón Subir --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('documents.index') }}" class="w-full md:max-w-md">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por nombre de documento..."
                   class="border rounded px-4 py-2 w-full">
        </form>

        <a href="{{ route('documents.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-center">
            Subir nuevo documento
        </a>
    </div>

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

    @if ($documents->isEmpty())
        <p class="text-gray-600">Aún no has subido documentos.</p>
    @else
        <table class="w-full bg-white shadow rounded">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Hash</th>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Firma</th>
                    <th class="p-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($documents as $doc)
                @php $estadoFirma = $doc->signatureStatusFor(auth()->user()); @endphp
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-3">{{ $doc->original_name }}</td>

                    <td class="p-3 text-xs text-gray-500 truncate">
                        {{ Str::limit($doc->file_hash, 40) }}
                    </td>

                    <td class="p-3">{{ $doc->created_at->format('d/m/Y H:i') }}</td>

                    {{-- Estado de firma --}}
                    <td class="p-3">
                        @switch($estadoFirma)
                            @case('válida')
                                <span class="text-green-600 font-semibold">Válida</span>
                                @break
                            @case('inválida')
                                <span class="text-red-600 font-semibold">Inválida</span>
                                @break
                            @default
                                <span class="text-gray-500">—</span>
                        @endswitch
                    </td>

                    {{-- Acciones --}}
                    <td class="p-3 text-center space-x-2">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                           class="text-blue-600 hover:underline">Ver</a>

                        <a href="{{ route('documents.download', $doc->id) }}"
                           class="text-green-600 hover:underline ml-2">Descargar</a>

                        <form action="{{ route('documents.destroy', $doc->id) }}"
                              method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('¿Estás seguro de eliminar este documento?')"
                                    class="text-red-600 hover:underline ml-2">Eliminar</button>
                        </form>

                        @if ($estadoFirma !== 'válida')
                            <form action="{{ route('documents.sign', $doc->id) }}"
                                  method="POST" enctype="multipart/form-data" class="inline">
                                @csrf
                                <label class="cursor-pointer text-indigo-600 hover:underline ml-2">
                                    Firmar
                                    <input type="file" name="private_key" class="hidden"
                                           onchange="this.form.submit()">
                                </label>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

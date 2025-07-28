{{-- Vista principal: listado / búsqueda / acciones --}}
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Repositorio de documentos</h2>

    {{-- Búsqueda + botón Subir --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('documents.index') }}" class="w-full md:max-w-md">
            <input  type="text" name="search" value="{{ $search }}"
                    placeholder="Buscar por propietario (correo) o nombre…"
                    class="border rounded px-4 py-2 w-full" />
        </form>

        <a href="{{ route('documents.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-center">
            Subir nuevo documento
        </a>
    </div>

    {{-- mensajes flash --}}
    @foreach (['success' => 'green', 'error' => 'red'] as $msg => $color)
        @if (session($msg))
            <div class="bg-{{ $color }}-100 text-{{ $color }}-800 p-4 rounded mb-4">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    @if ($documents->isEmpty())
        <p class="text-gray-600">No hay documentos que cumplan el criterio.</p>
    @else
        <table class="w-full bg-white shadow rounded text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3">Propietario</th>
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Hash</th>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Firma (mía)</th>
                    <th class="p-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
            @foreach ($documents as $doc)
                @php($estadoFirma = $doc->signatureStatusFor(auth()->user()))
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-3 text-gray-700">{{ $doc->user->email }}</td>

                    {{-- nombre enlaza al detalle --}}
                    <td class="p-3">
                        <a href="{{ route('documents.show', $doc) }}"
                           class="text-blue-700 hover:underline">
                            {{ $doc->original_name }}
                        </a>
                    </td>

                    <td class="p-3 text-gray-500 truncate max-w-[180px]">
                        {{ \Illuminate\Support\Str::limit($doc->file_hash, 40) }}
                    </td>

                    <td class="p-3">{{ $doc->created_at->format('d/m/Y H:i') }}</td>

                    {{-- Estado de MI firma --}}
                    <td class="p-3">
                        @switch($estadoFirma)
                            @case('válida')
                                <span class="text-green-600 font-semibold">Válida</span>
                                @break
                            @case('inválida')
                                <span class="text-red-600 font-semibold">Inválida</span>
                                @break
                            @case('alterado')
                                <span class="text-orange-600 font-semibold">PDF alterado</span>
                                @break
                            @default
                                <span class="text-gray-500">—</span>
                        @endswitch
                    </td>

                    {{-- Acciones --}}
                    <td class="p-3 text-center whitespace-nowrap">
                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank"
                           class="text-blue-600 hover:underline">Ver</a>

                        <a  href="{{ route('documents.download', $doc) }}"
                            class="text-green-600 hover:underline mx-2">Descargar</a>

                        {{-- eliminar sólo si soy dueño --}}
                        @if ($doc->user_id === auth()->id())
                            <form action="{{ route('documents.destroy', $doc) }}"
                                  method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('¿Eliminar permanentemente?')"
                                        class="text-red-600 hover:underline">
                                    Eliminar
                                </button>
                            </form>
                        @endif

                        {{-- Firmar si aún no es válida --}}
                        @if ($estadoFirma !== 'válida')
                            <form action="{{ route('documents.sign', $doc) }}"
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

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10 space-y-6">

    <h2 class="text-2xl font-bold">
        Detalle · {{ $document->original_name }}
    </h2>

    {{-- ╭── Información básica ────────────────────────────╮ --}}
    <div class="bg-white shadow rounded p-4 text-sm space-y-1">
        <p><strong>Propietario:</strong> {{ $document->user->email }}</p>
        <p><strong>Hash almacenado:</strong> {{ $document->file_hash }}</p>
        <p><strong>Subido:</strong> {{ $document->created_at->format('d/m/Y H:i') }}</p>

        <a href="{{ route('documents.download',$document) }}"
           class="text-blue-600 hover:underline">Descargar</a>
    </div>

    {{-- ⚠  Alerta de integridad  --}}
    @if (!$integrity)
        <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-800 p-4 rounded">
            <strong>¡Advertencia!</strong>
            El archivo físico ya no coincide con el hash guardado.  
            Todas las firmas se consideran inválidas hasta que se vuelva a subir el PDF original.
        </div>
    @endif

    {{-- ╭── Tabla de firmas ───────────────────────────────╮ --}}
    <div class="bg-white shadow rounded">
        <h3 class="px-4 py-3 bg-gray-100 font-semibold">Firmas registradas</h3>

        @if($signatures->isEmpty())
            <p class="p-4 text-gray-600">Aún no hay firmas.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-3 text-left">Usuario</th>
                        <th class="p-3 text-left">Fecha</th>
                        <th class="p-3 text-left">Estado</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($signatures as $row)
                    @php
                        // Si el PDF está alterado, todas se marcan como inválidas.
                        $estado = !$integrity
                                  ? 'alterado'
                                  : ($row['valid'] === true   ? 'válida'
                                    : ($row['valid'] === false ? 'inválida'
                                                               : 'sin‑llave'));
                    @endphp
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $row['signature']->user->email }}</td>
                        <td class="p-3">{{ $row['signature']->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-3">
                            @switch($estado)
                                @case('válida')
                                    <span class="text-green-600 font-bold">Válida ✔</span>
                                    @break
                                @case('inválida')
                                    <span class="text-red-600 font-bold">Inválida ✖</span>
                                    @break
                                @case('alterado')
                                    <span class="text-orange-600 font-bold">PDF alterado</span>
                                    @break
                                @default
                                    <span class="text-gray-500">Sin llave pública</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <a href="{{ route('documents.index') }}"
       class="inline-block text-sm text-indigo-600 hover:underline">&larr; volver al listado</a>

</div>
@endsection

{{-- Formulario de carga de pdf --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Subir documento PDF</h2>
    {{-- Errores de validacion --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label for="document" class="block font-semibold mb-1">Seleccionar archivo PDF:</label>
            <input type="file" name="document" id="document" accept=".pdf" required class="border p-2 w-full">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Subir documento
        </button>
    </form>
</div>
@endsection

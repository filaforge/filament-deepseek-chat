@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ollama Chat Settings</h1>
    <form action="{{ route('ollama-chat.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="api_key">API Key</label>
            <input type="text" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', $settings->api_key) }}" required>
        </div>

        <div class="form-group">
            <label for="model">Model</label>
            <select class="form-control" id="model" name="model">
                <option value="model1" {{ $settings->model == 'model1' ? 'selected' : '' }}>Model 1</option>
                <option value="model2" {{ $settings->model == 'model2' ? 'selected' : '' }}>Model 2</option>
                <option value="model3" {{ $settings->model == 'model3' ? 'selected' : '' }}>Model 3</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
@endsection
@if (!empty($errors))
    @if (is_string($errors))
        <div class="alert alert-{{ $type }}">
            <small class="d-block">{{ $errors }}</small>
        </div>
    @elseif (is_object($errors) && method_exists($errors, 'any'))
        @if ($errors->any())
            <div class="alert alert-{{ $type }}">
                @foreach ($errors->all() as $error)
                    <small class="d-block">{{ $error }}</small>
                @endforeach
            </div>
        @endif
    @endif
@endif

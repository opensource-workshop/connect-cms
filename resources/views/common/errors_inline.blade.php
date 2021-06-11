@if ($errors && $errors->has($name))
    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first($name)}}</div>
@endif

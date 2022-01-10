{{--
 * インライン エラー表示テンプレート
 *
 * @param $name inputのname
 * @param $class divタグの追加cssクラス (任意)
--}}
@php
    $class = isset($class) ? $class : null;
@endphp

@if ($errors && $errors->has($name))
    <div class="text-danger {{$class}}"><i class="fas fa-exclamation-triangle"></i> {{$errors->first($name)}}</div>
@endif

{{--
 * 登録画面(input file)テンプレート。
--}}
@php
    $default_extensions = \App\Plugins\User\Forms\FormsUploadHelper::normalizeExtensions(
        config('forms.upload.allowed_extensions', [])
    );
    $allowed_extensions = \App\Plugins\User\Forms\FormsUploadHelper::resolveAllowedExtensions(
        $default_extensions,
        $form_obj->rule_file_extensions
    );
    $accept_attr = \App\Plugins\User\Forms\FormsUploadHelper::toAcceptAttribute($allowed_extensions);
@endphp
<input name="forms_columns_value[{{$form_obj->id}}]" type="{{$form_obj->column_type}}" id="{{$label_id}}" @if ($accept_attr) accept="{{$accept_attr}}" @endif>
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])

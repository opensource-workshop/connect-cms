{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h2 style="text-align: center; font-size: 28px;">【{{$method->method_title}}】</h2>
<br />

{!!$method->method_desc!!}<br />
{!!$method->method_detail!!}<br />
<br />

@foreach($method->getImgArgs() as $img_arg)
    <img src="{{\Storage::disk('manual')->path('html')}}/{{$img_arg["path"]}}.png">
@endforeach


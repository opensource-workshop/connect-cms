{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

{{--
<table nobr="true">
    <tr>
        <td>
--}}
            <h3 style="text-align: center; font-size: 24px;"><u>{{$method->method_title}}機能</u></h3>
            <br />
            {!!$method->method_desc!!}<br />
            {!!$method->method_detail!!}<br />
            @foreach($method->getImgArgs() as $img_arg)
                <p>
                    @if($img_arg["name"])
                        【{{$img_arg["name"]}}】<br />
                    @else
                        【画像：{{$loop->iteration}}】<br />
                    @endif

                    <img src="{{\Storage::disk('manual')->path('html')}}/{{$img_arg["path"]}}.png" style="width: 400px;">

                    @if(array_key_exists("comment", $img_arg))
                        <div>
@php
/*
$tmp = $img_arg["comment"];
//$tmp = str_replace('<ul class="mb-0">', '', $tmp);
$tmp = str_replace('<ul class="mb-0">', '<ul>', $tmp);
//$tmp = str_replace('</ul>', '', $tmp);
//$tmp = str_replace('<li>', '', $tmp);
//$tmp = str_replace('</li>', '', $tmp);
echo $tmp;
*/
@endphp
                            {!! $img_arg["comment"] !!}
                        </div>
                    @endif
                </p>
{{--
            <div style="text-align: center;">
                <img src="{{\Storage::disk('manual')->path('html')}}/{{$img_arg["path"]}}.png" style="width: 400px;">
            </div>
--}}
            @endforeach
{{--
        </td>
    </tr>
</table>
--}}
<br />

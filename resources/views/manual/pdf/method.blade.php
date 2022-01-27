{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h3 style="text-align: center; font-size: 20px;">【{{$method->method_title}}】</h3>
<br />
{!!$method->method_desc!!}<br />
{!!$method->method_detail!!}<br />
<br />
@foreach($method->getImgArgs() as $img_arg)
    <table nobr="true">
        <tr>
            <td style="width: 5%;"></td>
            <td style="width: 90%;">
                @if($img_arg["name"])
                    【{{$img_arg["name"]}}】
                @else
                    【画像：{{$loop->iteration}}】
                @endif
                <br />

                    <img src="{{\Storage::disk('manual')->path('html')}}/{{$img_arg["path"]}}.png">
            </td>
            <td style="width: 5%;"></td>
        </tr>
    </table>
    @if(array_key_exists("comment", $img_arg))
        <div>
            {!! $img_arg["comment"] !!}
        </div>
    @endif
@endforeach

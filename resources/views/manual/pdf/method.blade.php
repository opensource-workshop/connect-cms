{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<h3 style="text-align: center; font-size: 20px;">【{{$method->method_title}}】</h3>
<br />
{!!$method->method_desc!!}<br />
{!!$method->method_detail!!}<br />
<br />
@foreach($method->getImgArgs() as $img_arg)
@if (\Storage::disk("screenshot")->exists($img_arg["path"] . ".png"))
    <table nobr="true">
        <tr>
            @php
                // ここでは、まだ、manual 側に画像ができていないので、screenshot を参照。
                $image_info = getimagesize(\Storage::disk("screenshot")->path($img_arg["path"] . ".png"));
                $width = $image_info[0];
                if ($width > 800) {
                    $td_width = [10, 80];
                } else {
                    $td_width = [30, 40];
                }
            @endphp
            <td style="width: {{$td_width[0]}}%;"></td>
            <td style="width: {{$td_width[1]}}%;">
                @if ($loop->index > 0)
                    <br /><br />
                @endif

                @if($img_arg["name"])
                    【{{$img_arg["name"]}}】
                @else
                    【画像：{{$loop->iteration}}】
                @endif
                <br />
                <img src="{{\Storage::disk('manual')->path('html')}}/{{$img_arg["path"]}}.png">
            </td>
            <td style="width: {{$td_width[0]}}%;"></td>
        </tr>
    </table>
    @if(array_key_exists("comment", $img_arg))
        {!! $img_arg["comment"] !!}
    @endif
@endif
@endforeach
{{-- 差し込み --}}
{!!$method->getInsertionPdf('method', 'foot')!!}

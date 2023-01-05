{{--
 * CMS仕様書の表紙
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category CMS仕様書
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br /><br /><br /><br />
<h1 style="text-align: center; font-size: 32px;">CMS 仕様書</h1>
<h1></h1>

<h1 style="text-align: center; font-size: 26px;">{{$about_title}}</h1>

{{-- table を分けないと、&nbspの字下げが効かなかった。(TCPDFの処理が原因と推測される) --}}
@foreach ($about_body_lines as $about_body)
<table border="0">
    <tr nobr="true">
        <td style="width: 5%;"></td>
        <td style="width: 90%;">
                &nbsp;&nbsp;{{$about_body}}<br />
        </td>
        <td style="width: 5%;"></td>
    </tr>
</table>
@endforeach

<h1 style="text-align: center; font-size: 26px;">{{$contact_title}}</h1>
<table border="0">
    <tr nobr="true">
        <td style="width: 5%;"></td>
        <td style="width: 90%;">
{{-- PDFに出力した際に文章にスペースがインデントとして反映されるために左詰めにしています。 --}}
@foreach ($contact_body_lines as $contact_body)
{{$contact_body}}<br />
@endforeach
        </td>
        <td style="width: 5%;"></td>
    </tr>
</table>

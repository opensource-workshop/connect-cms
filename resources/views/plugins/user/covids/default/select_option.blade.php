{{{--
 * 選択肢用テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 感染症数値集計プラグイン(covid)
 --}}
@if ($select_value == $option_value)
    <option value="{{$option_value}}" selected class="text-white bg-primary">{{$option_caption}}</option>
@else
    <option value="{{$option_value}}">{{$option_caption}}</option>
@endif

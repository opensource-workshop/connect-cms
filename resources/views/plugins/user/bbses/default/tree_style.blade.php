{{--
 * 掲示板ツリー形式のスタイル
 *
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}

@php
    $indents = (int)FrameConfig::getConfigValue($frame_configs, BbsFrameConfig::tree_indents, App\Plugins\User\Bbses\BbsesPlugin::max_tree_indents);
@endphp

<style>

{{-- 階層表現 --}}
@for ($i = 0; $i <= $indents; $i++)

    @if ($i === 0)
        .cc-bbs-indent-{{$i}} {
            margin-left: {{$i}};
        }
    @else
        .cc-bbs-indent-{{$i}} {
            margin-left: {{$i}}rem;
        }
    @endif
@endfor


</style>

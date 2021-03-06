{{--
 * 掲示板の投稿日＆投稿者名テンプレート
 *
 * @author 井上　雅人 <inoue@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
<span data-toggle="tooltip" title="{{$post->created_at->format('Y-m-d H:i:s')}}">
    {{$post->created_at->format('Y-m-d')}}
</span>
 [{{$post->created_name}}]

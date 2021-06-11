{{--
 * カテゴリ設定画面（プラグイン側カテゴリ、サイト設定＞カテゴリ設定）の共通エラーメッセージ blade
--}}
@if (count($errors) > 0)
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">
                @foreach($errors->all() as $error)
                    <i class="fas fa-exclamation-triangle"></i> {{$error}}<br />
                @endforeach
            </span>
            {{-- delete: 追加行はすべて入力する必要ない（クラスは任意）ため、コメントアウト
            <span class="text-secondary">
                @if ($errors->has('add_display_sequence') || $errors->has('add_category') || $errors->has('add_color') || $errors->has('add_background_color'))
                    <i class="fas fa-exclamation-circle"></i> 追加行を入力する場合は、すべての項目を入力してください。
                @endif
            </span>
            --}}
        </div>
    </div>
@endif

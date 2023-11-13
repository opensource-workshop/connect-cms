{{--
 * メール設定エラー共通テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category plugins.common
--}}
@if (empty(config('mail.from.address')))
    <div class="card border-danger form-group">
        <div class="card-body">
            <div class="text-danger">
                <i class="fas fa-exclamation-triangle"></i> 送信者メールアドレス (MAIL_FROM_ADDRESS)が空のため、メール送信設定が未設定のようです。「<a href="{{url('/manage/system/mail')}}" target="_blank">システム管理＞メール設定 <i class="fas fa-external-link-alt"></i></a>」から設定を行ってください。<br />
            </div>
        </div>
    </div>
@endif

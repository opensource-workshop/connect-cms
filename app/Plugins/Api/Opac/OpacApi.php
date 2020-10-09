<?php

namespace App\Plugins\Api\Opac;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Core\UsersRoles;
use App\Models\User\Opacs\Opacs;
use App\Models\User\Opacs\OpacsBooks;
use App\Models\User\Opacs\OpacsBooksLents;
use App\Models\User\Opacs\OpacsConfigs;
use App\User;

use App\Traits\ConnectCommonTrait;
use App\Plugins\User\Opacs\OpacsPlugin;
use App\Plugins\Api\ApiPluginBase;

/**
 * Opac関係APIクラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ関係API
 * @package Contoroller
 */
class OpacApi extends ApiPluginBase
{
    use ConnectCommonTrait;

    /**
     *  書籍情報表示
     */
    public function book($request, $opac_id, $key_column, $key_value)
    {
        // API 共通チェック
        $ret = $this->apiCallCheck($request);
        if (!empty($ret['code'])) {
            return $this->encodeJson($ret, $request);
        }

        // 書籍の取得
        list($ret, $opacs_books) = $this->bookImpl($request, $opac_id, $key_column, $key_value);
        if ($ret['code'] != 200) {
            return $this->encodeJson($ret, $request);
        }

        $opacs_book = $opacs_books->first();
        $ret = array('code' => 200,
                     'message'          => '',
                     'title'            => $opacs_book->title,
                     'subtitle'         => $opacs_book->subtitle,
                     'creator'          => $opacs_book->creator,
                     'piblisher'        => $opacs_book->piblisher,
                     'lend_flag'        => $opacs_book->lend_flag,
                     'publication_year' => $opacs_book->publication_year,
                     'lend_flag'        => $opacs_book->lend_flag);
        return $this->encodeJson($ret, $request);
    }

    /**
     *  書籍情報表示
     */
    public function bookImpl($request, $opac_id, $key_column, $key_value)
    {
        // パラメータチェック（キー項目）
        if ($key_column == 'barcode' || $key_column == 'isbn') {
            // キーに指定してもOKな項目。続きへ。
        } else {
            $ret = array('code' => 400, 'message' => '指定された項目が正しくありません。');
            return [$ret, null];
        }

        // 返すデータ取得
        $opacs_books = OpacsBooks::where('opacs_id', $opac_id)->where($key_column, $key_value)->get();
        if ($opacs_books->isEmpty()) {
            $ret = array('code' => 404, 'message' => '指定された書籍が存在しません。');
            return [$ret, null];
        }

        return [array('code' => 200, 'message' => ''), $opacs_books];
    }

    /**
     *  貸し出し中書籍情報取得
     */
    private function getLent($request, $opac_id, $key_column, $key_value, $userid)
    {
        // パラメータチェック（キー項目）
        if ($key_column == 'barcode' || $key_column == 'isbn') {
            // キーに指定してもOKな項目。続きへ。
        } else {
            $ret = array('code' => 400, 'message' => '指定された項目が正しくありません。');
            return [$ret, null];
        }

        // 返すデータ取得
        $opacs_book = OpacsBooks::select('opacs_books.*', 'opacs_books_lents.id as lent_id', 'opacs_books_lents.lent_flag')
                                ->join('opacs_books_lents', 'opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                                ->where('opacs_id', $opac_id)
                                ->where($key_column, $key_value)
                                ->where('opacs_books_lents.lent_flag', '<>', 9)
                                ->where('opacs_books_lents.student_no', $userid)
                                ->first();
        if (empty($opacs_book)) {
            $ret = array('code' => 404, 'message' => '指定された書籍が存在しません。');
            return [$ret, null];
        }

        return [array('code' => 200, 'message' => ''), $opacs_book];
    }

    /**
     *  ユーザ取得
     */
    private function getUser($request, $userid)
    {
        // ユーザの確認
        $user = User::where('userid', $userid)->first();
        if (empty($user)) {
            // ユーザがいない場合は、外部認証ユーザを探しに行く。
            $user_info = $this->getOtherAuthUser($request, $userid);
            if ($user_info['code'] == 200) {
                // 外部認証でユーザ確認。OK
            } else {
                // ローカルにも外部認証にもユーザがいない。NG
                $ret = array('code' => 403, 'message' => '指定されたログインID が見つかりません。');
                return $this->encodeJson($ret, $request);
            }
        } else {
            // ローカルユーザ確認。OK
        }
        return [array('code' => 200, 'message' => ''), $user];
    }

    /**
     *  書籍貸し出し処理
     */
    public function rent($request, $opac_id, $key_column, $key_value, $userid)
    {
        // ユーザの確認
        list($ret, $user) = $this->getUser($request, $userid);
        if ($ret['code'] != 200) {
            return $this->encodeJson($ret, $request);
        }

        // 権限に応じた貸し出し冊数や期間を取得するためにOpac 情報を取得
        $opac = Opacs::find($opac_id);
        if (empty($opac)) {
            $ret = array('code' => 404, 'message' => '指定されたOpac が見つかりません。');
            return $this->encodeJson($ret, $request);
        }

        // 返却期限、貸出最大冊数を取得のため、ユーザのroleを取得
        $original_roles = UsersRoles::where('users_id', $user->id)
                                    ->where('target', 'original_role')
                                    ->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac->id, $original_roles);

        // 貸出最大冊数
        $lent_max_count = OpacsPlugin::getReturnMaxLentCount($opac, $original_roles, $opac_configs);

        // すでに借りている書籍を取得
        $lents = OpacsBooksLents::select('opacs_books_lents.*', 'opacs_books.barcode', 'title')
                                ->leftJoin('opacs_books', function ($join) use ($opac) {
                                    $join->on('opacs_books.id', '=', 'opacs_books_lents.opacs_books_id')
                                         ->where('opacs_books.opacs_id', '=', $opac->id);
                                })
                                ->where('student_no', $user->userid)
                                ->get();

        // 貸し出し可能冊数オーバーチェック
        if ($lent_max_count > count($lents)) {
            // OK
        } else {
            // 貸し出し可能冊数をオーバー。NG
            $ret = array('code' => 403, 'message' => '貸し出し可能冊数の上限まで貸し出し中です。');
            return $this->encodeJson($ret, $request);
        }

        // 書籍の取得
        list($ret, $opacs_books_tmp) = $this->bookImpl($request, $opac_id, $key_column, $key_value);
        if ($ret['code'] != 200) {
            return $this->encodeJson($ret, $request);
        }

        // 禁帯出のチェック
        $not_rent_book = new Collection();
        $opacs_books = new Collection();
        foreach ($opacs_books_tmp as $opacs_book) {
            // 禁帯出を分ける
            if ($opacs_book->lend_flag == '9:禁帯出') {
                $not_rent_book->push($opacs_book);
            } else {
                $opacs_books->push($opacs_book);
            }
        }
        if ($not_rent_book->isNotEmpty() && $opacs_books->isEmpty()) {
            $ret = array('code' => 403, 'message' => '禁帯出の書籍です。');
            return $this->encodeJson($ret, $request);
        }

        // 貸出中になっていないかチェック
        // 貸し出し処理えは、現物があるのはずが基本だけど、一応チェックする。
        // 同じ条件の本が複数ある場合も含めて確認する。複数ある場合は、1冊でも貸出可能ならOK
        $rent_ok_book = null;
        foreach ($opacs_books as $opacs_book) {
            // lent_flag = 9:貸出終了(貸し出し可能)、1:貸し出し中、2:貸し出しリクエスト受付中
            // 最新の1件を判断する。
            $opacs_books_lent = OpacsBooksLents::where('opacs_books_id', $opacs_book->id)->orderBy('id', 'desc')->first();
            if (empty($opacs_books_lent) || $opacs_books_lent->lent_flag == 9) {
                // 貸出可能
                $rent_ok_book = $opacs_book;
                continue;
            }
            if ($opacs_books_lent->lent_flag == 1) {
                $ret = array('code' => 403, 'message' => '貸し出し中');
                return $this->encodeJson($ret, $request);
            } elseif ($opacs_books_lent->lent_flag == 2) {
                $ret = array('code' => 403, 'message' => '貸し出しリクエスト受付中');
                return $this->encodeJson($ret, $request);
            } else {
                $ret = array('code' => 403, 'message' => 'その他エラー。lent_flag = ' . $opacs_books_lent->lent_flag);
                return $this->encodeJson($ret, $request);
            }
        }

        // 権限に応じて設定された、貸出期限の取得
        $lent_max_ts = OpacsPlugin::getReturnMaxDate($opac, $original_roles, $opac_configs);

        // 貸出登録
        OpacsBooksLents::create([
            'opacs_books_id' => $rent_ok_book->id,
            'lent_flag' => 1,
            'student_no' => $userid,
            'return_scheduled' => date('Y-m-d 00:00:00', $lent_max_ts),
        ]);

        $ret = array('code' => 200, 'message' => '貸し出し処理が完了しました。');
        return $this->encodeJson($ret, $request);
    }

    /**
     *  書籍返却処理
     */
    public function returnbook($request, $opac_id, $key_column, $key_value, $userid)
    {
        // ユーザの確認
        list($ret, $user) = $this->getUser($request, $userid);
        if ($ret['code'] != 200) {
            return $this->encodeJson($ret, $request);
        }

        // 書籍＆貸出情報
        list($ret, $opacs_books_lent) = $this->getLent($request, $opac_id, $key_column, $key_value, $userid);
        if ($ret['code'] != 200) {
            return $this->encodeJson($ret, $request);
        }

        // 返却するデータ
        $opacs_books_lent = OpacsBooksLents::find($opacs_books_lent->lent_id);
        $opacs_books_lent->lent_flag = 9;
        $opacs_books_lent->student_no = null;
        $opacs_books_lent->return_date = date('Y-m-d 00:00:00');
        $opacs_books_lent->save();

        // メール送信
        $subject = '図書を返却しました。';
        $content = "";
        $content .= 'ISBN：'     . $opacs_books_lent->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books_lent->title . "\n";
        $content .= '返却日：'   . $opacs_books_lent->return_date . "\n";

        OpacsPlugin::sendMail($opacs_books_lent, $subject, $content);

        // 結果
        $ret = array('code' => 200, 'message' => '返却処理が完了しました。');
        return $this->encodeJson($ret, $request);
    }

    /**
     *  貸し出し状況取得
     */
    public function rentinfo($request, $opac_id, $userid)
    {
        // 貸し出し中書籍
        $lents = OpacsBooksLents::select('opacs_books_lents.*',
                                         'opacs_books.title',
                                         'opacs_books.subtitle',
                                         'opacs_books.creator',
                                         'opacs_books.publisher',
                                         'opacs_books.publication_year'
                                )
                                ->leftJoin('opacs_books', 'opacs_books.id', '=', 'opacs_books_lents.opacs_books_id')
                                ->where('opacs_id', $opac_id)
                                ->where('opacs_books_lents.lent_flag', 1)
                                ->where('opacs_books_lents.student_no', $userid)
                                ->orderBy('opacs_books_lents.return_scheduled', 'desc')
                                ->get();
        if ($lents->isEmpty()) {
            $ret = array('code' => 404, 'message' => '貸し出し中の書籍はありません。');
            return $this->encodeJson($ret, $request);
        }

        $ret = array('code' => 200, 'message' => '', 'lents' => $lents);
        return $this->encodeJson($ret, $request);
    }
}

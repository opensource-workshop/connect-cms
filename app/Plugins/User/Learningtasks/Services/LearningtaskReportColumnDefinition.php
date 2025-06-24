<?php

namespace App\Plugins\User\Learningtasks\Services;

use App\Enums\LearningtaskUseFunction;
// use App\Models\User\Learningtasks\LearningtasksPosts; // SettingChecker経由なので直接は不要
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface; // ★ 実装するインターフェース
use Illuminate\Validation\Rule;

/**
 * ★ レポート評価CSV用カラム定義クラス
 *
 * ColumnDefinitionInterface を実装し、レポート評価インポートに特化した
 * ヘッダーリスト、カラムマップ、バリデーションルールを提供する。
 */
class LearningtaskReportColumnDefinition implements ColumnDefinitionInterface // ★ implements を追加
{
    /**
     * CSVヘッダー名と内部キー名のマッピング定義
     * (レポート評価インポート/エクスポートで関連する可能性のある全カラム)
     */
    private const COLUMN_MAP = [
        'ログインID' => 'userid',
        'ユーザ名' => 'username',
        '提出日時' => 'submitted_at',
        '提出回数' => 'submit_count',
        '本文' => 'report_comment',
        'ファイルURL' => 'file_url',
        '評価' => 'grade',
        '評価コメント' => 'comment',
        '単語数' => 'word_count',
        '字数' => 'char_count',
    ];

    /**
     * 設定有効性を判定するサービス
     * @var \App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker
     */
    private LearningtaskSettingChecker $setting_checker;

    /**
     * コンストラクタ (変更なし)
     * @param LearningtaskSettingChecker $setting_checker 設定有効性を判定するサービス
     */
    public function __construct(LearningtaskSettingChecker $setting_checker)
    {
        $this->setting_checker = $setting_checker;
    }

    /**
     * レポート評価CSVで期待されるヘッダーカラム名のリストを取得する
     * (ColumnDefinitionInterface の実装)
     *
     * @return array ヘッダー文字列の配列
     */
    public function getHeaders(): array
    {
        // エクスポートと同じヘッダーを期待する
        $header_columns = ['ログインID', 'ユーザ名', '提出日時', '提出回数'];
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_comment)) {
            $header_columns[] = '本文';
        }
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_show_word_count)) {
            $header_columns[] = '単語数';
        }
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_show_char_count)) {
            $header_columns[] = '字数';
        }
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_file)) {
            $header_columns[] = 'ファイルURL';
        }
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_evaluate)) {
            $header_columns[] = '評価';
        }
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_evaluate_comment)) {
            $header_columns[] = '評価コメント';
        }
        return $header_columns;
    }

    /**
     * カラムマッピング配列を取得する
     * (ColumnDefinitionInterface の実装)
     *
     * @return array ['CSVヘッダー名' => '内部キー名', ...]
     */
    public function getColumnMap(): array
    {
        // このクラスが扱う全カラムのマッピングを返す
        return self::COLUMN_MAP;
    }

    /**
     * ヘッダー名に対応する内部キー名を取得する
     * (ColumnDefinitionInterface の実装)
     *
     * @param string $header_name CSVヘッダー名
     * @return string|null 内部キー名 or null
     */
    public function getInternalKey(string $header_name): ?string
    {
        return self::COLUMN_MAP[$header_name] ?? null;
    }

    /**
     * ★ レポート評価インポート用の基本バリデーションルールを取得する
     * (ColumnDefinitionInterface の実装)
     *
     * @return array ['内部キー名' => ['ルール', ...], ...]
     */
    public function getValidationRulesBase(): array
    {
        $rules = [
            'userid' => ['required', 'string', 'exists:users,userid'],
        ];

        // 'grade' (評価) のルール
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_evaluate)) {
            $rules['grade'] = [
                'nullable',
                'string',
                Rule::in(['A', 'B', 'C', 'D']),
            ];
        } else {
            $rules['grade'] = ['prohibited'];
        }

        // 'comment' (評価コメント) のルール
        if ($this->setting_checker->isEnabled(LearningtaskUseFunction::use_report_evaluate_comment)) {
            $rules['comment'] = [
                'nullable',
                'string',
                'max:65535',
                'required_with:grade',
            ];
        } else {
            $rules['comment'] = ['prohibited'];
        }

        return $rules;
    }

    /**
     * ★ (任意) レポート評価インポート用のカスタムバリデーションメッセージを取得する
     * (ColumnDefinitionInterface の実装)
     *
     * @return array ['内部キー名.ルール名' => 'メッセージ', ...]
     */
    public function getValidationMessages(): array
    {
        return [
            'userid.required' => 'ログインID列は必須です。',
            'userid.exists' => 'ログインID列の値が無効です（存在するユーザーを指定してください）。',

            'grade.in' => '評価列の値は A, B, C, D のいずれかである必要があります。',
            'grade.prohibited' => '評価機能が無効なため、評価列は入力できません（空にしてください）。',

            'comment.max' => '評価コメント列の文字数が多すぎます（最大65,535文字）。',
            'comment.required_with' => '評価コメント列を入力する場合、評価列にも有効な値（A,B,C,D）が必要です。',
            'comment.prohibited' => '評価コメント機能が無効なため、評価コメント列は入力できません（空にしてください）。',
        ];
    }
}

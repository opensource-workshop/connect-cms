<?php

namespace App\Plugins\User\Learningtasks\Services;

use App\Models\Common\Page;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorInterface;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Plugins\User\Learningtasks\Exceptions\AlreadyEvaluatedException;
use App\Plugins\User\Learningtasks\Exceptions\InvalidStudentException;
use App\Plugins\User\Learningtasks\Exceptions\SubmissionNotFoundException;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * 検証済みのCSVデータ1行を処理し、課題管理の新しい評価記録を作成するクラス
 * (評価済みチェックを含む)
 */
class LearningtaskEvaluationRowProcessor implements RowProcessorInterface
{
    /**
     * ユーザーリポジトリ
     * @var LearningtaskUserRepository
     */
    private LearningtaskUserRepository $user_repository;

    /**
     * 受講生リストのキャッシュ用プロパティ (初回アクセス時にロード)
     * @var \Illuminate\Support\Collection|null
     */
    private ?Collection $cached_students = null;

    /**
     * コンストラクタで UserRepository を注入
     * @param LearningtaskUserRepository $user_repository
     */
    public function __construct(LearningtaskUserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    /**
     * 検証済みの評価データ1行分を処理し、新しい評価レコードを作成する。
     *
     * 最新の提出記録が存在し、かつ、その提出がまだ評価されていない場合にのみ、
     * 新しい評価レコード (task_status = 2) を作成する。
     *
     * @param array $validated_data 検証済みデータ
     * @param LearningtasksPosts $post 課題投稿コンテキスト
     * @param Page $page ページ情報
     * @param User $importer インポート実行ユーザー
     * @return void
     * @throws ModelNotFoundException ユーザーが見つからない場合
     * @throws Exception 提出記録が見つからない場合、DB作成に失敗した場合
     * @throws Exception (またはカスタム例外) 最新の提出が既に評価済みの場合
     */
    public function process(array $validated_data, LearningtasksPosts $post, Page $page, User $importer): void
    {
        // 対象となるユーザーを特定
        try {
            $user = User::where('userid', $validated_data['userid'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("指定されたログインIDのユーザーが見つかりません: " . $validated_data['userid'], $e->getCode(), $e);
        }

        // 受講生チェック
        $this->ensureUserIsValidStudent($user, $post, $page);

        // このユーザー/課題における最新の「提出」記録を取得
        $latest_submit = LearningtasksUsersStatuses::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('task_status', 1)
            ->orderByDesc('id')
            ->first();

        if (!$latest_submit) {
            throw new SubmissionNotFoundException("評価対象の提出記録がユーザー ({$user->userid}) に見つかりません。");
        }

        // 最新の提出が既に評価済みかチェック
        // ヒューリスティック: 最新提出のIDより大きいIDを持つ評価レコード(task_status=2)が存在するか？
        $already_evaluated = LearningtasksUsersStatuses::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('task_status', 2) // 評価ステータス
            ->where('id', '>', $latest_submit->id) // 最新提出より後のレコード
            ->exists(); // 存在するかどうかだけチェック

        if ($already_evaluated) {
            // 既に評価が存在する場合は、エラーとして例外を投げる
            throw new AlreadyEvaluatedException("ユーザー ({$user->userid}) の最新の提出は既に評価済みのため、インポートできません。");
        }

        // 保存すべき評価データが validated_data に存在するかチェック
        if (!$this->hasEvaluation($validated_data)) {
            Log::info("Skipping evaluation record creation for user {$validated_data['userid']} on post {$post->id}: Validated data does not contain grade to save.");
            return;
        }

        // 新しい評価記録 (task_status = 2) を作成
        // (評価済みチェックを通過した場合のみ実行される)
        LearningtasksUsersStatuses::create([
            'post_id'       => $post->id,
            'user_id'       => $user->id,
            'task_status'   => 2,
            'grade'         => $validated_data['grade'] ?? null,
            'comment'       => $validated_data['comment'] ?? null,
            'upload_id'     => null,
            'examination_id'=> 0,
            'created_id'    => $importer->id,
            'created_name'  => $importer->name,
        ]);
    }

    // ===============================================
    // Private Helper Methods
    // ===============================================

    /**
     * 指定されたユーザーが、与えられた課題投稿の有効な受講生であるか検証する（キャッシュ利用）。
     * 受講生でない場合は InvalidStudentException をスローする。
     *
     * @param User $user 検証対象のユーザー
     * @param LearningtasksPosts $post 対象の課題投稿
     * @param Page $page 対象のページ
     * @return void
     * @throws InvalidStudentException ユーザーが有効な受講生でない場合
     * @throws Exception ページ情報がないなど、チェック自体が実行できない場合 (UserRepository側で発生する可能性)
     */
    private function ensureUserIsValidStudent(User $user, LearningtasksPosts $post, Page $page): void
    {
        // キャッシュがまだなければ取得・格納
        if ($this->cached_students === null) {
            $this->cached_students = $this->user_repository->getStudents($post, $page);
        }

        // キャッシュされたリストに含まれているかチェック
        if (!$this->cached_students->contains('id', $user->id)) {
            // 含まれていなければ例外をスロー
            throw new InvalidStudentException("ユーザー ({$user->userid}) はこの課題 ({$post->post_title}) の受講生として登録されていません。");
        }
    }

    /**
     * 保存すべき評価データが validated_data に存在するかチェック
     *
     * @param array $validated_data
     * @return bool
     */
    private function hasEvaluation(array $validated_data): bool
    {
        $has_grade_data = array_key_exists('grade', $validated_data)
                           && !is_null($validated_data['grade'])
                           && $validated_data['grade'] !== '';
        // 評価が設定されていればOK
        if ($has_grade_data) {
            return true;
        }
        return false;
    }
}

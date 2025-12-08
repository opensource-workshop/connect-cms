# リポジトリガイドライン

## プロジェクト構成・モジュール
- `app/`: Laravel アプリ本体（モデル、コントローラ、ポリシー、ジョブなど）
- `config/`, `routes/`, `resources/`, `public/`: 設定、ルート、Blade/アセット、Web ルート
- `database/`: マイグレーション・シーダー、`storage/`: 実行時ファイル（コミット禁止）
- `tests/`: `Unit/`、`Feature/`、`Browser/`（Laravel Dusk）
- `docker/` と `docker-compose.yml`: ローカル開発用スタック

## ビルド・テスト・開発コマンド
- PHP 依存解決: `composer install`
- JS/CSS 依存解決: `npm ci`
- アセットビルド: `npm run dev` / `npm run prod`
- ウォッチ/ホットリロード: `npm run watch` / `npm run hot`
- ローカル起動: `php artisan serve`（`.env` と `APP_KEY` が必要）
- PHP Lint: `composer phpcs`（自動整形: `composer phpcbf`）
- ユニット/Feature テスト: `composer phpunit`
- ブラウザテスト: `vendor/bin/phpunit -c phpunit.dusk.xml` または `php artisan dusk`

## コーディングスタイル・命名
- `.editorconfig`: LF、4 スペース、末尾空白なし
- PHP: PSR-2 ベース、詳細は `phpcs.xml`。`composer phpcs` で確認
- オートロード: PSR-4（`App\` → `app/`、`Tests\` → `tests/`）
- 命名: クラスは StudlyCase、メソッドは camelCase、定数はプロジェクト規約に従う
- JS/CSS: Laravel Mix（`webpack.mix.js`）。StyleCI は Laravel プリセット
- ユーザ向けプラグインの共通Bladeはテンプレート選択に出ないよう、`resources/views/plugins/user/{plugin}` 直下に配置する（`common/` 配下は使用しない）。

## テスト指針
- フレームワーク: PHPUnit（`phpunit.xml`）、Laravel Dusk（`phpunit.dusk.xml`）
- 配置: Unit → `tests/Unit`、Feature → `tests/Feature`、Browser → `tests/Browser`
- 命名: `*Test.php` で終える。一つのクラスに一つの関心事
- カバレッジ: ビジネスロジックを重点的に。外部サービスは可能な限りモック

## コミット・PR ガイドライン
- コミット: `fix:`, `feat:`, `refactor:`, `docs:` などの接頭辞。命令形・現在形
- PR: サマリ・理由・関連 Issue（例: `Fixes #123`）を入れる。UI 変更はスクショ/操作手順を添付
- 品質ゲート: `composer phpcs`、`composer phpunit`、アセットビルドをローカルでパスさせる。`storage/` や `public/mix-manifest.json` 等の生成物はコミットしない
- PR の想定読者: 一般利用者や非エンジニアのレビュアーも読む前提で、平易な表現と必要な補足を入れる
- PR 本文の構成: 変更の概要 → 背景と目的 → 特記事項（影響範囲・除外事項など）の順で記載する

## セキュリティ・設定
- `.env.example` を `.env` にコピーし、`php artisan key:generate` でキー生成
- 秘密情報はコミットしない。テスト専用の上書きは `.env.testing` を利用
- ローカル/Docker 利用時は `config/` と `storage/` の権限を確認

## ブランチ・PR フロー
- ブランチ名: 簡潔なケバブケース例 `fix/databases-preserve-back-and-selection`、`feat/<area>-<short-desc>`
- 変更を絞ってステージング: `git add <paths>`
- コミットメッセージ: Conventional Commits（スコープ任意）例 `fix(databases): <summary>`
- プッシュ: `git push -u origin <branch>`
- PR 作成ルール:
  - タイトルはプロジェクト規約に従い `[プラグイン名] <要約>` で始める（例: `[データベース] ...`）
  - `.github/PULL_REQUEST_TEMPLATE.md` を使い、概要/確認観点/DB 変更有無などを埋める
  - タイトルと本文は「ですます調」
  - GitHub CLI 例: `gh pr create --base master --head <branch> --title "[プラグイン名] <要約>" --body-file .github/PULL_REQUEST_TEMPLATE.md`
    - ローカルで本文を編集した場合: `gh pr edit <pr-number> --title "[プラグイン名] <要約>" --body-file pr_body.md`
- レビュー依頼前:
  - `composer phpcs` と `composer phpunit` をパスさせる
  - ラベルとレビュアーを設定する

## git worktree 利用（並行作業）
- 既存ワークツリーを汚さないよう、タスクごとに worktree を作成
- 例:
  - `cd /home/yuki/src`
  - 初回のみ `mkdir -p worktrees`
  - `git -C connect-cms worktree add ../worktrees/<task-name> -b <branch-name> master`
- タスクごとに別の codex セッションで作業し、完了したらプッシュを確認後 `git worktree remove ../worktrees/<task-name>`。不要なブランチは別途削除
- stale が残れば `git worktree prune`。`storage/` と `vendor/` は共有されないため必要に応じてセットアップ

## 回答方針
- 回答は日本語で行う

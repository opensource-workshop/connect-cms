# CLAUDE.md

このファイルは、Claude Code (claude.ai/code) がこのリポジトリのコードを操作する際のガイダンスを提供します。

## プロジェクト概要

Connect-CMSは、Webサイトを簡単に作成するためのコンテンツ管理システムです。Laravel 8フレームワークをベースに構築されており、プラグインベースのアーキテクチャを採用しています。

## 開発コマンド

### コードスタイル
```bash
# PHP Code Sniffer（コードスタイルチェック）
composer run phpcs

# PHP Code Beautifier and Fixer（コードスタイル自動修正）
composer run phpcbf

# 特定のファイルやディレクトリのみをチェック
composer run phpcs-any -- path/to/file
composer run phpcbf-any -- path/to/file
```

### テスト
```bash
# PHPUnit テストの実行
composer run phpunit
# または
./vendor/bin/phpunit

# 特定のテストファイルの実行
./vendor/bin/phpunit tests/Feature/SomeTest.php
```

### フロントエンド
```bash
# 開発環境向けビルド
npm run dev

# 本番環境向けビルド
npm run prod

# ファイル監視（開発中）
npm run watch
```

## アーキテクチャ

### プラグインシステム
このシステムの核心は `app/Plugins/` にあるプラグインアーキテクチャです：

- **User プラグイン**: フロントエンド機能（Blog、Database、Forms、Reservations等）
- **Manage プラグイン**: 管理機能（UserManage、PageManage、SystemManage等）
- **Api プラグイン**: API機能
- **Mypage プラグイン**: マイページ機能

各プラグインは `PluginBase` クラスを継承し、独自の機能を実装します。

### データベース設計
- `pages`: ページ階層構造（Nested Set Pattern）
- `frames`: ページ内のフレーム（プラグインの配置エリア）
- `buckets`: 各プラグインのデータコンテナ
- `plugins`: プラグインの設定情報

### 権限システム
- `users_roles`: ユーザーと権限の関連
- `buckets_roles`: バケット（プラグインデータ）レベルの権限
- `page_roles`: ページレベルの権限

## 重要な設定

### PHPコーディング規約
- phpcs.xml に従った PSR-12 準拠のコーディングスタイル
- プライベートフィールドには `_` プレフィックスを使用

## 主要なファイル構造

- `app/Plugins/`: プラグイン本体
- `app/Enums/`: 列挙型定義
- `app/Models/`: Eloquentモデル
- `database/migrations/`: データベースマイグレーション
- `resources/views/plugins/`: プラグインのビューファイル
- `public/`: 公開ファイル（CSS、JS、画像等）

## 開発時の注意点

### プラグイン開発
新しいプラグインを作成する場合は、既存のプラグインを参考にして以下の構造に従ってください：
- プラグインクラス（UserPluginBase継承）
- モデル（必要に応じて）
- ビューファイル
- マイグレーションファイル

### データベース操作
- データベースの変更は必ずマイグレーションファイルを作成
- `created_id`、`updated_id` フィールドを適切に設定
- 論理削除（`deleted_at`）を適切に使用

### テスト作成
- 新機能には対応するテストを作成
- Feature テストとUnit テストを適切に使い分け
- テストデータベースは `db-testing` データベースを使用

## ブランチ戦略

### GitHub Flow
このプロジェクトではGitHub Flowを採用しています：

1. **mainブランチ**: 常にデプロイ可能な状態を保つ
2. **featureブランチ**: 新機能や修正は `feature/` プレフィックスで分岐
3. **ワークフロー**:
   - `master` から `feature/task-name` ブランチを作成
   - 開発・テスト・コミット
   - Pull Requestを作成（`feature/task-name` → `master`）
   - レビュー・承認後にマージ
   - featureブランチを削除

### Pull Requestルール
PRを作成する際は以下のルールに**必ず**従ってください：

**タイトル形式**: `[Plugin/Area] 具体的な変更内容の説明`
- 例: `[キャビネット] ZIPファイルを解凍してアップロードする機能を追加しました`
- プラグイン名やエリア名を角括弧で明記
- ですます調で記述
- リリースノートに使用されるため、ユーザーが理解しやすい説明にする

**PRテンプレート**: `.github/PULL_REQUEST_TEMPLATE.md` に従って作成
- 概要: 変更の背景・目的・内容
- レビュー完了希望日
- 関連PR/Issues
- 参考情報
- DB変更の有無
- チェックリスト

詳細: https://github.com/opensource-workshop/connect-cms/wiki/Pull-requests-Rule

## 記録すべき内容

- 現在のプロジェクトの状態や、今後の開発で注意すべき点を随時メモする
- 特に、複雑な実装や重要な設計判断の背景を記録する
- **プラグイン固有の知識**: 各プラグインの実装パターンや特殊な仕様は積極的に記録し、今後の開発効率化に活用する

## 効率的な開発のためのベストプラクティス

### 検索とファイル読み込み
- 複数回にわたる検索が必要な場合は、Taskツールを使用してトークン消費を削減
- 複数ファイルの同時読み込みには、並行してReadツールを実行
- 特定のファイルパスが分かっている場合は、GrepやGlobよりもReadツールを直接使用

### コード共通化
- 重複するコードパターンを発見した場合は、DRY原則に従って共通テンプレートやヘルパー関数を作成
- 特にビューファイルでは、includeを活用した部分テンプレート化を推奨

### フォームプラグイン固有の知識
- フォームモードは `form`（通常）と `questionnaire`（アンケート）の2種類
- アンケートモードでは `index_tandem.blade.php` と `forms_confirm_tandem.blade.php` を使用
- Captcha機能は `mews/captcha` ライブラリを使用、APIエンドポイント: `/captcha/api/flat`
- 共通エラー表示は `@include('plugins.common.errors_inline', ['name' => 'field_name'])` を使用

## 注意点

- git comment や PR本文にはclaudeで生成した内容の文章は残さない
- git commentにはprefixを付ける
- PR

## 禁止事項
- masterブランチへのcommitは禁止です。featureブランチを作ること。 

# COACHTECH お問い合せフォーム

## 概要

COACHTECHの確認テストで作成した成果物です。
一般ユーザーが利用するお問い合せフォームで、管理者はログイン後にその内容について確認・管理することができるものです。

これまでに学習したバックエンド技術を実践的にアウトプットし、復習箇所を洗い出すことを目的としています。

## 環境構築手順
- Dockerコマンドを実行し、Laravel10.xを指定してプロジェクトを作成する。
- プロジェクト作成後、contact-form-appに移動し、Laravel Sailをインストールする。
- Sailの設定をパブリッシュ(MySQLを選択)する
- .envファイルを開き、データベース接続情報が一致しているかを確認する。
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

- フロントエンドは提供された物を使用する(今回はTailwind CSS)
- sail npm installでNPM依存パッケージをインストールする
- sail npm install -D tailwindcss@^3.4.0 postcss autoprefixer
- sail npm install alpinejs
- 上記二つのコマンドでTailwind CSSをインストールする。
- sail npx tailwindcss init -pで設定ファイルを生成する。
- Tailwind CSSのテンプレートパスを設定する。content: []の中身を、"./resources/**/*.blade.php",
    "./resources/**/*.js ",
   "./resources/**/*.vue",にする。

- 提供されたリポジトリからresourceフォルダをダウンロード、Finderで開く。（contact-form-appのresourceフォルダは削除しておく）。
- 提供された方のresourceフォルダを、contact-form-appのプロジェクトフォルダにコピーする。
- sail npm run devをターミナルで入力し、実行したままにする。

- compose.yamlを開き、myaqlサービスの後に以下の設定をする。(mysqlの、timeout: 5sの後に追加する、段落はmysqlと同じところで)
phpmyadmin:
        image: 'phpmyadmin:latest'
        ports:
            - '${FORWARD_PHPMYADMIN_PORT:-8080}:80'
        environment:
            PMA_HOST: mysql
            PMA_USER: '${DB_USERNAME}'
            PMA_PASSWORD: '${DB_PASSWORD}'
        networks:
            - sail
        depends_on:
            - mysql

- ./vendor/bin/sail up -dでSailをバックグラウンドで起動
- echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc　でエイリアスを設定して"sail"だけでコマンドを実行できるようにする。
- sail artisan key:generate でアプリケーションキーを作成する。
- sail artisan migrate --seedでテーブルを作成し、初期データを投入する。

## ER図
- プロジェクトフォルダ内に記載(名称：リレーションシップ)

## 使用技術および環境
- MacBook
- PHP 8.2
- Laravel 10.50.4
- DB MySQL 8.0
- Webサーバー　Nginx
- フロントエンド Vite, Tailwind CSS^3.4.0
- 開発ツール Docker, Laravel, Sail, phpMyAdmin

## APIエンドポイント一覧
- GET   /api/v1/contacts
- GET   /api/v1/contacts/{contact}
- POST   /api/v1/contacts
- PUT   /api/v1/contacts/{contact}
- DELETE   /api/v1/contacts/{contact}

## 動作確認
- http://localhost/
- http://localhost/login

## 製作者
前畑　野々花
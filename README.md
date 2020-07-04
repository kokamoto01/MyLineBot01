## このアプリについて
<p>「LINEから外食を探せればわざわざサイト開いて検索したり、グルメアプリをインストールする必要ないじゃん。」 </p>
<p>そんな適当な考えで作りました。</p>

## 使い方
<p>「北千住 ハンバーガー」のように、「場所 グルメの名前」 と入力することで検索することができます。</p>
<p>下にあるQRコードを読み込み、友達登録をお願いします。</p>


![QRコード](./484gilot.png)

## 使った技術
<p>Laravel 6.8</p>
<p>Laradock (https://laradock.io/)</p>
<p>ぐるなびAPI (https://api.gnavi.co.jp/api/manual/restsearch/)</p>
<p>LINE Messaging API (https://developers.line.biz/ja/docs/messaging-api/)</p>
<p>Guzzle 6(PHPのHTTPクライアントライブラリ) (http://docs.guzzlephp.org/en/stable/)</p>

## 苦労したところ
<p>FlexMessageの組み立て。line独自の仕様があり、理解するのが困難だった。</p>
<p>app/Services/RestaurantBubbleBuilder.phpの設計。</p>
<p>RestaurantBubbleBuilderに飲食店1件分の情報をもたせること。</p>


## 参考にしたもの
<p>https://developers.line.biz/ja/docs/messaging-api/flex-message-elements/</p>
<p>https://qiita.com/clustfe/items/f9ff2b12da7a501197f8</p>

## アプリの詳細
<p>https://docs.google.com/presentation/d/1JpH1_Yk7HSxIih1J_9eS6exoarT6WZUcIg6MaFcLvgk/edit?usp=sharing</p>

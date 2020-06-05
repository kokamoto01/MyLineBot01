## このアプリについて
LINEから外食を探したい、そんな考えで作りました。

## 使い方
「北千住 ハンバーガー」のように、「場所 グルメの名前」
と入力することで検索することができます。
下にあるQRコードを読み込み、友達登録をお願いします。
![画像リンク](./484gilot.png) 

## 使ったもの
ぐるなびAPI (https://api.gnavi.co.jp/api/manual/restsearch/)
LINE Messaging API
Laravel 6.8
Laradock

## 苦労したところ
FlexMessageの組み立て。
line独自の仕様があり、理解するのが困難だった。
app/Services/RestaurantBubbleBuilder.phpの設計。
飲食店1件分の情報をもたせること。


## 参考にしたもの
https://developers.line.biz/ja/docs/messaging-api/flex-message-elements/
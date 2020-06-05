<?php

namespace App\Services;

// ぐるなびAPIから飲食店検索結果を取得するには、LaravelからぐるなびAPIへGETリクエストを行う必要がある。今回はGETなどのHTTPリクエストを行うのにGuzzleというライブラリを使った。
use GuzzleHttp\Client;

// ぐるなびAPIの仕様(https://api.gnavi.co.jp/api/manual/restsearch/)
// パラメータkeyidには、ぐるなびより提供されたアクセスキーを設定する
// その他のパラメータにはname(店舗名)、freeword(フリーワード)などさまざまなものが存在し、これらが検索条件となる
// パラメータkeyidは必須だが、それ以外の検索条件となるパラメータに関しては指定してもしなくても良い
// ただし、パラメータがkeyidのみの場合は、パラメータ不足のため、エラーとなる
// rest(レストラン情報)というパラメータの出現回数に関して「複数回」と記載されており、検索結果は複数件が一度に返ってくる
// rest(レストラン情報)の中には、さらにname(店舗名称)やurlなどのさまざまなパラメータがある

// ユーザーが送信したメッセージを、ぐるなびAPIのリクエストパラメータのfreewordに設定する
// 返ってきたぐるなびAPIのレスポンスからname(店舗名称)とurlを取り出し、ユーザーへ返信する

class Gurunavi
{
  private const RESTAURANTS_SEARCH_API_URL = 'https://api.gnavi.co.jp/RestSearchAPI/v3/';
 
  // searchRestaurantメソッドは、検索ワードを渡すと、ぐるなびAPIのレスポンスを配列で返す。
  // $wordが文字列じゃないとsearchRestaurantsメソッドが機能しなくなるので、型宣言しておいたほうがいい？
  // 戻り値は絶対配列じゃないとだめ。
  public function searchRestaurants(string $word): array
  {
    // GuzzleのClientクラスを作成
    $client = new Client();
    // 指定したURLに対してGETリクエストを行い、レスポンスが返ってくる
    // Guzzleのgetメソッド。第一引数には、リクエスト先のURLを渡す。第二引数には、オプションとなる情報を連想配列で渡す
    $response = $client
    ->get(self::RESTAURANTS_SEARCH_API_URL, [
            'query' => [
                'keyid' => env('GURUNAVI_ACCESS_KEY'),
                'freeword' => str_replace(' ', ',', $word), 
            ],
            // Guzzleは、HTTPのステータスコードがエラー系だとClientExceptionが発生する？
            // 例外が出ると処理が中断してメッセージが送れない。なので例外を発生させないようにする。
            'http_errors' => false,
        ]);
        
    // Guzzleは$response->getBody()->getContents()で、レスポンスボディが取り出せる
    // HTTPレスポンスボディを扱いやすくするためにjson形式に変換
    return json_decode($response->getBody()->getContents(), true);
  }
}
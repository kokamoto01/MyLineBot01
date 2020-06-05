<?php

namespace App\Http\Controllers;

use App\Services\Gurunavi;
use App\Services\RestaurantBubbleBuilder; // LINE\LINEBot\MessageBuilder\Flex\ContainerBuilderからインターフェースとして実装されています。
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// LINE Messaging API SDKに従う。(https://github.com/line/line-bot-sdk-php)
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Event\MessageEvent\TextMessage;

// MessageBuilderの詳細 (https://line.github.io/line-bot-sdk-php/namespace-LINE.LINEBot.MessageBuilder.html)
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;

class LineBotController extends Controller
{
    public function index(){
        return view('linebot.index');
    }

    public function restaurants(Request $request){
        // 今回はテストは作っていないので、ログを見れるようにしておく。
        Log::debug($request->header());
        Log::debug($request->input());  // メッセージボディ

        // Messaging APIの仕様に従い、LINEBotクラスを生成
        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        // 友だち追加やメッセージの送信のような出来事をイベントといい、これが発生するとLINEプラットフォームからWebhook URL（ボットサーバー）にHTTPS POSTリクエストが送信されます。
        // LINEチャネルからのPOSTリクエストのメッセージボディは、eventsとdestinationという2つの要素で構成されています。

        // LINEチャネルからのリクエストには改ざんされないように署名の情報が含まれている。
        // Laravelの機能validateSignatureメソッドを使い、署名を検証する。
        $signature = $request->header('x-line-signature');
        if (!$lineBot->validateSignature($request->getContent(), $signature)){
            abort(400, 'invalid signature');
        }

        // LINEBotクラスのparseEventRequestメソッドが、POSTリクエストからイベント情報を取り出す
        // LINE\LINEBot\Event\MessageEvent\TextMessageクラスを返すはず。
        $events = $lineBot->parseEventRequest($request->getContent(), $signature);
        Log::debug($events);

        // eventsは複数存在することがあるので、繰り返す。
        foreach($events as $event){
            // LINE\LINEBot\Event\MessageEvent\TextMessageクラスが返ってこなかった。
            if(!($event instanceof TextMessage)){
                Log::debug('non text message has come');
                continue;
            }

            // Gurunaviクラスを作成してインスタンス化。
            $gurunavi = new Gurunavi();
            // searchRestaurantsメソッドは、ユーザーからのメッセージのテキストを使って(getTextメソッドで取り出している。)ぐるなびのレストラン検索を行い、検索結果の連想配列が$gurunaviResponseに代入される。
            $gurunaviResponse = $gurunavi->searchRestaurants($event->getText());

            // errorであるキーが存在するかを調べ、存在する場合はエラーメッセージを返信する。
            if(array_key_exists('error', $gurunaviResponse)) {
                $replyText = $gurunaviResponse['error'] [0] ['message'];
                // Messaging APIでは、各メッセージへの返信に応答トークンを必要とする。(https://developers.line.biz/ja/reference/messaging-api/#retry-api-request)
                $replyToken = $event->getReplyToken();
                $lineBot->replyText($replyToken, $replyText);
                continue;
            }

            $bubbles = [];
            foreach ($gurunaviResponse['rest'] as $restaurant) {
                $bubble = RestaurantBubbleBuilder::builder();
                $bubble->setContents($restaurant); // 飲食店検索結果の情報を各種プロパティに代入。
                $bubbles[] = $bubble;
            }

            // FlexMessageBuilderクラスのbuilderメソッドは、空のインスタンスを生成してくれる。
            $carousel = CarouselContainerBuilder::builder();
            $carousel->setContents($bubbles);

            // FlexMessageBuilderクラスのbuilderメソッドは、空のインスタンスを生成してくれる。
            $flex = FlexMessageBuilder::builder();
            $flex->setAltText('飲食店検索結果');  // LINEのトークの名前の下に表示されるアレ
            $flex->setContents($carousel); //

            // LineBotクラスのreplyMessageメソッド。FlexMessageはjson形式のため。
            $lineBot->replyMessage($event->getReplyToken(), $flex);
        }
    }
}

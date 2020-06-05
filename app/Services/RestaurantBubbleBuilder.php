<?php

namespace App\Services;

use Illuminate\Support\Arr;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder;

class RestaurantBubbleBuilder implements ContainerBuilder
{
    private const GOOGLE_MAP_URL = 'https://www.google.com/maps';
    private $imageUrl; // お店の画像
    private $name; // 店名
    private $closestStation; // 最寄り駅
    private $minutesByFoot; // 徒歩何分か
    private $category; // ジャンル名
    private $budget; // 予算
    private $latitude; // 緯度
    private $longitude; // 経度
    private $phoneNumber; // お店の電話番号
    private $restaurantUrl; // お店のサイトのURL
    
    //  自クラスの空のインスタンスを返すメソッド builder()
    public static function builder(): RestaurantBubbleBuilder
    {
        return new self();
    }

    // ぐるなびAPIの検索結果のレスポンスをプロパティに代入するメソッド setContents()
    // ぐるなびAPIのレスポンスには、正常に検索結果が得られた時であれば各キーは必ず存在するが、
    // 念のためArr::get関数を使用し、想定外のレスポンスであった場合でも処理が中断しないようにした。
    public function setContents(array $restaurant): void
    {
        $this->imageUrl = Arr::get($restaurant, 'image_url.shop_image1', null); 
        $this->name = Arr::get($restaurant, 'name', null);
        $this->closestStation = Arr::get($restaurant, 'access.station', null);
        $this->minutesByFoot = Arr::get($restaurant, 'access.walk', null);
        $this->category = Arr::get($restaurant, 'category', null);
        $this->budget = Arr::get($restaurant, 'budget', null);
        $this->latitude = Arr::get($restaurant, 'latitude', null);
        $this->longitude = Arr::get($restaurant, 'longitude', null);
        $this->phoneNumber = Arr::get($restaurant, 'tel', null); 
        $this->restaurantUrl = Arr::get($restaurant, 'url', null); 
    }

    // 横に動かすカルーセルタイプとコンテナの中のひとつひとつのレストラン情報単体がバブルタイプのコンテナ。 https://developers.line.biz/ja/docs/messaging-api/using-flex-messages/
    // カルーセルタイプのコンテナ(カルーセルコンテナ)が親で、その中に子となるバブルタイプのコンテナ(バブルコンテナ)が複数ある。

    // バブルコンテナはブロックというもので構成されている。
    // ヒーロー = 画像コンテンツを表示するブロック
    // ヘッダー = メッセージコンテンツを表示するブロック
    // フッター = ボタン・補足情報のブロック

    // ボックスコンポーネント = レイアウトを定義する。
    // 画像コンポーネント = jpegかpngを表示
    
    // FlexContainerの設定。画面の整形用のメソッド build()
    public function build(): array
    {
        $array = [
            'type' => 'bubble',
            // heroで使えるのは画像コンポーネントのみ。
            'hero' => [
                'type' => 'image',
                'url' => $this->imageUrl,
                'size' => 'full',
                'aspectRatio' => '20:13',
                'aspectMode' => 'cover',
            ],
            'body' => [
                // ボックスコンポーネントで店名、概要を表示。
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $this->name,
                        'wrap' => true,
                        'weight' => 'bold',
                        'size' => 'md',
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'margin' => 'lg',
                        'spacing' => 'sm',
                        'contents' => [
                            [
                                'type' => 'box',
                                'layout' => 'baseline',
                                'spacing' => 'xs',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'エリア',
                                        'color' => '#aaaaaa',
                                        'size' => 'xs',
                                        'flex' => 4 // エリアの見出しの割合は4:12
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $this->closestStation . '徒歩' . $this->minutesByFoot . '分',
                                        'wrap' => true,
                                        'color' => '#666666',
                                        'size' => 'xs',
                                        'flex' => 12 // エリアの情報のコンポーネントの割合は4:12
                                    ]
                                ]
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'baseline',
                                'spacing' => 'xs',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'ジャンル',
                                        'color' => '#aaaaaa',
                                        'size' => 'xs',
                                        'flex' => 4 // ジャンルの見出しの割合は4:12
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $this->category,
                                        'wrap' => true,
                                        'color' => '#666666',
                                        'size' => 'xs',
                                        'flex' => 12 // ジャンルの情報のコンポーネントの割合は4:12
                                    ]
                                ]
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'baseline',
                                'spacing' => 'xs',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => '予算',
                                        'wrap' => true,
                                        'color' => '#aaaaaa',
                                        'size' => 'sm',
                                        'flex' => 4
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => is_numeric($this->budget) ? '¥' . number_format($this->budget) . '円' : '不明', // 数値がない場合は不明にする
                                        'wrap' => true,
                                        'maxLines' => 1,
                                        'color' => '#666666',
                                        'size' => 'xs',
                                        'flex' => 12
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'xs',
                'contents' => [
                    [
                        'type' => 'button',
                        'style' => 'link',
                        'height' => 'sm',
                        'action' => [
                            'type' => 'uri',
                            'label' => '地図を見る',
                            'uri' => self::GOOGLE_MAP_URL . '?q=' . $this->latitude . ',' . $this->longitude, // GoogleMapのURI形式
                        ]
                    ],
                    [
                        'type' => 'button',
                        'style' => 'link',
                        'height' => 'sm',
                        'action' => [
                            'type' => 'uri',
                            'label' => '電話する',
                            'uri' => 'tel:' . $this->phoneNumber,
                        ]
                    ],
                    [
                        'type' => 'button',
                        'style' => 'link',
                        'height' => 'sm',
                        'action' => [
                            'type' => 'uri',
                            'label' => '詳しく見る',
                            'uri' => $this->restaurantUrl,
                        ]
                    ],
                    [
                        'type' => 'spacer',
                        'size' => 'xs'
                    ]
                ],
                'flex' => 0
            ]
        ];
        // 画像コンポーネントの画像URLの値が空文字のFlex MessageをLINEチャネルを送っても、LINEチャネルは応答してくれない
        // だから画像がないときヒーローブロックごと消せばLINEチャネルは認識できると考えた。
        if ($this->imageUrl == '') {
            Arr::forget($array, 'hero');
        }
        
        return $array;
    }
}
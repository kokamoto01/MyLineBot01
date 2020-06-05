<?php
// LINEチャネルはブラウザを使わずにLaravelにアクセスするので、
// routes/api.phpに定義する
Route::get('/hello', 'LineBotController@index');
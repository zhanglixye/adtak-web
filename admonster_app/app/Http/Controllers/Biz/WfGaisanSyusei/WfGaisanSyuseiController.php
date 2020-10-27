<?php

namespace App\Http\Controllers\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WfGaisanSyuseiController extends Controller
{
    protected $google_client_secret_json_path;

    protected $google_credential_json_path;

    protected $google_oauth_redirect_uri;

    protected $disk;

    public function __construct()
    {
        $this->google_client_secret_json_path = storage_path('biz/wf_gaisan_syusei/google_client_secret.json');
        $this->google_credential_json_path = 'wf_gaisan_syusei/spreadsheet_credential.json';
        $this->google_oauth_redirect_uri = url('/') . '/biz/wf_gaisan_syusei/oauth2_callback';

        // 使用ディスク
        $this->disk = \Storage::disk('biz');
    }

    /*
     * アクセストークン、アクセストークンの期限、リフレッシュトークン、scopeなどの情報をリクエスト
     *
     * TODO : この処理実行中にアカウントの選択とアカウントへのアクセス許可をブラウザ操作で行う必要があるため、
     *        この処理実行はシステムサポートもしくは業務管理者が行うことを想定。
     *        その場合、どこから実行するかの検討が必要。
     *        基本的には業務詳細画面にボタン設置が望ましいが、全業務共通のテンプレートに1業務単位でのボタン出し分けをするのは避けたい。
     *        現時点では暫定的にこの場所に置いておく。
     */
    public function getGoogleOAuthCredentials()
    {
        // クライアントオブジェクトを作成
        $client = $this->getSpreadsheetClient();

        return redirect($client->getRedirectUri());
    }

    public function oauth2Callback(Request $req)
    {
        $client = $this->getSpreadsheetClient();

        if (!isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();

            return redirect()->away($auth_url);
        } else {
            $client->authenticate($_GET['code']);

            // ここから直接ファイル更新じゃなくて、ダウンロード->手動で上書き->pushの方が良いのかも？
            $this->disk->put($this->google_credential_json_path, json_encode($client->getAccessToken()));

            return redirect('/home')->with('success', 'WF概算修正業務スプレッドシートデータ取得用の認証情報が更新完了しました。');
        }
    }

    public function getSpreadsheetClient()
    {
        $client = new \Google_Client();
        $client->setAuthConfig($this->google_client_secret_json_path);
        $client->setApprovalPrompt('force');
        $client->setAccessType("offline");
        $client->setIncludeGrantedScopes(true);
        $client->addScope('https://www.googleapis.com/auth/spreadsheets');

        return $client;
    }
}

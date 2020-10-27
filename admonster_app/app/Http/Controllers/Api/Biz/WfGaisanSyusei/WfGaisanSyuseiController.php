<?php

namespace App\Http\Controllers\Api\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Step;
use App\Models\Task;
use App\Models\RequestLog;

class WfGaisanSyuseiController extends Controller
{
    protected $google_client_secret_json_path;

    protected $google_credential_json_path;

    protected $google_oauth_redirect_uri;

    protected $twitter_spreadsheet_id;

    protected $facebook_spreadsheet_id;

    protected $disk;

    // 作業ID(steps.idに対応)
    const STEP_ID_ASSORT_MAIL = 1;          // メール仕訳
    const STEP_ID_INPUT_MAIL_INFO = 2;      // メール情報入力
    const STEP_ID_INPUT_SAS_INFO = 3;       // SAS情報入力
    const STEP_ID_IDENTIFY_PPT = 4;         // パワポ特定
    const STEP_ID_INPUT_PPT_INFO = 5;       // パワポ情報入力
    const STEP_ID_FINAL_JUDGE = 6;         // 最終判定

    public static $step_ids = [
        'step1' => self::STEP_ID_ASSORT_MAIL,
        'step2' => self::STEP_ID_INPUT_MAIL_INFO,
        'step3' => self::STEP_ID_INPUT_SAS_INFO,
        'step4' => self::STEP_ID_IDENTIFY_PPT,
        'step5' => self::STEP_ID_INPUT_PPT_INFO,
        'step6' => self::STEP_ID_FINAL_JUDGE,
    ];

    /*
     * 判定タイプ
     */
    const JUDGE_TYPE_WF_APPLY            = 1;     // WF申請
    const JUDGE_TYPE_WF_APPLY_W_CHANGE   = 2;     // WF申請 x 案件概要変更あり
    const JUDGE_TYPE_FULL_CHARGE         = 3;     // 満額請求
    const JUDGE_TYPE_FULL_DIGESTION      = 4;     // 満額消化
    const JUDGE_TYPE_SAS_CORRECTED       = 5;     // SAS修正済
    const JUDGE_TYPE_RETURN_STEPS        = 0;     // 戻し
    const JUDGE_TYPE_HOLD                = 99;     // 保留

    public static $judge_types = [
        'wf_apply' => self::JUDGE_TYPE_WF_APPLY,
        'wf_apply_w_change' => self::JUDGE_TYPE_WF_APPLY_W_CHANGE,
        'full_charge' => self::JUDGE_TYPE_FULL_CHARGE,
        'full_digestion' => self::JUDGE_TYPE_FULL_DIGESTION,
        'sas_corrected' => self::JUDGE_TYPE_SAS_CORRECTED,
        'return_steps' => self::JUDGE_TYPE_RETURN_STEPS,
        'hold' => self::JUDGE_TYPE_HOLD,
    ];

    public static $judge_types_text_map = [
        self::JUDGE_TYPE_WF_APPLY => 'WF申請',
        self::JUDGE_TYPE_WF_APPLY_W_CHANGE => 'WF申請 x 案件概要変更あり',
        self::JUDGE_TYPE_FULL_CHARGE => '満額請求',
        self::JUDGE_TYPE_FULL_DIGESTION => '満額消化',
        self::JUDGE_TYPE_SAS_CORRECTED => 'SAS修正済み',
        self::JUDGE_TYPE_RETURN_STEPS => '戻し',
        self::JUDGE_TYPE_HOLD => '保留',
    ];

    /*
     * 処理タイプ
     */
    const PROCESS_TYPE_WF_APPLY            = 1;     // WF申請
    const PROCESS_TYPE_FULL_DIGESTION      = 2;     // 満額消化
    const PROCESS_TYPE_FULL_CHARGE         = 3;     // 満額請求
    const PROCESS_TYPE_CONTACT             = 4;     // 問い合わせ
    const PROCESS_TYPE_DONE                = 5;     // 処理済み
    const PROCESS_TYPE_SAS_CORRECTED       = 6;     // SAS修正済み
    const PROCESS_TYPE_WF_APPLY_W_CHANGE   = 7;     // WF申請 x 案件概要変更あり
    const PROCESS_TYPE_HOLD                = 0;     // 保留

    public static $process_types_text_map = [
        self::PROCESS_TYPE_WF_APPLY => 'WF申請',
        self::PROCESS_TYPE_FULL_DIGESTION => '満額消化',
        self::PROCESS_TYPE_FULL_CHARGE => '満額請求',
        self::PROCESS_TYPE_CONTACT => '問い合わせ',
        self::PROCESS_TYPE_DONE => '処理済み',
        self::PROCESS_TYPE_SAS_CORRECTED => 'SAS修正済み',
        self::PROCESS_TYPE_WF_APPLY_W_CHANGE => 'WF申請 x 案件概要変更あり',
        self::PROCESS_TYPE_HOLD => '保留',
    ];

    /*
     * 各作業画面内フォームの「はい」「いいえ」の選択肢のvalue
     */
    const ANSWER_FOR_QUESTION_YES = 1;
    const ANSWER_FOR_QUESTION_NO = 0;

    /*
     * マスタデータスプレッドシートの種別
     */
    const SPREADSHEET_TYPE_TWITTER = 'Twitter';
    const SPREADSHEET_TYPE_FACEBOOK = 'facebook';

    /*
     * マスターデータスプレッドシートのJOBNoの列
     */
    const TWITTER_SPREADSHEET_NAME  = '済案件';
    const FACEBOOK_SPREADSHEET_NAME = '済案件';

    /*
     * マスタデータスプレッドシートのJOBNoの列
     */
    const TWITTER_SPREADSHEET_JOBNO_ROW  = 'L';
    const FACEBOOK_SPREADSHEET_JOBNO_ROW = 'H';

    /*
     * マスタデータスプレッドシートの取得対象範囲データ
     */
    // Twitter
    const TWITTER_SPREADSHEET_START_LINE = 2;       // 取得データ範囲の最初の行番号
    const TWITTER_SPREADSHEET_FIRST_ROW = 'E';      // 取得データ範囲の最初の列
    const TWITTER_SPREADSHEET_LAST_ROW = 'AH';      // 取得データ範囲の最後の列
    // Facebook
    const FACEBOOK_SPREADSHEET_START_LINE = 2;      // 取得データ範囲の最初の行番号
    const FACEBOOK_SPREADSHEET_FIRST_ROW = 'A';     // 取得データ範囲の最初の列
    const FACEBOOK_SPREADSHEET_LAST_ROW = 'AG';     // 取得データ範囲の最後の列

    public function __construct()
    {
        $this->google_client_secret_json_path = storage_path('biz/wf_gaisan_syusei/google_client_secret.json');
        $this->google_credential_json_path = 'wf_gaisan_syusei/spreadsheet_credential.json';
        $this->google_oauth_redirect_uri = url('/') . '/biz/wf_gaisan_syusei/oauth2_callback';

        // 取得対象のスプレッドシートを環境別で読み分ける
        if (\Config::get('app.env') == 'production') {
            // 本番
            $this->twitter_spreadsheet_id = '1FOtjLcLjtL336KDAVCxkb1W4fnmA_NfcYKkbuTyM1JM';
            $this->facebook_spreadsheet_id = '1J6DHp4CPJI9Tc7bMHTzeuJfzuEdE843wJ5nO2ibDmuI';
        } elseif (\Config::get('app.env') == 'staging') {
            // ステージング
            $this->twitter_spreadsheet_id = '1jSPp0A3SdqXDl2bRU9bzP6DTIvyiAR_nf6TAKIclIVI';
            $this->facebook_spreadsheet_id = '1axtJO9AMh2V7fFJTmrkAfYkuFhDP4GNv_I8xT3FaWwg';
        } else {
            // その他
            // $this->spreadsheet_id = '1axtJO9AMh2V7fFJTmrkAfYkuFhDP4GNv_I8xT3FaWwg';
            $this->twitter_spreadsheet_id = '1jSPp0A3SdqXDl2bRU9bzP6DTIvyiAR_nf6TAKIclIVI';
            $this->facebook_spreadsheet_id = '1axtJO9AMh2V7fFJTmrkAfYkuFhDP4GNv_I8xT3FaWwg';
        }

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

    public function getMasterDataOnSpreadsheet($req, $task_id, $jobno)
    {
        $master_data = [];

        try {
            // クライアントオブジェクトを作成
            $client = $this->getSpreadsheetClient();

            // セッションにあればセッションからアクセストークンを取得
            if ($req->session()->has('access_token')) {
                $credential_json = $req->session()->get('access_token');
            } else {
            // セッションになければローカルに既存のcredential_jsonファイルを読みに行く
                if ($this->disk->exists($this->google_credential_json_path)) {
                    $credential_json = $this->disk->get($this->google_credential_json_path);
                } else {
                    return $master_data;
                }
            }

            $client->setAccessToken($credential_json);
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $this->disk->put($this->google_credential_json_path, json_encode($client->getAccessToken()));

            // アクセストークンの期限が切れている場合リフレッシュトークンで取得してファイル作成or更新
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $this->disk->put($this->google_credential_json_path, json_encode($client->getAccessToken()));
            }

            // セッションにアクセストークンをセット
            session(['access_token' => $client->getAccessToken()]);

            $service = new \Google_Service_Sheets($client);

            // TwitterのスプレッドシートからJOBNoの列を取得して対象JOBNo.の行を取得
            $range = self::TWITTER_SPREADSHEET_NAME . '!' . self::TWITTER_SPREADSHEET_JOBNO_ROW . self::TWITTER_SPREADSHEET_START_LINE . ':' . self::TWITTER_SPREADSHEET_JOBNO_ROW;
            $values = $service->spreadsheets_values->get($this->twitter_spreadsheet_id, $range)->getValues();
            $target_line_num = $this->getTargetLineNum($jobno, $values, self::SPREADSHEET_TYPE_TWITTER);

            // 取得した行番号から行データを取得
            $target_line_data = [];
            if ($target_line_num !== '') {
                $target_line_data = $this->getTargetLineData($service, $target_line_num, self::SPREADSHEET_TYPE_TWITTER);
            }
            // データが見つかればマスタデータにマッピング
            if (!empty($target_line_data)) {
                $master_data = $this->setAsMasterData($target_line_data, self::SPREADSHEET_TYPE_TWITTER);
            } else {
                // TwitterスプレッドシートになければFacebookスプレッドシート内を検索
                $range = self::FACEBOOK_SPREADSHEET_NAME . '!' . self::FACEBOOK_SPREADSHEET_JOBNO_ROW . self::FACEBOOK_SPREADSHEET_START_LINE . ':' . self::FACEBOOK_SPREADSHEET_JOBNO_ROW;
                $values = $service->spreadsheets_values->get($this->facebook_spreadsheet_id, $range)->getValues();
                $target_line_num = $this->getTargetLineNum($jobno, $values, self::SPREADSHEET_TYPE_FACEBOOK);

                if ($target_line_num !== '') {
                    $target_line_data = $this->getTargetLineData($service, $target_line_num, self::SPREADSHEET_TYPE_FACEBOOK);
                    if (!empty($target_line_data)) {
                        $master_data = $this->setAsMasterData($target_line_data, self::SPREADSHEET_TYPE_FACEBOOK);
                    }
                }
            }
        } catch (\Exception $e) {
            report($e);
            return $master_data;
        }

        return $master_data;
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

    /*
     * 対象のJOBNo.の行番号を返す
     */
    public function getTargetLineNum($code, $values, $sheet_type)
    {
        $target_line_num = '';

        // 不正利用を防ぐため、JOBNO(J+数字10桁)が正しく受け取れない場合は空文字を返却
        if (strlen($code) !== 11 or substr($code, 0, 1) !== 'J') {
            return $target_line_num;
        }

        foreach ($values as $key => $value) {
            // 空の場合はループを抜ける
            if (!empty($value)) {
                if (preg_match('/^'.$code.'/', $value[0])) {
                    switch ($sheet_type) {
                        case self::SPREADSHEET_TYPE_TWITTER:
                            $target_line_num = $key + self::TWITTER_SPREADSHEET_START_LINE;
                            // no break
                        case self::SPREADSHEET_TYPE_FACEBOOK:
                            $target_line_num = $key + self::FACEBOOK_SPREADSHEET_START_LINE;
                            // no break
                    }
                }
            }
        }

        return $target_line_num;
    }

    /*
     * 指定行のデータを返す
     */
    public function getTargetLineData($service, $target_line_num, $sheet_type)
    {
        $target_line_data = [];
        switch ($sheet_type) {
            case self::SPREADSHEET_TYPE_TWITTER:
                $range = self::TWITTER_SPREADSHEET_NAME . '!' . self::TWITTER_SPREADSHEET_FIRST_ROW . $target_line_num . ':' . self::TWITTER_SPREADSHEET_LAST_ROW . $target_line_num;
                $target_line_data = $service->spreadsheets_values->get($this->twitter_spreadsheet_id, $range)->getValues();
                break;
            case self::SPREADSHEET_TYPE_FACEBOOK:
                $range = self::FACEBOOK_SPREADSHEET_NAME . '!' . self::FACEBOOK_SPREADSHEET_FIRST_ROW . $target_line_num . ':' . self::FACEBOOK_SPREADSHEET_LAST_ROW . $target_line_num;
                $target_line_data = $service->spreadsheets_values->get($this->facebook_spreadsheet_id, $range)->getValues();
                break;
        }

        return $target_line_data;
    }

    /*
     * マスターデータとして使うようにマッピング
     */
    public function setAsMasterData($target_line_data, $sheet_type)
    {
        $master_data = [];
        $target_line_data = $target_line_data[0];

        /*
         * 行の取得対象セルが空で以降の列が全て空の場合、対象セル自体が取得されないので念のため変数の存在チェックを入れています
         */
        switch ($sheet_type) {
            case self::SPREADSHEET_TYPE_TWITTER:
                $site_and_menu = isset($target_line_data[3]) ? $target_line_data[3] : '';
                $master_data = [
                    'sheet_type' => self::SPREADSHEET_TYPE_TWITTER,
                    'jobno' => isset($target_line_data[7]) ? $target_line_data[7] : '',                 // JOBNo.
                    'sales_staff' => isset($target_line_data[6]) ? $target_line_data[6] : '',           // 営業担当
                    'agency' => isset($target_line_data[0]) ? $target_line_data[0] : '',                // 代理店
                    'advertiser' => isset($target_line_data[1]) ? $target_line_data[1] : '',            // 広告主
                    'campaign' => isset($target_line_data[2]) ? $target_line_data[2] : '',              // キャンペーン名
                    'site' => mb_substr($site_and_menu, 0, mb_strpos($site_and_menu, "/")),             // サイト
                    'menu' => mb_substr($site_and_menu, mb_strpos($site_and_menu, "/") + 1),            // メニュー
                    'from' => isset($target_line_data[4]) ? $target_line_data[4] : '',                  // 掲載開始日
                    'to' => isset($target_line_data[5]) ? $target_line_data[5] : '',                    // 掲載終了日
                    'order_amount_gross' => isset($target_line_data[8]) ? money_format_ja($target_line_data[8]) : '',    // 申込金額(変更前グロス)
                    'order_amount_net' => isset($target_line_data[10]) ? money_format_ja($target_line_data[10]) : '',    // 申込金額(変更前ネット)
                    'commit_amount_gross' => isset($target_line_data[24]) ? money_format_ja($target_line_data[24]) : '', // 確定金額(グロス)
                    'commit_amount_net' => isset($target_line_data[25]) ? money_format_ja($target_line_data[25]) : '',   // 確定金額(ネット)
                    'order_line_num' => isset($target_line_data[28]) ? $target_line_data[28] : '',      // 受注明細番号
                ];
                break;
            case self::SPREADSHEET_TYPE_FACEBOOK:
                $site_and_menu = isset($target_line_data[2]) ? $target_line_data[2] : '';
                $master_data = [
                    'sheet_type' => self::SPREADSHEET_TYPE_FACEBOOK,
                    'jobno' => isset($target_line_data[7]) ? $target_line_data[7] : '',
                    'sales_staff' => '',
                    'agency' => isset($target_line_data[0]) ? $target_line_data[0] : '',
                    'advertiser' => isset($target_line_data[1]) ? $target_line_data[1] : '',
                    'campaign' => isset($target_line_data[6]) ? $target_line_data[6] : '',
                    'site' => mb_substr($site_and_menu, 0, mb_strpos($site_and_menu, "/")),
                    'menu' => mb_substr($site_and_menu, mb_strpos($site_and_menu, "/") + 1),
                    'from' => isset($target_line_data[3]) ? $target_line_data[3] : '',
                    'to' => isset($target_line_data[4]) ? $target_line_data[4] : '',
                    'order_amount_gross' => isset($target_line_data[5]) ? money_format_ja($target_line_data[5]) : '',
                    'order_amount_net' => isset($target_line_data[9]) ? money_format_ja($target_line_data[9]) : '',
                    'commit_amount_gross' => isset($target_line_data[26]) ? money_format_ja($target_line_data[26]) : '',
                    'commit_amount_net' => isset($target_line_data[27]) ? money_format_ja($target_line_data[27]) : '',
                    'order_line_num' => isset($target_line_data[31]) ? $target_line_data[31] : '',
                ];
                break;
        }

        return $master_data;
    }

    public function extractValueBykey($array, $key)
    {
        $result = array();
        for ($i=0; $i<count($array); $i++) {
            array_push($result, $array[$i]->$key);
        }
        return $result;
    }

    public function makeUnclearContactMailSubject($step_id, $unclear_points, $request_mail_subject, $path_to_step_key)
    {
        $step_name = Step::where('id', $step_id)->first()->name;
        $unclear_point_labels = [];
        foreach ($unclear_points as $key => $value) {
            if ($value == self::ANSWER_FOR_QUESTION_YES) {
                $unclear_comment_key = 'item'.sprintf('%02d', intval(str_replace('item', '', $key)) + 1);
                $unclear_point_labels[] = __($path_to_step_key.'.'.$key.'.label');
            }
        }

        return '【'.$step_name.'＿'.implode('／', $unclear_point_labels).'】 '.$request_mail_subject;
    }

    public function escapeYenMark($str)
    {

        if (!$str) {
            return $str;
        }

        if (strpos($str, '¥') === false) {
            // 念のため￥マークが存在しないパターンを記載。
            $str = '¥¥'.$str;
        } else {
            $str = '¥'.$str;
        }
        return $str;
    }

    // メールの件名を生成
    public function generateSendMailSubject($data)
    {
        $subject = '【'.$data['business_name'].'＿'.$data['step_name'].'＿'.$data['mail_type'].'】'.$data['request_name'];

        return $subject;
    }

    // タスク処理のログ登録
    public function storeRequestLog($task_result_type, $request_id, $request_work_id, $task_id, $user_id)
    {
        switch ($task_result_type) {
            // 通常処理
            case \Config::get('const.TASK_RESULT_TYPE.DONE'):
                $request_log_type = \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY');
                break;
            // 対応不要処理
            case \Config::get('const.TASK_RESULT_TYPE.CANCEL'):
                $request_log_type = \Config::get('const.REQUEST_LOG_TYPE.REQUEST_EXCEPTED');
                break;
            // 不明処理
            case \Config::get('const.TASK_RESULT_TYPE.CONTACT'):
                $request_log_type = \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT');
                break;
            // 戻し処理
            case \Config::get('const.TASK_RESULT_TYPE.RETURN'):
                $request_log_type = \Config::get('const.REQUEST_LOG_TYPE.STEPS_RETURNED');
                break;
            //他の一切状況
            default:
                $request_log_type = '';
        }
        
        $request_log = new RequestLog;
        $request_log->request_id = $request_id;
        $request_log->type = $request_log_type;
        $request_log->request_work_id = $request_work_id;
        $request_log->task_id = $task_id;
        $request_log->created_user_id = $user_id;
        $request_log->updated_user_id = $user_id;
        $request_log->save();
    }
}

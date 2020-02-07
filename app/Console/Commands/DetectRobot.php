<?php

namespace App\Console\Commands;

use App\Libraries\classes\JdUnion\FormatData;
use App\Libraries\classes\JdUnion\JdInterface;
use App\Logic\V1\Admin\Robot\MessageLogic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectRobot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'detectRobot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 发送微信消息
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        //
        try {
            $wxId = "wxid_jn6rqr7sx35322";
            $lists = (new MessageLogic())->syncMessage($wxId);
            foreach ($lists as $list){
                // 判断是否获取商品信息
                if (!empty($list["Content"]["String"])){
                    if (!empty($list["PushContent"]) && strpos($list["Content"]["String"],'查找') !== false) {
                        $keyWord = mb_substr(strstr($list["Content"]["String"], '查找'), 3);
                        $param["methodType"] = 'inquire';
                        $param["param_json"]["goodsReqDTO"] = ['pageIndex' => $param["page"] ?? 1, 'pageSize' => $param["limit"] ?? 5, 'keyword' => $keyWord];
                        $data = JdInterface::getInstance($param)->setRequestParam()->execute();
                        $lists = FormatData::getInit()->headleOptional($data["data"]);
                        foreach ($lists as $k => $v){
                            //  发送微信文本消息
                            (new MessageLogic())->sendTxtMessage([
                                "toWxIds" => ["22514679284@chatroom"],
                                "content" => '『京东』' . $v["goods_name"] . "\n【原价】￥{$v["goods_price"]} \n【限时抢券后价】￥{$v["coupon_price"]}\n------------------\n【购买链接】{$v["material_url"]}\n【购买方式】点击链接即可下单购买",
                                "wxId" => "wxid_jn6rqr7sx35322"
                            ]);
                            // 发送微信图片消息
                            (new MessageLogic())->sendImageMessage([
                                "toWxIds" => ["22514679284@chatroom"],
                                "imgUrl" => $v["goods_thumb"],
                                "wxId" => "wxid_jn6rqr7sx35322"
                            ]);
                        }
                    }
                }
            }
        } catch(\Throwable $e) {
            Log::info('Fail to call api');
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Events\InPacketEvent;
use App\Events\OutPacketEvent;
use App\Http\Resources\InPacketResource;
use App\Http\Resources\OutPacketResource;
use App\Models\InPacket;
use App\Models\OutPacket;
use App\Models\TransactionInfo;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * 参数值
     * token
     * userid 用户id
     * issus_sum 金额
     * tail_number 尾号
     * count 个数
     * eosid 区块链id
     * blocknumber 块号
     *
     * addr 平台
     *
     * 发红包接口
     * @param Request $request
     * @return $this
     */
    public function issus_packet(Request $request)
    {
        $issus_sum_arr = [
            0 => -1,
            1 => 0,
            5 => 1,
            10 => 2,
            20 => 3,
            50 => 4,
            100 => 5
        ];
        $entity = OutPacket::create($request->all());

        $userid = $request->input('userid');
        $issus_sum = $request->input('issus_sum', 0);
        $addr = $request->input('addr', 'pc');
        $transactionInfo = new TransactionInfo();
        $transactionInfo->issus_userid = $userid;
        $transactionInfo->income_userid = 0;
        $transactionInfo->type = 2;
        $transactionInfo->status = 1;
        $transactionInfo->eos = $issus_sum;
        $transactionInfo->addr = $addr;
        $transactionInfo->save();
        $username = User::find($userid)->name;
        event(new OutPacketEvent($entity, $issus_sum_arr[$issus_sum], $username));
        Log::info('');
        return $this->success([
            'code' => 200,
            'token' => $request->input('token'),
            'userid' => $request->input('userid')
        ], '发送成功');
    }

    /**
     * outid 发出的红包id
     * userid 用户id
     * eosid 区块链id
     * blocknumber 区块链号
     * income_sum 抢中金额
     * is_chailei 是否踩雷
     * is_reward 是否中奖
     * reward_type 中奖类型
     * reward_sum 中奖金额
     * isnone 是否是最后一个
     * addr 平台
     * 抢红包记录接口
     * @param Request $request
     * @return $this
     */

    public function income_packet(Request $request)
    {
        $outeosid = $request->input('outid');
        $outid = OutPacket::where('eosid', $outeosid)->first()->id;

        $userid = $request->input('userid');
        $is_chailei = $request->input('is_chailei');
        $is_reward = $request->input('is_reward');
        $reward_sum = $request->input('reward_sum');
        $eos = $request->input('income_sum');
        $addr = $request->input('addr', 'pc');
        $isnone = $request->input('isnone');
        $InpacketData = [
            'outid' => $outid,
            'userid' => $userid,
            'eosid' => $request->input('eosid'),
            'blocknumber' => $request->input('blocknumber'),
            'income_sum' => $request->input('income_sum'),
            'is_chailei' => $request->input('is_chailei') == 1 ? 1 : 2,
            'is_reward' => $request->input('reward_type') == 0 ? 1 : 2,
            'reward_type' => $request->input('reward_type'),
            'reward_sum' => $request->input('reward_type') == 0 ? 0 : $request->input('reward_sum'),
            'addr' => $request->input('addr'),
        ];
        $entity = InPacket::create($InpacketData);


        $income_userid = OutPacket::find($outid)->userid;
        // 抢红包信息
        $data = [
            'issus_userid' => $userid,
            'income_userid' => $income_userid,
            'type' => 1,
            'status' => 1,
            'eos' => $eos,
            'addr' => $addr,
        ];
        TransactionInfo::create($data);

        // 踩雷信息
        if ($is_chailei == 1) {
            $data['issus_userid'] = $income_userid;
            $data['income_userid'] = $userid;
            $data['type'] = 3;
            $data['eos'] = OutPacket::find($outid)->issus_sum;
            TransactionInfo::create($data);
        }

        // 中奖信息
        if ($is_reward !== 0) {
            $data['issus_userid'] = 0;
            $data['income_userid'] = $income_userid;
            $data['type'] = 4;
            $data['eos'] = $reward_sum;
            TransactionInfo::create($data);
        }
        if ($isnone == 1) {
            // 红包被抢完后生成发红包对用的抢红包的列表
            $out_in_packet = InPacket::where('outid', $outid)->get();
            $outPacket_entity = OutPacket::find($outid);
            $outPacket_entity->status = 2;
            $outPacket_entity->save();
            $outPacket = $outPacket_entity;
            $out_in_packet_data = array();
            $reward_data__ = array();
            $chailei_data__ = array();
            foreach ($out_in_packet as $item => $value) {
                $out_in_packet_data[$item]['name'] = User::find($value['userid'])->name;
                $out_in_packet_data[$item]['income_sum'] = $value['income_sum'];
                if ($value['is_chailei'] == 1) {
                    $chailei_data__[$item]['name'] = User::find($value['userid'])->name;
                    $chailei_data__[$item]['chailai_sum'] = $outPacket->issus_sum;
                }
                if ($value['is_reward'] == 2) {
                    $reward_data__[$item]['name'] = User::find($value['userid'])->name;
                    $reward_data__[$item]['reward_type'] = $value['reward_type'];
                    $reward_data__[$item]['reward_sum'] = $value['reward_sum'];
                }

            }

            $reward_data = array_values($reward_data__);
            $chailei_data = array_values($chailei_data__);

            $name = User::find($outPacket->userid)->name;
            $issus_sum_arr = [
                0 => -1,
                1 => 0,
                5 => 1,
                10 => 2,
                20 => 3,
                50 => 4,
                100 => 5
            ];
            $index = $issus_sum_arr[intval($outPacket->issus_sum)];
            event(new InPacketEvent(
                $reward_data,
                $outPacket,
                $chailei_data,
                $out_in_packet_data,
                $name,
                2,
                $index
            ));
        }

        return $this->success([
            'code' => 200,
            'token' => $request->input('token'),
            'userid' => $request->input('userid')
        ], '发送成功');
    }

    /**
     * 参数值
     * token
     * 用户id userid
     * 当前用户发红包的情况
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function my_issus_packet(Request $request)
    {
        $page = $request->input('page', 1);
        $userid = $request->input('userid');
        $outpacketsum = OutPacket::where('userid', $userid)->sum('issus_sum');
        $outpacket = OutPacket::where('userid', $userid)->count();
        $chaileicount = TransactionInfo::where('income_userid', $userid)->where('type', 3)->count();
        $query = OutPacket::where('userid', $userid);
        if ($request->filled('time')) {
            $begin_time = date('Y-m-d 0:0:0', $request->input('time'));
            $end_time = date('Y-m-d 59:59:59', $request->input('time'));
            $query->where('created_at', '>', $begin_time)->where('created_at', '<', $end_time);
        }
        return OutPacketResource::collection(
            $query->where('status', '<>', 1)->orderBy('created_at', 'desc')->paginate()
        )->additional([
            'code' => 200,
            'outpacketcount' => $outpacket,
            'chaileicount' => $chaileicount,
            'outpacketsum' => $outpacketsum,
            'name' => User::find($userid)->name,
            'last_time' => strtotime(OutPacket::where('userid', $userid)->min('created_at')),
            'max_time' => strtotime(OutPacket::where('userid', $userid)->max('created_at')),
            'message' => ''
        ]);
    }

    /**
     * 参数值
     * token
     * 用户id userid
     * 当前用户抢红包的情况
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function my_income_packet(Request $request)
    {
        $userid = $request->input('userid');


        $pairs = InPacket::where('userid', $userid)->where('reward_type', 1)->count();
        $three = InPacket::where('userid', $userid)->where('reward_type', 2)->count();

        $int = InPacket::where('userid', $userid)->where('reward_type', 3)->count();
        $shunzi = InPacket::where('userid', $userid)->where('reward_type', 4)->count();
        $bomb = InPacket::where('userid', $userid)->where('reward_type', 5)->count();

        $chailei = InPacket::where('userid', $userid)->where('is_chailei', 1)->count();


        $query = InPacket::query()->with(['out'])->whereHas('out', function ($q) {
            $q->where('status', '=', 2);
        })->where('userid', $userid);
        if ($request->filled('time')) {
            $begin_time = date('Y-m-d 0:0:0', $request->input('time'));
            $end_time = date('Y-m-d 59:59:59', $request->input('time'));
            $query->where('created_at', '>', $begin_time)->where('created_at', '<', $end_time);
        }
        return InPacketResource::collection(
            $query->orderBy('created_at', 'desc')->paginate()
        )->additional([
            'code' => 200,
            'paris' => $pairs,
            'three' => $three,
            'int' => $int,
            'shunzi' => $shunzi,
            'bomb' => $bomb,
            'chailei' => $chailei,
            'name' => User::find($userid)->name,
            'packetcount' => InPacket::where('userid', $userid)->count(),
            'packetsum' => InPacket::where('userid', $userid)->sum('income_sum'),
            'last_time' => strtotime(InPacket::where('userid', $userid)->min('created_at')),
            'max_time' => strtotime(InPacket::where('userid', $userid)->max('created_at')),
            'message' => ''
        ]);
    }

    /**
     * 参数值
     * token
     * 发出红包id  outid
     * 发出红包领取的情况
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    public function red_packet(Request $request)
    {
        $eosid = $request->input('outid');
        $outpacketentity = OutPacket::where('eosid', $eosid)->first();
        if (empty($outpacketentity)) {
            return response()->json([
                'data' => [],
                'code' => 2001,
                'message' => '参数错误'
            ]);
        }

        $outid = $outpacketentity->id;
        $outuserid = $outpacketentity->userid;
        if ($outpacketentity->status == 1) {
            return response()->json([
                'data' => [],
                'outpacketname' => User::find($outuserid)->name,
                'outpacketsum' => $outpacketentity->issus_sum,
                'outpackettailnumber' => $outpacketentity->tail_number,
                'code' => 2002,
                'message' => '红包未领完'
            ]);
        } else {
            return InPacketResource::collection(
                InPacket::where('outid', $outid)->orderBy('created_at', 'desc')->get()
            )->additional([
                'outpacketname' => User::find($outuserid)->name,
                'outpacketsum' => $outpacketentity->issus_sum,
                'outpackettailnumber' => $outpacketentity->tail_number,
                'code' => 200,
                'message' => ''
            ]);
        }
    }
    public function postRewardMoney(Request $request)
    {
        $userid = $request->input('userid');
        $money = $request->input('money');
        $data = [
            'issus_userid'=>0,
            'income_userid'=>$userid,
            'type'=>5,
            'status'=>1,
            'eos'=>$money,
            'addr'=>User::find($userid)->name,
        ];
        TransactionInfo::create($data);
        $this->success([],'发送成功');
    }
}

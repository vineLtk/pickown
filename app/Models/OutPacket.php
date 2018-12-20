<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutPacket extends Model
{
    protected $fillable = [
        'id',
        'userid',
        'issus_sum',
        'count',
        'eosid',
        'blocknumber',
        'tail_number',
        'addr',
        'status',
        'created_at',
        'updated_at',
        'surplus_sum',
        'is_guangbo'
    ];
    public $statusArr = [1 => '未抢完', 2 => '已抢完',3=>'退回','4'=>'后台关闭'];
    public $indexArr = [
        '0.1000' => 0,
        '1.0000' => 1,
        '5.0000' => 2,
        '10.0000' => 3,
        '20.0000' => 4,
        '50.0000' => 5,
        '100.0000' => 6
    ];
    public static $iidexArr = [
        0 => -1,
        '0.1000' => 0,
        '1.0000' => 1,
        '5.0000' => 2,
        '10.0000' => 3,
        '20.0000' => 4,
        '50.0000' => 5,
        '100.0000' => 6
    ];
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'userid');
    }

    public function inpacket()
    {
        return $this->hasMany(InPacket::class, 'outid', 'id');
    }

}

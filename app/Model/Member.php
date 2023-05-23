<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;
use Qbhy\HyperfAuth\Authenticatable;

/**
 * @property int $id 
 * @property string $name 
 * @property string $account 
 * @property string $password 
 * @property int $sex 
 * @property int $age 
 * @property string $avatar 
 * @property string $email 
 * @property string $phone 
 * @property int $status 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $uuid 
 * @property int $member_level_status 
 * @property string $device 
 * @property string $register_ip 
 * @property string $last_ip 
 * @property string $coins 
 * @property string $diamond_coins 
 * @property int $diamond_quota 
 * @property int $vip_quota 
 * @property int $free_quota 
 * @property int $free_quota_limit 
 * @property string $aff 
 * @property int $invited_by 
 * @property int $invited_num 
 * @property string $tui_coins 
 * @property string $total_tui_coins 
 * @property string $aff_url 
 * @property-read mixed $is_selected_tag 
 */
class Member extends Model implements Authenticatable
{
    public const STATUS = ['VISITORS' => 0, 'NOT_VERIFIED' => 1, 'VERIFIED' => 2, 'DISABLE' => 3, 'DELETE' => 4];

    public const SEX = ['DEFAULT' => 0, 'MALE' => 1, 'FEMALE' => 2];

    public const PAGE_PER = 10;

    public const VISITOR_NAME = '遊客';
    //VIP次數 
    public const VIP_QUOTA = ['DAY'=>50,'UP_TWO'=>NULL];

    /**
     * The table associated with the model.
     * @var string
     */
    protected ?string $table = 'members';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['name', 'sex', 'age', 'password', 'role_id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'sex' => 'integer', 'age' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'member_level_status' => 'integer', 'diamond_quota' => 'integer', 'vip_quota' => 'integer', 'free_quota' => 'integer', 'free_quota_limit' => 'integer', 'invited_by' => 'integer', 'invited_num' => 'integer'];

    protected array $hidden = ['password'];

    // protected array $appends = ['is_selected_tag', 'is_vip_experience', 'is_diamond_experience'];
    protected array $appends = ['is_selected_tag'];

    public function getJwtIdentifier()
    {
        return $this->getKey();
    }

    public function getId()
    {
        // 返回用户id
        return $this->id;
    }

    public static function retrieveById($key): ?Authenticatable
    {
        // 通过id查找用户
        return Member::find($key);
    }

    /**
     * JWT自定义载荷.
     */
    public function getJwtCustomClaims(): array
    {
        return ['guard' => 'api'];
    }

    // 確認是否為第一次登入app
    protected function getIsSelectedTagAttribute()
    {
        $query = MemberTag::where('member_id', $this->id)->count();
        if (empty($query)) {
            return 0;
        }
        return 1;
    }

    // 確認是否為VIP體驗會員
    // protected function getIsVipExperienceAttribute()
    // {
    //     $date = BuyMemberLevel::select(Db::raw('DATEDIFF(end_time, start_time) as date'))->where('member_id', $this->id)->where('member_level_type', 'vip')->whereNull('deleted_at')->first();
    //     if (empty($date)) {
    //         return 0;
    //     }
    //     return 1;
    // }

    // 確認是否為鑽石體驗會員
    // protected function getIsDiamondExperienceAttribute()
    // {
    //     $date = BuyMemberLevel::select(Db::raw('DATEDIFF(end_time, start_time) as date'))->where('member_id', $this->id)->where('member_level_type', 'diamond')->whereNull('deleted_at')->first();
    //     if (empty($date)) {
    //         return 0;
    //     }
    //     return 1;
    // }
}

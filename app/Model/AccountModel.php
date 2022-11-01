<?php 
declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property $id
 * @property $amount
 */

class AccountModel extends Model
{
    public ?String $table = 'account';
    /**
     * @var string
     */
    public String $keyType = "integer";

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'amount', 'created_at', 'updated_at'];


}
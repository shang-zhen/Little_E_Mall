<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    //
    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['roleId','adminName', 'gender', 'email', 'address', 'adminPwd', 'tel', 'status', 'createdAt','updatedAt'];
    public function getCreatedAtColumn()
    {
        return 'createdAt';
    }
    public function getUpdatedAtColumn()
    {
        return 'updatedAt';
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }
}

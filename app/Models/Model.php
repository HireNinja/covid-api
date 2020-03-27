<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as ElModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends ELModel 
{
    use SoftDeletes;
    protected $casts = [
        'uuid' => 'string'
    ];
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $primaryKey = "uuid";
    protected $keyType = 'string';
    protected $dates = ['updated_at','deleted_at','created_at'];
    protected $guarded = ['uuid','updated_at','deleted_at','created_at'];
    
    public function applySearchQuery($query, $value) {
        return $query;
    }

}

<?php
namespace App\Models;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Request;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = "users";
    protected $guarded =['access_token','access_token_expired_at','user_agent','ip_address','ref', 'is_admin', 
                            'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['access_token','access_token_expired_at','user_agent','password','ip_address','ref'];

    public static function Register($data) {
        $user = User::create($data);
        return $user;
    }

    public static function Login($email, $password) {
        $user = User::whereEmail($email)->first();
        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }
        return false;
    }

    public function setPasswordAttribute($value) {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setAccessToken() {
        $this->access_token_expired_at = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " + 3 months"));;
        $this->access_token =  md5($this->email . $this->access_token_expired_at . rand(10000, 99999));
    }

    public function setResetToken() {
        $this->reset_token = rand(100000, 999999);
        $this->save();
    }

    public function generateUniqueUsername() {
        $name = explode("@", $this->email)[0];
        while (true) {
            $tempUsername = $name . rand(0,9) . rand(0,9);
            if (!User::whereUsername($tempUsername)->exists()) {
                return $tempUsername;
            }
        }
    }

    public static function verifyEmail($email) {
        $user = User::whereEmail($email)->first();
        if ($user) {
            $user->setResetToken();
            return $user;
        }
        return false;
    }

    public static function emailAlreadyExists($email) {
        if (User::whereEmail($email)->first()) {
            return true;
        }
        return false;
    }

    public static function verifyResetToken($data) {
        $user = User::find($data['user_uuid']);
        if ($user && ($data['reset_token'] == $user->reset_token)) {
            $user->password = $data['password'];
            $user->setAccessToken();
            $user->reset_token = null;
            $user->save();
            return $user;
        }
        return false;
    }

    public static function verifyOldPassword($data) {
        $user = User::whereUuid($data['user_uuid'])->first();
        if ($user && Hash::check($data['old_password'], $user->password)) {
            $user->password = $data['new_password'];
            $user->setAccessToken();
            $user->save();
            return $user;
        }
        return false;
    }
  
    public function isSuperAdmin() {
        return $this->is_admin;
    }    
}

User::creating(function($user){
    $user->setAccessToken(); 
    if (!$user->user_agent){
        $user->user_agent = Request::header('User-Agent');
    }
    if (!$user->ip_address) {
        $user->ip_address = Request::header("host");
    }
});

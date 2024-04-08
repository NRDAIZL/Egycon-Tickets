<?php

namespace App\Models;

use App\Helpers\QRHelper;
use App\Helpers\StringUtils;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Laravolt\Avatar\Avatar;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,  \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $auditExclude = [
        'password',
        'remember_token'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function generateAvatar()
    {
        $avatar = new Avatar();
        $avatar_image_url =
        $avatar->create($this->email)->toGravatar(['d' => 'identicon', 'r' => 'pg', 's' => 100]);
        file_get_contents($avatar_image_url);
        $path = 'avatars/'.$this->id.'.png';
        Storage::disk('public')->put($path, file_get_contents($avatar_image_url));                
        UserAvatar::create(['user_id' => $this->id, 'image' => $path]);
        return $this;
    }

    public function getAvatar(){
        $avatar = UserAvatar::where('user_id', $this->id);
        if($avatar->exists()){
            return $avatar->get()->last()->getImageAttribute();
        }else if(env("GENERATE_AVATARS_AUTOMATICALLY", true)){
            // generate
            return $this->generateAvatar()->getAvatar();
        }else{
            return asset("images/". env("DEFAULT_AVATAR_PATH", "default_avatar.png"));
        }
    }

    public function telegram_chat(){
        return $this->hasOne(TelegramChat::class);
    }

    public function getTelegramChatId() {
        return $this->telegram_chat()->exists() ? $this->telegram_chat->chat_id : null;
    }

    public function getTelegramChatIdObject()
    {
        return $this->telegram_chat()->exists() ? $this->telegram_chat : null;
    }

    public function saveTelegramChatId($chat_id){
        if($this->telegram_chat()->exists()){
            $this->telegram_chat()->update(['chat_id' => $chat_id]);
        }else{
            $this->telegram_chat()->create(['chat_id' => $chat_id]);
        }
        $this->getTelegramCode(true);
    }


    public function getTelegramCode(bool $newCode = false): string
    {
        if ($this->telegram_code == null || $this->telegram_code == '' || $newCode) {
            $this->telegram_code = StringUtils::generateRandomString(16);
            $this->save();
        }
        return $this->telegram_code;
    }

    public function getTelegramCodeQR(){
        $telegramBotUsername = env('TELEGRAM_BOT_USERNAME');
        $url = "https://t.me/{$telegramBotUsername}?start={$this->getTelegramCode()}";
        return (new QRHelper)->generate($url, false, true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PersonModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // اسم الجدول في قاعدة البيانات
    protected $table = 'personnes';

    // تحديد المفتاح الأساسي (لأن CIN هو المفتاح الأساسي وليس 'id')
    protected $primaryKey = 'cin';
    // المفتاح الأساسي ليس تلقائي الزيادة
    public $incrementing = false;
    // نوع المفتاح الأساسي هو string
    protected $keyType = 'string';

    /**
     * الأعمدة التي يمكن تعبئتها جماعياً.
     */
    protected $fillable = [
        'cin',
        'prenom',
        'nom',
        'email',
        'spare_email',
        'telephone',
        'mot_de_passe',
        'est_administrateur',
    ];

    /**
     * الأعمدة التي يجب إخفاؤها (مثل كلمة المرور).
     */
    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    /**
     * Permet à Laravel d'utiliser la colonne personnalisée du mot de passe.
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    /**
     * علاقة One-to-One: يمكن للشخص أن يكون طالباً.
     * يستخدم المفتاح الأساسي لهذا النموذج (cin) للبحث في نموذج Etudiant.
     */
    public function etudiant()
    {
        // يربط CIN بـ cin_personne في جدول etudiants
        return $this->hasOne(StudentModel::class, 'cin_personne', 'cin');
    }
}
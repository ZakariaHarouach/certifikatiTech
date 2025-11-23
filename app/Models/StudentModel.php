<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentModel extends Model
{
    use HasFactory;

    // اسم الجدول في قاعدة البيانات
    protected $table = 'etudiants';

    // تحديد المفتاح الأساسي (cin_personne هو المفتاح الأساسي هنا)
    protected $primaryKey = 'cin_personne';
    // المفتاح الأساسي ليس تلقائي الزيادة
    public $incrementing = false;
    // نوع المفتاح الأساسي هو string
    protected $keyType = 'string';

    /**
     * الأعمدة التي يمكن تعبئتها جماعياً.
     */
    protected $fillable = [
        'cin_personne',
        'groupe',
        'niveau_etudiant',
        'specialite',
    ];

    /**
     * علاقة One-to-One (Inverse): الطالب ينتمي إلى شخص واحد.
     * المفتاح الأجنبي هنا هو المفتاح الأساسي للنموذج (cin_personne).
     */
    public function personne()
    {
        // يربط cin_personne بـ cin في جدول personnes
        return $this->belongsTo(PersonModel::class, 'cin_personne', 'cin');
    }

    /**
     * علاقة One-to-Many: يمكن للطالب أن يكون لديه عدة شهادات طبية.
     */
    public function certificatsMedicaux()
    {
        // يربط cin_personne بـ cin_etudiant في جدول certificats_medicaux
        return $this->hasMany(CertificateModel::class, 'cin_etudiant', 'cin_personne');
    }
}
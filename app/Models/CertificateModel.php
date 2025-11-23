<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateModel extends Model
{
    use HasFactory;

    // اسم الجدول في قاعدة البيانات
    protected $table = 'certificats_medicaux';

    // تحديد المفتاح الأساسي
    protected $primaryKey = 'id_certificat_medical';

    /**
     * الأعمدة التي يمكن تعبئتها جماعياً.
     */
    protected $fillable = [
        'cin_etudiant',
        'image_certificat',
        'date_emission',
        'statut_certificat',
    ];

    /**
     * علاقة Belongs-To (كثير لواحد): الشهادة تنتمي إلى طالب واحد.
     */
    public function etudiant()
    {
        // يربط cin_etudiant (المفتاح الأجنبي المحلي) بـ cin_personne (المفتاح الأساسي للجدول etudiants)
        return $this->belongsTo(StudentModel::class, 'cin_etudiant', 'cin_personne');
    }
}
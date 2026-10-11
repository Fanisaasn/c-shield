<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentUser extends Model
{
    /**
     * Allowed respondent domiciles: every kecamatan in Kota Cimahi and the
     * rest of Bandung Raya, grouped by city/regency for the identity form.
     */
    public const DOMICILES = [
        'Kota Cimahi' => [
            'Cimahi Selatan', 'Cimahi Tengah', 'Cimahi Utara',
        ],
        'Kota Bandung' => [
            'Andir', 'Antapani', 'Arcamanik', 'Astanaanyar', 'Babakan Ciparay', 'Bandung Kidul',
            'Bandung Kulon', 'Bandung Wetan', 'Batununggal', 'Bojongloa Kaler', 'Bojongloa Kidul',
            'Buahbatu', 'Cibeunying Kaler', 'Cibeunying Kidul', 'Cibiru', 'Cicendo', 'Cidadap',
            'Cinambo', 'Coblong', 'Gedebage', 'Kiaracondong', 'Lengkong', 'Mandalajati',
            'Panyileukan', 'Rancasari', 'Regol', 'Sukajadi', 'Sukasari', 'Sumur Bandung', 'Ujungberung',
        ],
        'Kabupaten Bandung' => [
            'Arjasari', 'Baleendah', 'Banjaran', 'Bojongsoang', 'Cangkuang', 'Cicalengka', 'Cikancung',
            'Cilengkrang', 'Cileunyi', 'Cimaung', 'Cimenyan', 'Ciparay', 'Ciwidey', 'Dayeuhkolot',
            'Ibun', 'Katapang', 'Kertasari', 'Kutawaringin', 'Majalaya', 'Margaasih', 'Margahayu',
            'Nagreg', 'Pacet', 'Pameungpeuk', 'Pangalengan', 'Paseh', 'Pasirjambu', 'Rancabali',
            'Rancaekek', 'Solokanjeruk', 'Soreang',
        ],
        'Kabupaten Bandung Barat' => [
            'Batujajar', 'Cihampelas', 'Cikalong Wetan', 'Cililin', 'Cipatat', 'Cipeundeuy',
            'Cipongkor', 'Cisarua', 'Gununghalu', 'Lembang', 'Ngamprah', 'Padalarang',
            'Parongpong', 'Rongga', 'Saguling', 'Sindangkerta',
        ],
    ];

    /**
     * Every allowed domicile as a flat list, for validation.
     */
    public static function domicileOptions(): array
    {
        return array_merge(...array_values(self::DOMICILES));
    }

    protected $fillable = [
        'name',
        'phone_last_digits',
        'gender',
        'age',
        'education',
        'domicile',
        'occupation_status',
    ];

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}

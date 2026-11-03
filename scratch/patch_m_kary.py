import re

file_path = r'models/m_kary/m_kary-custom.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """    public function scopelanding($model)
    {
        return $model->select('m_kary.id', 'm_kary.kode','m_kary.nama_lengkap', 'm_kary.nama_depan', 'tipe_karyawan.value as tipe_karyawan.value' ,'tipe_karyawan.value as tipe_karyawan_value', 'm_dir.nama as m_dir.nama', 'm_divisi.nama as m_divisi.nama', 'm_kary.is_active');
    }"""

replacement = """    public function scopelanding($model)
    {
        try {
            \DB::statement("
                UPDATE m_kary
                SET is_active = false,
                    tgl_berhenti = COALESCE(t_resign.date, m_kary.tgl_berhenti, CURRENT_DATE),
                    alasan_berhenti = COALESCE(t_resign.keterangan, m_kary.alasan_berhenti, 'Pengunduran Diri')
                FROM t_resign
                WHERE t_resign.m_kary_id = m_kary.id
                  AND t_resign.status = 'POSTED'
                  AND m_kary.is_active = true
            ");

            \DB::statement("
                UPDATE m_kary_det_kontrak
                SET status = false
                FROM t_resign
                WHERE t_resign.m_kary_id = m_kary_det_kontrak.m_karyawan_id
                  AND t_resign.status = 'POSTED'
                  AND m_kary_det_kontrak.status = true
            ");

            \DB::statement("
                UPDATE default_users
                SET is_active = false
                FROM t_resign
                WHERE t_resign.m_kary_id = default_users.m_kary_id
                  AND t_resign.status = 'POSTED'
                  AND default_users.is_active = true
            ");
        } catch (\Throwable $e) {
        }

        return $model->select('m_kary.id', 'm_kary.kode','m_kary.nama_lengkap', 'm_kary.nama_depan', 'tipe_karyawan.value as tipe_karyawan.value' ,'tipe_karyawan.value as tipe_karyawan_value', 'm_dir.nama as m_dir.nama', 'm_divisi.nama as m_divisi.nama', 'm_kary.is_active');
    }"""

# Normalize line endings for replacement
norm_content = content.replace('\r\n', '\n')
norm_target = target.replace('\r\n', '\n')
norm_replacement = replacement.replace('\r\n', '\n')

if norm_target in norm_content:
    new_content = norm_content.replace(norm_target, norm_replacement, 1)
    # Restore CRLF
    new_content = new_content.replace('\n', '\r\n')
    with open(file_path, 'wb') as f:
        f.write(new_content.encode('utf-8'))
    print("Successfully patched m_kary-custom.php")
else:
    print("Target not found in m_kary-custom.php")


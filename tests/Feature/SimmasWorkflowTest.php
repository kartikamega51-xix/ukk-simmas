<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JurnalHarian;
use App\Models\PenempatanMagang;
use App\Models\PengajuanMagang;
use App\Models\Profile;
use App\Models\Siswa;
use App\Models\TempatMagang;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SimmasWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_landing_page_and_login_page_are_accessible(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Magang lebih teratur.');
        $this->get('/login')->assertStatus(200)->assertSee('PORTAL MASUK');
    }

    public function test_multi_role_login_and_cross_role_protection(): void
    {
        // Login Admin
        $res = $this->post('/login', [
            'email' => 'admin@simmas.sch.id',
            'password' => 'password',
        ]);
        $res->assertRedirect('/admin/dashboard');

        // Admin mencoba akses route Guru -> diarahkan kembali ke dashboard admin
        $this->get('/guru/dashboard')->assertRedirect('/admin/dashboard');

        $this->post('/logout');

        // Login Guru
        $resGuru = $this->post('/login', [
            'email' => 'guru@simmas.sch.id',
            'password' => 'password',
        ]);
        $resGuru->assertRedirect('/guru/dashboard');

        // Guru mencoba akses route Admin -> ditolak/redirect ke guru dashboard
        $this->get('/admin/guru')->assertRedirect('/guru/dashboard');

        $this->post('/logout');

        // Login Siswa
        $resSiswa = $this->post('/login', [
            'email' => 'siswa@simmas.sch.id',
            'password' => 'password',
        ]);
        $resSiswa->assertRedirect('/siswa/dashboard');
    }

    public function test_inactive_account_cannot_login(): void
    {
        $siswaProfile = Profile::where('email', 'siswa@simmas.sch.id')->firstOrFail();
        $siswaProfile->update(['is_active' => false]);

        $res = $this->post('/login', [
            'email' => 'siswa@simmas.sch.id',
            'password' => 'password',
        ]);

        $res->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_crud_and_delete_protection_rules(): void
    {
        $admin = Profile::where('email', 'admin@simmas.sch.id')->firstOrFail();
        $this->actingAs($admin);

        // Tambah Guru otomatis membuat akun
        $this->post('/admin/guru', [
            'nama' => 'Drs. Hendro Utomo',
            'nip' => '198001012005011099',
            'jurusan' => 'Rekayasa Perangkat Lunak',
        ])->assertRedirect('/admin/guru')->assertSessionHas('created_account');

        // Guru yang masih membimbing siswa aktif tidak boleh dihapus
        $activeGuru = Guru::whereHas('profile', fn ($q) => $q->where('email', 'guru@simmas.sch.id'))->firstOrFail();
        $this->delete("/admin/guru/{$activeGuru->id}")
            ->assertRedirect('/admin/guru')
            ->assertSessionHas('toast_error');
        $this->assertDatabaseHas('guru', ['id' => $activeGuru->id]);

        // DUDI yang masih ada siswa aktif tidak boleh dihapus
        $activeDudi = TempatMagang::where('nama_perusahaan', 'PT. Universal Big Data')->firstOrFail();
        $this->delete("/admin/dudi/{$activeDudi->id}")
            ->assertRedirect('/admin/dudi')
            ->assertSessionHas('toast_error');
        $this->assertDatabaseHas('tempat_magang', ['id' => $activeDudi->id]);
    }

    public function test_full_internship_flow_pengajuan_absensi_jurnal_validasi_and_nilai(): void
    {
        Storage::fake('public');

        // 1. Siswa yang belum magang mengajukan tempat magang
        $bagusProfile = Profile::where('email', '20261007@siswa.smk.sch.id')->firstOrFail();
        $dudi = TempatMagang::where('nama_perusahaan', 'PT. Telkom Sidoarjo')->firstOrFail();

        $this->actingAs($bagusProfile)->post('/siswa/pengajuan', [
            'tempat_magang_id' => $dudi->id,
            'posisi' => 'IT Support Intern',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonths(3)->toDateString(),
        ])->assertRedirect('/siswa/pengajuan');

        $pengajuan = PengajuanMagang::where('siswa_id', $bagusProfile->siswa->id)
            ->where('status', 'menunggu')
            ->firstOrFail();

        // 2. Admin menyetujui pengajuan dan menetapkan guru pembimbing
        $admin = Profile::where('email', 'admin@simmas.sch.id')->firstOrFail();
        $guru = Guru::whereHas('profile', fn ($q) => $q->where('email', 'guru@simmas.sch.id'))->firstOrFail();

        $this->actingAs($admin)->patch("/admin/pengajuan/{$pengajuan->id}/review", [
            'status' => 'disetujui',
            'guru_id' => $guru->id,
        ])->assertSessionHas('toast_success');

        $penempatan = PenempatanMagang::where('siswa_id', $bagusProfile->siswa->id)->firstOrFail();
        $this->assertEquals('disahkan', $penempatan->status_pengesahan);

        // 3. Siswa melakukan Clock In dengan foto bukti
        $photo = UploadedFile::fake()->image('selfie.jpg');
        $this->actingAs($bagusProfile)->post('/siswa/absensi', [
            'status' => 'hadir',
            'tanggal' => now()->toDateString(),
            'jam_masuk' => '07:50',
            'photo_masuk' => $photo,
        ])->assertRedirect('/siswa/absensi');

        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $bagusProfile->siswa->id,
            'status' => 'hadir',
        ]);

        // 4. Siswa menulis jurnal harian
        $this->actingAs($bagusProfile)->post('/siswa/jurnal', [
            'tanggal' => now()->toDateString(),
            'kegiatan' => 'Melakukan instalasi dan konfigurasi perangkat router jaringan di lantai 2.',
            'kendala' => 'Kabel UTP kurang panjang.',
            'solusi' => 'Melakukan crimping kabel baru.',
        ])->assertRedirect('/siswa/jurnal');

        $jurnal = JurnalHarian::where('siswa_id', $bagusProfile->siswa->id)->firstOrFail();

        // 5. Guru memvalidasi jurnal (Setujui) & memberikan nilai akhir
        $guruProfile = $guru->profile;
        $this->actingAs($guruProfile)->patch("/guru/jurnal/{$jurnal->id}/validasi", [
            'status_verifikasi' => 'disetujui',
            'catatan_guru' => 'Pekerjaan sangat baik dan rapi.',
        ])->assertRedirect(route('guru.jurnal', ['tab' => 'jurnal']));

        // Jurnal yang sudah disetujui tidak boleh dihapus oleh siswa
        $this->actingAs($bagusProfile)->delete("/siswa/jurnal/{$jurnal->id}")
            ->assertSessionHas('toast_error');
        $this->assertDatabaseHas('jurnal_harian', ['id' => $jurnal->id]);

        // Guru memberikan nilai akhir
        $this->actingAs($guruProfile)->patch("/guru/siswa/{$penempatan->id}/nilai", [
            'nilai_akhir' => 95,
        ])->assertRedirect('/guru/siswa');

        $this->assertEquals(95.0, $penempatan->fresh()->nilai_akhir);
    }
}


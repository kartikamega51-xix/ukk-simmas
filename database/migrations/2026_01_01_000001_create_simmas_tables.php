<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add max(uuid) and min(uuid) support for PostgreSQL if missing
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                CREATE OR REPLACE FUNCTION max(uuid, uuid)
                RETURNS uuid AS $$
                BEGIN
                    RETURN GREATEST($1, $2);
                END;
                $$ LANGUAGE plpgsql;
            ');
            DB::statement('
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_proc p
                        JOIN pg_aggregate a ON a.aggfnoid = p.oid
                        WHERE p.proname = \'max\' AND p.proargtypes[0] = \'uuid\'::regtype
                    ) THEN
                        CREATE AGGREGATE max(uuid) (
                            SFUNC = max,
                            STYPE = uuid
                        );
                    END IF;
                END $$;
            ');
            DB::statement('
                CREATE OR REPLACE FUNCTION min(uuid, uuid)
                RETURNS uuid AS $$
                BEGIN
                    RETURN LEAST($1, $2);
                END;
                $$ LANGUAGE plpgsql;
            ');
            DB::statement('
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_proc p
                        JOIN pg_aggregate a ON a.aggfnoid = p.oid
                        WHERE p.proname = \'min\' AND p.proargtypes[0] = \'uuid\'::regtype
                    ) THEN
                        CREATE AGGREGATE min(uuid) (
                            SFUNC = min,
                            STYPE = uuid
                        );
                    END IF;
                END $$;
            ');
        }

        // Drop default users table if exists to keep schema clean around profiles
        Schema::dropIfExists('users');

        // 1) profiles
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('nama');
            $table->text('email')->unique();
            $table->string('password');
            $table->string('role', 20)->default('siswa'); // admin|guru|siswa
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 2) guru
        Schema::create('guru', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->text('nip')->unique(); // 18 digit
            $table->text('jurusan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->index('user_id');
        });

        // 3) siswa
        Schema::create('siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->text('nis')->unique();
            $table->text('kelas');
            $table->string('status_magang', 30)->default('belum_magang'); // belum_magang|pengajuan|sedang_magang|lulus
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->index('user_id');
        });

        // 4) tempat_magang
        Schema::create('tempat_magang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('nama_perusahaan');
            $table->text('alamat');
            $table->text('bidang_usaha');
            $table->integer('kuota')->default(0);
            $table->text('penanggung_jawab');
            $table->text('kontak_pic');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 5) penempatan_magang
        Schema::create('penempatan_magang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('tempat_magang_id');
            $table->uuid('guru_id');
            $table->string('status_pengesahan', 30)->default('belum_disahkan'); // belum_disahkan|disahkan|lulus_magang
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('catatan_admin')->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tempat_magang_id')->references('id')->on('tempat_magang')->onDelete('cascade');
            $table->foreign('guru_id')->references('id')->on('guru')->onDelete('cascade');

            $table->index('siswa_id');
            $table->index('tempat_magang_id');
            $table->index('guru_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE penempatan_magang ADD CONSTRAINT chk_nilai_akhir CHECK (nilai_akhir IS NULL OR (nilai_akhir >= 0 AND nilai_akhir <= 100))');
        }

        // 6) pengajuan_magang
        Schema::create('pengajuan_magang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->uuid('tempat_magang_id');
            $table->text('posisi');
            $table->string('status', 30)->default('menunggu'); // menunggu|disetujui|ditolak
            $table->text('catatan_penolakan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamp('tanggal_pengajuan')->useCurrent();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tempat_magang_id')->references('id')->on('tempat_magang')->onDelete('cascade');

            $table->index('siswa_id');
            $table->index('tempat_magang_id');
        });

        // 7) absensi
        Schema::create('absensi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->date('tanggal')->useCurrent();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('status', 20)->default('hadir'); // hadir|sakit|izin|alfa
            $table->text('photo_masuk_url')->nullable();
            $table->text('photo_pulang_url')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status_validasi', 20)->default('disetujui'); // menunggu|disetujui|ditolak
            $table->text('catatan_guru')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->unique(['siswa_id', 'tanggal']);
            $table->index('siswa_id');
        });

        // 8) jurnal_harian
        Schema::create('jurnal_harian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('siswa_id');
            $table->date('tanggal');
            $table->text('kegiatan');
            $table->text('kendala')->nullable();
            $table->text('solusi')->nullable();
            $table->text('photo_url')->nullable();
            $table->string('status_verifikasi', 20)->default('menunggu'); // menunggu|disetujui|revisi
            $table->text('catatan_guru')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->index('siswa_id');
        });

        // 9) kunjungan
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guru_id');
            $table->uuid('tempat_magang_id');
            $table->date('tanggal');
            $table->text('catatan');
            $table->text('photo_url')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('guru_id')->references('id')->on('guru')->onDelete('cascade');
            $table->foreign('tempat_magang_id')->references('id')->on('tempat_magang')->onDelete('cascade');

            $table->index('guru_id');
            $table->index('tempat_magang_id');
        });

        // 10) activity_logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('timestamp')->useCurrent();
            $table->string('level', 15)->default('INFO'); // INFO|WARN|ERROR
            $table->text('action_type');
            $table->text('actor_email')->nullable();
            $table->text('actor_role')->nullable();
            $table->text('target')->nullable();
            $table->text('ip_address')->nullable();
            $table->jsonb('metadata')->nullable();
        });

        // Tambahan: settings (key-value untuk /admin/settings)
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('kunjungan');
        Schema::dropIfExists('jurnal_harian');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('pengajuan_magang');
        Schema::dropIfExists('penempatan_magang');
        Schema::dropIfExists('tempat_magang');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('profiles');
    }
};


<?php

namespace Tests\Feature;

require_once __DIR__ . '/_stubs_excel.php';

use App\Events\AntrianDiperbarui;
use App\Exports\DokterExport;
use App\Exports\JadwalPeriksaExport;
use App\Exports\ObatExport;
use App\Exports\PasienExport;
use App\Exports\RiwayatPasienExport;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FastTrackTest extends TestCase
{
    use RefreshDatabase;

    private Poli $poli;
    private User $admin;
    private User $dokter;
    private User $dokter2;
    private User $pasienA;
    private User $pasienB;
    private JadwalPeriksa $jadwal;
    private Obat $obat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->poli = Poli::create(['nama_poli' => 'Poli Umum']);

        $this->admin   = $this->mk('Admin', 'admin@t.test', 'admin');
        $this->dokter  = $this->mk('Dr. Satu', 'd1@t.test', 'dokter', ['id_poli' => $this->poli->id]);
        $this->dokter2 = $this->mk('Dr. Dua', 'd2@t.test', 'dokter', ['id_poli' => $this->poli->id]);
        $this->pasienA = $this->mk('Pasien A', 'a@t.test', 'pasien', ['no_rm' => '202610-001']);
        $this->pasienB = $this->mk('Pasien B', 'b@t.test', 'pasien', ['no_rm' => '202610-002']);

        $this->jadwal = JadwalPeriksa::create([
            'id_dokter' => $this->dokter->id, 'hari' => 'Senin',
            'jam_mulai' => '08:00:00', 'jam_selesai' => '10:00:00', 'status' => 'Aktif',
        ]);

        $this->obat = Obat::create(['nama_obat' => 'Paracetamol', 'kemasan' => 'Strip', 'harga' => 5000, 'stok' => 3]);
    }

    private function mk(string $nama, string $email, string $role, array $extra = []): User
    {
        return User::create(array_merge([
            'nama' => $nama, 'email' => $email, 'password' => 'password123', 'role' => $role,
            'no_ktp' => (string) random_int(1000000000, 9999999999), 'no_hp' => '0812', 'alamat' => 'Semarang',
        ], $extra));
    }

    private function daftar(User $pasien, string $keluhan = 'Pusing'): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($pasien)->post(route('pasien.daftar.submit'), [
            'id_poli' => $this->poli->id, 'id_jadwal' => $this->jadwal->id, 'keluhan' => $keluhan,
        ]);
    }

    private function periksa(DaftarPoli $dp, array $obat = null): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->dokter)->post(route('periksa-pasien.store'), [
            'id_daftar_poli' => $dp->id, 'obat' => $obat ?? [$this->obat->id], 'catatan' => 'Istirahat',
        ]);
    }

    // ---------------------------------------------------------------- 1 & 4. Dashboard + antrian real-time

    public function test_pendaftaran_aturan_satu_antrian_dan_dashboard(): void
    {
        $this->daftar($this->pasienA)->assertRedirect(route('pasien.dashboard'));
        $this->assertDatabaseHas('daftar_poli', ['id_pasien' => $this->pasienA->id, 'no_antrian' => 1]);

        // tidak boleh mendaftar lagi (apa pun jadwalnya) selama belum diperiksa
        $this->daftar($this->pasienA)->assertSessionHasErrors('id_jadwal');
        $this->assertSame(1, DaftarPoli::count());

        // halaman daftar menampilkan peringatan + tombol nonaktif
        $this->get(route('pasien.daftar'))->assertOk()->assertSee('masih memiliki antrian aktif');

        // dashboard: banner + tabel
        $this->get(route('pasien.dashboard'))->assertOk()
            ->assertSee('Antrian Aktif Anda')->assertSee('Nomor Anda')->assertSee('Sedang Dilayani')
            ->assertSee('data-nomor-jadwal="' . $this->jadwal->id . '"', false)
            ->assertSee("window.Echo.channel('antrian').listen('.antrian.diperbarui'", false)
            ->assertSee('Poli Umum')->assertSee('Dr. Satu');
    }

    public function test_dashboard_tanpa_antrian_menampilkan_pesan_kosong(): void
    {
        $this->actingAs($this->pasienA)->get(route('pasien.dashboard'))->assertOk()
            ->assertSee('belum memiliki antrian aktif')->assertDontSee('Antrian Aktif Anda');
    }

    public function test_nomor_dilayani_dan_event_realtime(): void
    {
        Event::fake([AntrianDiperbarui::class]);

        $this->daftar($this->pasienA);
        Event::assertDispatched(AntrianDiperbarui::class,
            fn ($e) => $e->idJadwal === $this->jadwal->id && $e->sedangDilayani === 1);

        $this->daftar($this->pasienB);
        $this->assertSame(2, DaftarPoli::where('id_pasien', $this->pasienB->id)->value('no_antrian'));

        // B melihat: nomor saya 2, sedang dilayani 1
        $this->actingAs($this->pasienB)->get(route('pasien.dashboard'))
            ->assertViewHas('dilayani', fn ($d) => (int) $d[$this->jadwal->id] === 1);

        // dokter selesai memeriksa A -> nomor yang dilayani maju ke 2 dan disiarkan
        $this->periksa(DaftarPoli::where('id_pasien', $this->pasienA->id)->first())->assertRedirect(route('periksa-pasien.index'));
        Event::assertDispatched(AntrianDiperbarui::class,
            fn ($e) => $e->idJadwal === $this->jadwal->id && $e->sedangDilayani === 2);

        // pasien A sudah selesai -> boleh daftar lagi
        $this->daftar($this->pasienA)->assertRedirect(route('pasien.dashboard'));
        $this->assertSame(2, DaftarPoli::where('id_pasien', $this->pasienA->id)->count());
    }

    public function test_event_payload_dan_channel(): void
    {
        $e = new AntrianDiperbarui(7, 3);
        $this->assertSame('antrian.diperbarui', $e->broadcastAs());
        $this->assertSame(['id_jadwal' => 7, 'sedang_dilayani' => 3], $e->broadcastWith());
        $this->assertSame('antrian', $e->broadcastOn()[0]->name);
    }

    public function test_gagal_broadcast_tidak_menggagalkan_pendaftaran(): void
    {
        // driver "reverb" belum terpasang/server mati -> hanya dicatat, pendaftaran tetap sukses
        config(['broadcasting.default' => 'reverb']);

        $this->daftar($this->pasienA)->assertRedirect(route('pasien.dashboard'));
        $this->assertSame(1, DaftarPoli::count());
    }

    public function test_log_driver_berjalan_normal(): void
    {
        config(['broadcasting.default' => 'log']);
        $this->daftar($this->pasienA)->assertRedirect(route('pasien.dashboard'));
        AntrianDiperbarui::kirim($this->jadwal->id);
        $this->assertTrue(true);
    }

    // ---------------------------------------------------------------- Periksa: tagihan + stok

    public function test_periksa_membuat_tagihan_dan_mengurangi_stok(): void
    {
        Event::fake([AntrianDiperbarui::class]);
        $this->daftar($this->pasienA);
        $dp = DaftarPoli::first();

        $this->periksa($dp);

        $periksa = Periksa::first();
        $this->assertSame(155000, (int) $periksa->biaya_periksa);
        $this->assertDatabaseHas('pembayaran', [
            'id_periksa' => $periksa->id, 'jumlah' => 155000, 'status' => Pembayaran::BELUM_BAYAR,
        ]);
        $this->assertSame(2, $this->obat->fresh()->stok);
    }

    public function test_stok_habis_membatalkan_pemeriksaan(): void
    {
        Event::fake([AntrianDiperbarui::class]);
        $this->obat->update(['stok' => 0]);
        $this->daftar($this->pasienA);

        $this->periksa(DaftarPoli::first())->assertSessionHasErrors('obat');

        $this->assertSame(0, Periksa::count());
        $this->assertSame(0, Pembayaran::count());
        $this->assertSame(0, $this->obat->fresh()->stok);
    }

    // ---------------------------------------------------------------- 3. Riwayat pendaftaran

    public function test_riwayat_pendaftaran_dan_detail(): void
    {
        Event::fake([AntrianDiperbarui::class]);
        $this->daftar($this->pasienA);
        $dp = DaftarPoli::first();

        // belum diperiksa -> halaman detail 404, tombol detail tidak ada
        $this->actingAs($this->pasienA)->get(route('pasien.riwayat.index'))->assertOk()
            ->assertSee('Menunggu')->assertDontSee('Detail');
        $this->get(route('pasien.riwayat.show', $dp->id))->assertNotFound();

        $this->periksa($dp);

        $this->actingAs($this->pasienA)->get(route('pasien.riwayat.index'))->assertOk()
            ->assertSee('Sudah Diperiksa')->assertSee('Detail')->assertSee('Poli Umum')->assertSee('Dr. Satu');

        $this->get(route('pasien.riwayat.show', $dp->id))->assertOk()
            ->assertSee('Istirahat')->assertSee('Paracetamol')->assertSee('155.000');

        // pasien lain tidak boleh melihat
        $this->actingAs($this->pasienB)->get(route('pasien.riwayat.show', $dp->id))->assertNotFound();
    }

    public function test_riwayat_tidak_n_plus_1(): void
    {
        Event::fake([AntrianDiperbarui::class]);

        foreach (range(1, 6) as $i) {
            $dp = DaftarPoli::create([
                'id_pasien' => $this->pasienA->id, 'id_jadwal' => $this->jadwal->id,
                'keluhan' => "k$i", 'no_antrian' => $i,
            ]);
            Periksa::create(['id_daftar_poli' => $dp->id, 'tgl_periksa' => now(), 'biaya_periksa' => 1000]);
        }

        $this->actingAs($this->pasienA);
        DB::enableQueryLog();
        $this->get(route('pasien.riwayat.index'))->assertOk();
        $jumlah = count(DB::getQueryLog());

        // banyak baris, jumlah query tetap kecil (session/user/daftar_poli/jadwal/user/poli/periksa)
        $this->assertLessThanOrEqual(10, $jumlah, "Query terlalu banyak: $jumlah");
    }

    // ---------------------------------------------------------------- 6 & 2. Pembayaran + notifikasi

    private function siapkanTagihan(): Pembayaran
    {
        Event::fake([AntrianDiperbarui::class]);
        $this->daftar($this->pasienA);
        $this->periksa(DaftarPoli::first());

        return Pembayaran::first();
    }

    public function test_alur_pembayaran_dan_notifikasi(): void
    {
        Storage::fake('public');
        $p = $this->siapkanTagihan();

        // halaman pembayaran pasien
        $this->actingAs($this->pasienA)->get(route('pasien.pembayaran.index'))->assertOk()
            ->assertSee('Belum Dibayar')->assertSee('Upload Bukti')->assertSee('155.000');

        // pasien lain tidak boleh upload tagihan orang lain
        $this->actingAs($this->pasienB)->post(route('pasien.pembayaran.upload', $p->id), [
            'bukti_bayar' => UploadedFile::fake()->image('x.jpg'),
        ])->assertForbidden();

        // validasi: bukan gambar / kosong
        $this->actingAs($this->pasienA)->post(route('pasien.pembayaran.upload', $p->id), [
            'bukti_bayar' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('bukti_bayar');
        $this->post(route('pasien.pembayaran.upload', $p->id), [])->assertSessionHasErrors('bukti_bayar');
        $this->assertSame(0, $this->dokter->notifications()->count());

        // upload sukses
        $this->post(route('pasien.pembayaran.upload', $p->id), [
            'bukti_bayar' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('pasien.pembayaran.index'))->assertSessionHas('success');

        $p->refresh();
        $this->assertSame(Pembayaran::MENUNGGU_VERIFIKASI, $p->status);
        Storage::disk('public')->assertExists($p->bukti_bayar);

        // dokter yang memeriksa menerima TEPAT satu notifikasi
        $this->assertSame(1, $this->dokter->notifications()->count());
        $this->assertSame(0, $this->dokter2->notifications()->count());
        $this->assertStringContainsString('Pasien A', $this->dokter->notifications()->first()->data['pesan']);

        // upload ulang ditolak, tidak ada notifikasi ganda
        $this->actingAs($this->pasienA)->post(route('pasien.pembayaran.upload', $p->id), [
            'bukti_bayar' => UploadedFile::fake()->image('lagi.jpg'),
        ])->assertSessionHas('error');
        $this->assertSame(1, $this->dokter->notifications()->count());

        // pasien tidak boleh memverifikasi
        $this->post(route('admin.pembayaran.verifikasi', $p->id))->assertForbidden();

        // admin melihat daftar + verifikasi
        $this->actingAs($this->admin)->get(route('admin.pembayaran.index'))->assertOk()
            ->assertSee('Pasien A')->assertSee('Konfirmasi')->assertSee('1 menunggu verifikasi');

        $this->post(route('admin.pembayaran.verifikasi', $p->id))->assertSessionHas('success');
        $p->refresh();
        $this->assertSame(Pembayaran::LUNAS, $p->status);
        $this->assertSame($this->admin->id, (int) $p->diverifikasi_oleh);

        // pasien menerima TEPAT satu notifikasi; verifikasi ganda tidak menambah
        $this->assertSame(1, $this->pasienA->notifications()->count());
        $this->post(route('admin.pembayaran.verifikasi', $p->id))->assertSessionHas('error');
        $this->assertSame(1, $this->pasienA->notifications()->count());
        $this->assertSame(0, $this->pasienB->notifications()->count());
    }

    public function test_verifikasi_tanpa_bukti_ditolak(): void
    {
        $p = $this->siapkanTagihan();

        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $p->id))->assertSessionHas('error');
        $this->assertSame(Pembayaran::BELUM_BAYAR, $p->fresh()->status);
        $this->assertSame(0, $this->pasienA->notifications()->count());
    }

    public function test_notifikasi_hanya_untuk_pemilik_dan_bisa_ditandai(): void
    {
        Storage::fake('public');
        $p = $this->siapkanTagihan();
        $this->actingAs($this->pasienA)->post(route('pasien.pembayaran.upload', $p->id), [
            'bukti_bayar' => UploadedFile::fake()->image('b.png'),
        ]);
        $notif = $this->dokter->notifications()->first();

        // dokter lain / pasien tidak bisa membuka atau menandai notifikasi milik dokter
        foreach ([$this->dokter2, $this->pasienA, $this->admin] as $orangLain) {
            $this->actingAs($orangLain)->post(route('notifikasi.baca', $notif->id))->assertNotFound();
            $this->actingAs($orangLain)->get(route('notifikasi.buka', $notif->id))->assertNotFound();
        }
        $this->assertNull($notif->fresh()->read_at);

        // dokter: lonceng menampilkan jumlah belum dibaca + isi pesan
        $this->actingAs($this->dokter)->get(route('dokter.dashboard'))->assertOk()
            ->assertSee('Bukti pembayaran diunggah')->assertSee('Tandai dibaca')->assertSee('(1 baru)');

        // tandai dibaca
        $this->post(route('notifikasi.baca', $notif->id))->assertRedirect();
        $this->assertNotNull($notif->fresh()->read_at);
        $this->get(route('dokter.dashboard'))->assertDontSee('(1 baru)');
    }

    public function test_buka_notifikasi_menandai_dan_mengarahkan(): void
    {
        Storage::fake('public');
        $p = $this->siapkanTagihan();
        $this->actingAs($this->pasienA)->post(route('pasien.pembayaran.upload', $p->id), [
            'bukti_bayar' => UploadedFile::fake()->image('b.png'),
        ]);
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $p->id));
        $notif = $this->pasienA->notifications()->first();

        $this->actingAs($this->pasienA)->get(route('notifikasi.buka', $notif->id))
            ->assertRedirect('/pasien/pembayaran');
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_tandai_semua_dan_pesan_kosong(): void
    {
        $this->actingAs($this->dokter)->get(route('dokter.dashboard'))->assertSee('Belum ada notifikasi');

        $this->dokter->notify(new \App\Notifications\PembayaranDiunggah(
            Pembayaran::create(['id_periksa' => $this->bikinPeriksa()->id, 'jumlah' => 1000]), $this->pasienA));
        $this->dokter->notify(new \App\Notifications\PembayaranDiverifikasi(Pembayaran::first()));
        $this->assertSame(2, $this->dokter->unreadNotifications()->count());

        $this->post(route('notifikasi.bacaSemua'))->assertRedirect();
        $this->assertSame(0, $this->dokter->unreadNotifications()->count());
    }

    private function bikinPeriksa(): Periksa
    {
        $dp = DaftarPoli::create([
            'id_pasien' => $this->pasienA->id, 'id_jadwal' => $this->jadwal->id, 'keluhan' => 'x', 'no_antrian' => 1,
        ]);

        return Periksa::create(['id_daftar_poli' => $dp->id, 'tgl_periksa' => now(), 'biaya_periksa' => 1000]);
    }

    // ---------------------------------------------------------------- 7. Ganti password

    public function test_profile_tampil_untuk_semua_role_dengan_menu_sesuai_role(): void
    {
        $this->actingAs($this->admin)->get(route('profile.edit'))->assertOk()
            ->assertSee('Ganti Password')->assertSee('Menu Admin')->assertSee('Verifikasi Pembayaran');
        $this->actingAs($this->dokter)->get(route('profile.edit'))->assertOk()
            ->assertSee('Menu Dokter')->assertSee('Riwayat Pasien');
        $this->actingAs($this->pasienA)->get(route('profile.edit'))->assertOk()
            ->assertSee('Menu Pasien')->assertSee('Riwayat Pendaftaran')->assertSee('Pembayaran')->assertSee('202610-001');
    }

    public function test_ganti_password_validasi(): void
    {
        $this->actingAs($this->pasienA)->from(route('profile.edit'));
        $url = route('profile.password.update');

        // password lama salah
        $this->put($url, ['current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertSessionHasErrors(['current_password' => 'Password lama tidak sesuai.']);
        // password lama kosong
        $this->put($url, ['password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertSessionHasErrors('current_password');
        // minimal 8
        $this->put($url, ['current_password' => 'password123', 'password' => 'abc', 'password_confirmation' => 'abc'])
            ->assertSessionHasErrors(['password' => 'Password baru minimal 8 karakter.']);
        // konfirmasi beda
        $this->put($url, ['current_password' => 'password123', 'password' => 'baru12345', 'password_confirmation' => 'lain12345'])
            ->assertSessionHasErrors(['password' => 'Konfirmasi password baru tidak sama.']);
        // sama dengan password lama
        $this->put($url, ['current_password' => 'password123', 'password' => 'password123', 'password_confirmation' => 'password123'])
            ->assertSessionHasErrors(['password' => 'Password baru tidak boleh sama dengan password lama.']);

        // tidak ada yang berubah
        $this->assertTrue(Hash::check('password123', $this->pasienA->fresh()->password));
    }

    public function test_ganti_password_berhasil_dan_ter_hash(): void
    {
        $this->actingAs($this->dokter)->put(route('profile.password.update'), [
            'current_password' => 'password123', 'password' => 'passwordBaru1', 'password_confirmation' => 'passwordBaru1',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success', 'Password berhasil diganti.');

        $hash = $this->dokter->fresh()->password;
        $this->assertNotSame('passwordBaru1', $hash);
        $this->assertTrue(Hash::check('passwordBaru1', $hash));
        // hanya akun sendiri yang berubah
        $this->assertTrue(Hash::check('password123', $this->dokter2->fresh()->password));
        $this->assertTrue(Hash::check('password123', $this->pasienA->fresh()->password));
    }

    public function test_ganti_password_wajib_login(): void
    {
        $this->put(route('profile.password.update'), [])->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------- 5. Export Excel

    public function test_export_data_dan_batas_role(): void
    {
        // data dokter
        $dokter = new DokterExport();
        $this->assertSame(2, $dokter->collection()->count());
        $this->assertSame(['No', 'Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'], $dokter->headings());
        $row = $dokter->map($dokter->collection()->first());
        $this->assertSame(1, $row[0]);
        $this->assertSame('Poli Umum', $row[6]);

        // data pasien
        $pasien = new PasienExport();
        $this->assertSame(2, $pasien->collection()->count());
        $this->assertSame('202610-001', $pasien->map($pasien->collection()->first())[6]);

        // data obat memuat harga & stok
        $obat = new ObatExport();
        $this->assertContains('Stok', $obat->headings());
        $this->assertContains('Harga (Rp)', $obat->headings());
        $this->assertSame([1, 'Paracetamol', 'Strip', 5000, 3], $obat->map($obat->collection()->first()));

        // jadwal: hanya milik dokter tertentu
        JadwalPeriksa::create(['id_dokter' => $this->dokter2->id, 'hari' => 'Selasa',
            'jam_mulai' => '09:00:00', 'jam_selesai' => '11:00:00', 'status' => 'Aktif']);
        $j1 = new JadwalPeriksaExport($this->dokter->id);
        $this->assertSame(1, $j1->collection()->count());
        $this->assertSame([1, 'Senin', '08:00', '10:00', 'Aktif'], $j1->map($j1->collection()->first()));
        $this->assertSame('Selasa', (new JadwalPeriksaExport($this->dokter2->id))->collection()->first()->hari);

        // riwayat: hanya pasien yang diperiksa dokter tersebut + obat digabung
        Event::fake([AntrianDiperbarui::class]);
        $this->daftar($this->pasienA);
        $this->periksa(DaftarPoli::first());
        $r1 = new RiwayatPasienExport($this->dokter->id);
        $this->assertSame(1, $r1->collection()->count());
        $map = $r1->map($r1->collection()->first());
        $this->assertSame('Pasien A', $map[1]);
        $this->assertSame('Paracetamol', $map[5]);
        $this->assertSame(155000, $map[6]);
        $this->assertSame(0, (new RiwayatPasienExport($this->dokter2->id))->collection()->count());

        // batas akses route export
        $this->actingAs($this->pasienA)->get(route('export.obat'))->assertForbidden();
        $this->actingAs($this->dokter)->get(route('export.dokter'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('export.jadwal-periksa'))->assertForbidden();
    }

    // ---------------------------------------------------------------- Halaman: tombol export + stok pada obat

    public function test_halaman_admin_dan_dokter_menampilkan_tombol_export_dan_stok(): void
    {
        $this->actingAs($this->admin);
        foreach (['dokter.index' => 'export.dokter', 'pasien.index' => 'export.pasien', 'obat.index' => 'export.obat'] as $page => $export) {
            $this->get(route($page))->assertOk()->assertSee('Export Excel')->assertSee(route($export), false);
        }
        $this->get(route('obat.index'))->assertSee('Stok');
        $this->get(route('obat.create'))->assertOk()->assertSee('name="stok"', false);
        $this->get(route('obat.edit', $this->obat->id))->assertOk()->assertSee('name="stok"', false);

        $this->actingAs($this->dokter);
        $this->get(route('jadwal-periksa.index'))->assertOk()->assertSee(route('export.jadwal-periksa'), false);
        $this->get(route('riwayat-pasien.index'))->assertOk()->assertSee(route('export.riwayat-pasien'), false);
    }

    public function test_crud_obat_dengan_stok(): void
    {
        $this->actingAs($this->admin)->post(route('obat.store'), [
            'nama_obat' => 'Amoxicillin', 'kemasan' => 'Botol', 'harga' => 12000, 'stok' => 20,
        ])->assertRedirect(route('obat.index'));
        $this->assertDatabaseHas('obat', ['nama_obat' => 'Amoxicillin', 'stok' => 20]);

        $this->post(route('obat.store'), ['nama_obat' => 'X', 'kemasan' => 'Y', 'harga' => 1])
            ->assertSessionHasErrors('stok');

        $obat = Obat::where('nama_obat', 'Amoxicillin')->first();
        $this->put(route('obat.update', $obat->id), [
            'nama_obat' => 'Amoxicillin', 'kemasan' => 'Botol', 'harga' => 12000, 'stok' => 5,
        ])->assertRedirect(route('obat.index'));
        $this->assertSame(5, $obat->fresh()->stok);
    }

    public function test_halaman_dokter_periksa_menampilkan_stok(): void
    {
        Event::fake([AntrianDiperbarui::class]);
        $this->daftar($this->pasienA);
        $this->actingAs($this->dokter)->get(route('periksa-pasien.create', DaftarPoli::first()->id))
            ->assertOk()->assertSee('Stok: 3');
    }

    public function test_halaman_pembayaran_dan_riwayat_kosong(): void
    {
        $this->actingAs($this->pasienA)->get(route('pasien.pembayaran.index'))->assertOk()->assertSee('Belum ada tagihan');
        $this->get(route('pasien.riwayat.index'))->assertOk()->assertSee('Belum ada riwayat pendaftaran');
        $this->actingAs($this->admin)->get(route('admin.pembayaran.index'))->assertOk()->assertSee('Belum ada tagihan');
    }
}

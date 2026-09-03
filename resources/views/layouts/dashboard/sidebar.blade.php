{{-- ======= Sidebar ======= --}}
<aside id="sidebar" class="sidebar">

  @php
      $role  = Auth::user()?->activeRole() ?? '';
      $name  = Auth::user()->name  ?? 'Pengguna';

      $isAdmin    = $role === 'admin';
      $isKepsek   = $role === 'kepala sekolah';
      $isGuru     = $role === 'guru';
      $isWali     = $role === 'wali kelas';
      $isSiswa    = $role === 'siswa';
      $isOrangTua = $role === 'orang tua';

      $isPersonal = in_array($role, ['siswa', 'orang tua']);
  @endphp

  <ul class="sidebar-nav" id="sidebar-nav">

    {{-- DASHBOARD (semua role) --}}
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard') }}">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard</span>
      </a>
    </li>

    {{-- ==========================================
         A. MENU UNTUK ADMIN
         ========================================== --}}
    @if ($isAdmin)

      {{-- 1. MASTER DATA --}}
      <li class="nav-heading">Master Data</li>
      
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('profil-sekolah.*') ? '' : 'collapsed' }}" href="{{ route('profil-sekolah.index') }}">
          <i class="bi bi-building"></i><span>Profile Sekolah</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('siswa.*') ? '' : 'collapsed' }}" href="{{ route('siswa.index') }}">
          <i class="bi bi-person-lines-fill"></i><span>Siswa</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('jabatan.*') ? '' : 'collapsed' }}" href="{{ route('jabatan.index') }}">
          <i class="bi bi-briefcase"></i><span>Jabatan</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pegawai.*') ? '' : 'collapsed' }}" href="{{ route('pegawai.index') }}">
          <i class="bi bi-people"></i><span>Pegawai</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('tahun-ajaran.*', 'semester.*') ? '' : 'collapsed' }}"
           data-bs-target="#tahun-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-calendar3"></i><span>Tahun Ajaran & Semester</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tahun-nav" class="nav-content collapse {{ request()->routeIs('tahun-ajaran.*', 'semester.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li><a href="{{ route('tahun-ajaran.index') }}" class="{{ request()->routeIs('tahun-ajaran.*') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Tahun Ajaran</span></a></li>
          <li><a href="{{ route('semester.index') }}" class="{{ request()->routeIs('semester.*') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Semester</span></a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('kelas.*') ? '' : 'collapsed' }}" href="{{ route('kelas.index') }}">
          <i class="bi bi-door-open"></i><span>Kelas</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('guru.*') ? '' : 'collapsed' }}" href="{{ route('guru.index') }}">
          <i class="bi bi-person-badge"></i><span>Guru</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('walikelas.*') ? '' : 'collapsed' }}" href="{{ route('walikelas.index') }}">
          <i class="bi bi-person-workspace"></i><span>Wali Kelas</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('matapelajaran.index', 'matapelajaran.create', 'matapelajaran.edit', 'matapelajaran.show') ? '' : 'collapsed' }}" href="{{ route('matapelajaran.index') }}">
          <i class="bi bi-book"></i><span>Mata Pelajaran</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('jeniscatatan.*') ? '' : 'collapsed' }}" href="{{ route('jeniscatatan.index') }}">
          <i class="bi bi-tags"></i><span>Jenis Catatan</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('jeniskehadiran.*') ? '' : 'collapsed' }}" href="{{ route('jeniskehadiran.index') }}">
          <i class="bi bi-calendar-range"></i><span>Jenis Kehadiran</span>
        </a>
      </li>
      {{-- <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('orang-tua.*') ? '' : 'collapsed' }}" href="{{ route('orang-tua.index') }}">
          <i class="bi bi-person-hearts"></i><span>Orang Tua / Wali</span>
        </a>
      </li> --}}

      {{-- 2. DATA AKADEMIK --}}
      <li class="nav-heading">Data Akademik</li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pembagiankelas.*') ? '' : 'collapsed' }}" href="{{ route('pembagiankelas.index') }}">
          <i class="bi bi-diagram-3"></i><span>Pembagian Kelas</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('matapelajaranaktif.*') ? '' : 'collapsed' }}" href="{{ route('matapelajaranaktif.index') }}">
          <i class="bi bi-calendar-week"></i><span>Mata Pelajaran Aktif</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('ekstrakurikuler.*') ? '' : 'collapsed' }}" href="{{ route('ekstrakurikuler.index') }}">
          <i class="bi bi-trophy"></i><span>Ekstrakurikuler</span>
        </a>
      </li>

      {{-- 3. INFORMASI & PENGATURAN --}}
      <li class="nav-heading">Informasi & Pengaturan</li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pengumuman.*') ? '' : 'collapsed' }}" href="{{ route('pengumuman.index') }}">
          <i class="bi bi-megaphone"></i><span>Pengumuman</span>
        </a>
      </li>

      {{-- <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('materipembelajaran.*') ? '' : 'collapsed' }}" href="{{ route('materipembelajaran.index') }}">
          <i class="bi bi-file-earmark-text"></i><span>Materi Pembelajaran</span>
        </a>
      </li> --}}

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('user.*') ? '' : 'collapsed' }}" href="{{ route('user.index') }}">
          <i class="bi bi-person-gear"></i><span>Manajemen User/Pengguna</span>
        </a>
      </li>

      {{-- 4. AKADEMIK & NILAI SISWA --}}
      <li class="nav-heading">Akademik & Nilai</li>

      <li class="nav-item">
        <a class="nav-link {{ (request()->routeIs('nilai.rekap-mapel') || request()->routeIs('nilai.rekap-raport') || request()->routeIs('nilai.index')) ? '' : 'collapsed' }}"
           data-bs-target="#nilai-admin-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-check"></i><span>Rekap Nilai</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nilai-admin-nav" class="nav-content collapse {{ (request()->routeIs('nilai.rekap-mapel') || request()->routeIs('nilai.rekap-raport') || request()->routeIs('nilai.index')) ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li><a href="{{ route('nilai.rekap-mapel') }}" class="{{ request()->routeIs('nilai.rekap-mapel') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Rekap Nilai Per Mapel</span></a></li>
          <li><a href="{{ route('nilai.rekap-raport') }}" class="{{ request()->routeIs('nilai.rekap-raport') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Rekap Nilai Raport</span></a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('kehadiran.*') ? '' : 'collapsed' }}" href="{{ route('kehadiran.rekap') }}">
          <i class="bi bi-calendar-check"></i><span>Rekap Kehadiran</span>
        </a>
      </li>

      {{-- 5. LAPORAN & REKAPITULASI --}}
      <li class="nav-heading">Laporan & Rekapitulasi</li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('nilai.cetak-raport') ? '' : 'collapsed' }}" href="{{ route('nilai.cetak-raport') }}">
          <i class="bi bi-printer"></i><span>Cetak Raport</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('catatansiswa.*') ? '' : 'collapsed' }}" href="{{ route('catatansiswa.index') }}">
          <i class="bi bi-journal-text"></i><span>Catatan Siswa</span>
        </a>
      </li>

    @endif

    {{-- ==========================================
         B. MENU UNTUK KEPALA SEKOLAH
         ========================================== --}}
    @if ($isKepsek)
      <li class="nav-heading">Master Data</li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('profil-sekolah.*') ? '' : 'collapsed' }}" href="{{ route('profil-sekolah.index') }}"><i class="bi bi-building"></i><span>Profile Sekolah</span></a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('siswa.*') ? '' : 'collapsed' }}" href="{{ route('siswa.index') }}"><i class="bi bi-person-lines-fill"></i><span>Siswa</span></a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('guru.*') ? '' : 'collapsed' }}" href="{{ route('guru.index') }}"><i class="bi bi-person-badge"></i><span>Guru</span></a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('pegawai.*') ? '' : 'collapsed' }}" href="{{ route('pegawai.index') }}"><i class="bi bi-people"></i><span>Pegawai</span></a></li>

      <li class="nav-heading">Laporan & Rekapitulasi</li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.rekap-raport') ? '' : 'collapsed' }}" href="{{ route('nilai.rekap-raport') }}"><i class="bi bi-journal-check"></i><span>Rekap Nilai</span></a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('nilai.cetak-raport') ? '' : 'collapsed' }}" href="{{ route('nilai.cetak-raport') }}"><i class="bi bi-printer"></i><span>Cetak Raport</span></a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('kehadiran.rekap') ? '' : 'collapsed' }}" href="{{ route('kehadiran.rekap') }}"><i class="bi bi-calendar-check"></i><span>Rekap Kehadiran</span></a></li>
    @endif

    {{-- ==========================================
         C. MENU UNTUK GURU & WALI KELAS
         ========================================== --}}
    @if ($isGuru)
      
      <li class="nav-heading">Akademik & Kelas</li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('nilai.*') ? '' : 'collapsed' }}"
           data-bs-target="#nilai-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-check"></i><span>Nilai Siswa</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nilai-nav" class="nav-content collapse {{ request()->routeIs('nilai.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li class="px-3 py-1 text-secondary fw-semibold fs-7" style="font-size: 0.8rem; list-style-type: none;"><i class="bi bi-star me-1 text-warning"></i> Input Nilai</li>
          <li><a href="{{ route('nilai.harian') }}" class="{{ request()->routeIs('nilai.harian') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Input Nilai Harian</span></a></li>
          <li><a href="{{ route('nilai.mid') }}" class="{{ request()->routeIs('nilai.mid') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Input Nilai MID</span></a></li>
          <li><a href="{{ route('nilai.pas') }}" class="{{ request()->routeIs('nilai.pas') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Input Nilai PAS</span></a></li>
          <li><a href="{{ route('nilai.raport-input') }}" class="{{ request()->routeIs('nilai.raport-input') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Input Nilai Raport</span></a></li>
          
          <li class="px-3 py-1 mt-2 text-secondary fw-semibold fs-7" style="font-size: 0.8rem; list-style-type: none;"><i class="bi bi-star me-1 text-warning"></i> Rekap Nilai</li>
          <li><a href="{{ route('nilai.rekap-mapel') }}" class="{{ request()->routeIs('nilai.rekap-mapel') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Rekap Nilai Per Mapel</span></a></li>
          <li><a href="{{ route('nilai.rekap-raport') }}" class="{{ request()->routeIs('nilai.rekap-raport') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Rekap Nilai Raport</span></a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('kehadiran.*') ? '' : 'collapsed' }}"
           data-bs-target="#kehadiran-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-calendar-check"></i><span>Kehadiran Siswa</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="kehadiran-nav" class="nav-content collapse {{ request()->routeIs('kehadiran.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li><a href="{{ route('kehadiran.create') }}" class="{{ request()->routeIs('kehadiran.create') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Input Kehadiran</span></a></li>
          <li><a href="{{ route('kehadiran.rekap') }}" class="{{ request()->routeIs('kehadiran.rekap') ? 'active' : '' }}"><i class="bi bi-circle"></i><span>Rekap Kehadiran</span></a></li>
        </ul>
      </li>

      <li class="nav-heading">Informasi & Pengaturan</li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('pengumuman.*') ? '' : 'collapsed' }}" href="{{ route('pengumuman.index') }}"><i class="bi bi-megaphone"></i><span>Pengumuman</span></a></li>
      <li class="nav-item"><a class="nav-link {{ request()->routeIs('materipembelajaran.*') ? '' : 'collapsed' }}" href="{{ route('materipembelajaran.index') }}"><i class="bi bi-file-earmark-text"></i><span>Materi Pembelajaran</span></a></li>

    @endif

    @if ($isWali)
      
      <li class="nav-heading">Nilai Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('nilai.rekap-raport') ? '' : 'collapsed' }}" href="{{ route('nilai.rekap-raport') }}">
          <i class="bi bi-star"></i><span>Rekap Nilai</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('nilai.cetak-raport') ? '' : 'collapsed' }}" href="{{ route('nilai.cetak-raport') }}">
          <i class="bi bi-star"></i><span>Cetak Raport</span>
        </a>
      </li>

      <li class="nav-heading">Kehadiran Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('kehadiran.rekap') ? '' : 'collapsed' }}" href="{{ route('kehadiran.rekap') }}">
          <i class="bi bi-star"></i><span>Rekap Kehadiran</span>
        </a>
      </li>

      <li class="nav-heading">Ekstrakurikuler Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('ekstrakurikuler.siswa') ? '' : 'collapsed' }}" href="{{ route('ekstrakurikuler.siswa') }}">
          <i class="bi bi-star"></i><span>Input Ekstrakurikuler</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('ekstrakurikuler.rekap') ? '' : 'collapsed' }}" href="{{ route('ekstrakurikuler.rekap') }}">
          <i class="bi bi-star"></i><span>Rekap Ekstrakurikuler</span>
        </a>
      </li>

      <li class="nav-heading">Informasi & Pengaturan</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pengumuman.index') ? '' : 'collapsed' }}" href="{{ route('pengumuman.index') }}">
          <i class="bi bi-star"></i><span>Pengumuman</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('matapelajaran.jadwal') ? '' : 'collapsed' }}" href="{{ route('matapelajaran.jadwal') }}">
          <i class="bi bi-star"></i><span>Jadwal Mata Pelajaran</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('materipembelajaran.index') ? '' : 'collapsed' }}" href="{{ route('materipembelajaran.index') }}">
          <i class="bi bi-star"></i><span>Materi Pembelajaran</span>
        </a>
      </li>

    @endif

    {{-- ==========================================
         D. MENU UNTUK SISWA & ORANG TUA
         ========================================== --}}
    @if ($isPersonal)
      
      <li class="nav-heading">Profile Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('siswa.profile') ? '' : 'collapsed' }}" href="{{ route('siswa.profile') }}">
          <i class="bi bi-person"></i><span>Profile Siswa</span>
        </a>
      </li>

      <li class="nav-heading">Nilai Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('nilai.index') ? '' : 'collapsed' }}" href="{{ route('nilai.index') }}">
          <i class="bi bi-star"></i><span>Rekap Nilai</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('nilai.raport.personal') ? '' : 'collapsed' }}" href="{{ route('nilai.raport.personal') }}">
          <i class="bi bi-star"></i><span>Cetak Raport</span>
        </a>
      </li>

      <li class="nav-heading">Kehadiran Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('kehadiran.rekap') ? '' : 'collapsed' }}" href="{{ route('kehadiran.rekap') }}">
          <i class="bi bi-star"></i><span>Rekap Kehadiran</span>
        </a>
      </li>

      <li class="nav-heading">Ekstrakurikuler Siswa</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('ekstrakurikuler.rekap') ? '' : 'collapsed' }}" href="{{ route('ekstrakurikuler.rekap') }}">
          <i class="bi bi-star"></i><span>Rekap Ekstrakurikuler</span>
        </a>
      </li>

      <li class="nav-heading">Informasi & Pengaturan</li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pengumuman.index') ? '' : 'collapsed' }}" href="{{ route('pengumuman.index') }}">
          <i class="bi bi-star"></i><span>Pengumuman</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('matapelajaran.jadwal') ? '' : 'collapsed' }}" href="{{ route('matapelajaran.jadwal') }}">
          <i class="bi bi-star"></i><span>Jadwal Mata Pelajaran</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('materipembelajaran.index') ? '' : 'collapsed' }}" href="{{ route('materipembelajaran.index') }}">
          <i class="bi bi-star"></i><span>Materi Pembelajaran</span>
        </a>
      </li>

    @endif

    {{-- Keluar --}}
    <li class="nav-heading">Akun</li>
    <li class="nav-item">
      <form method="POST" action="{{ route('logout') }}" id="sidebarLogout">
        @csrf
        <a class="nav-link collapsed" href="#" onclick="event.preventDefault(); document.getElementById('sidebarLogout').submit();">
          <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
        </a>
      </form>
    </li>

  </ul>

</aside>{{-- End Sidebar --}}

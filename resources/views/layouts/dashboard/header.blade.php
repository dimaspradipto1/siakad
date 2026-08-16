  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      @php
          $headerLogoUrl = ($schoolProfile && $schoolProfile->logo_sekolah) 
              ? asset($schoolProfile->logo_sekolah) 
              : asset('assets/img/logo.png');
      @endphp
      <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
        <img src="{{ $headerLogoUrl }}" alt="Logo" style="max-height: 26px; margin-right: 8px;">
        <span class="d-none d-lg-block fw-bold">SIAKAD</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    {{-- <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="GET" action="#">
        <input type="text" name="query" placeholder="Cari..." title="Masukkan kata kunci">
        <button type="submit" title="Cari"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar --> --}}

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle" href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon -->

        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">0</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              Tidak ada notifikasi baru
            </li>
          </ul>
        </li><!-- End Notification Nav -->

        @php
            $authUser = Auth::user();
            $canSwitchRole = $authUser && $authUser->roles === 'guru' && $authUser->isWaliKelasAktif();
            $currentActiveRole = $authUser?->activeRole();
            $isOrangTuaUser = $authUser && $authUser->roles === 'orang tua';
            $parentChildren = collect();
            $currentChild = null;
            if ($isOrangTuaUser) {
                $parentChildren = (new class { use \App\Traits\ResolvesStudentFromUser; public function list($u) { return $this->getChildrenForParent($u); } })->list($authUser);
                $activeChildId = session('selected_child_id');
                $currentChild = $parentChildren->firstWhere('id', (int)$activeChildId) ?? $parentChildren->first();
            }
        @endphp

        @if($isOrangTuaUser && $parentChildren->count() > 1)
        <li class="nav-item dropdown me-3">
          <a class="nav-link dropdown-toggle btn btn-light btn-sm px-3 py-1 d-flex align-items-center gap-2 border shadow-sm rounded-pill fw-semibold text-dark" href="#" data-bs-toggle="dropdown" style="font-size: 0.85rem; background: #f8f9fa;">
            <i class="bi bi-people-fill text-primary"></i>
            <span>{{ $currentChild?->nama_siswa ?? 'Pilih Anak' }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius: 12px; min-width: 240px;">
            <li class="dropdown-header text-start py-1 px-3">
              <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Pilih Data Anak :</span>
            </li>
            @foreach($parentChildren as $c)
              @php
                $cKelas = $c->pembagianKelas->first()?->kelas?->nama_kelas ?? ($c->kelas?->nama_kelas ?? '-');
                $isSelected = $currentChild && $currentChild->id === $c->id;
              @endphp
              <li>
                <a class="dropdown-item d-flex justify-content-between align-items-center py-2 px-3 {{ $isSelected ? 'active bg-primary text-white' : '' }}" href="{{ route('orangtua.select-child', ['id' => $c->id, 'redirect' => url()->current()]) }}">
                  <div>
                    <div class="fw-bold" style="font-size: 0.9rem;">{{ $c->nama_siswa }}</div>
                    <small class="{{ $isSelected ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.78rem;">Kelas {{ $cKelas }} (NISN: {{ $c->nisn }})</small>
                  </div>
                  @if($isSelected)
                    <i class="bi bi-check-circle-fill ms-2"></i>
                  @endif
                </a>
              </li>
            @endforeach
          </ul>
        </li>
        @endif

        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                  style="width:36px;height:36px;font-size:16px;font-weight:600;">
              {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </span>
            <span class="d-none d-md-block dropdown-toggle ps-2">
              {{ Auth::user()->name ?? 'Pengguna' }}
            </span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>{{ Auth::user()->name ?? 'Pengguna' }}</h6>
              <span class="text-capitalize">{{ $currentActiveRole ?: '-' }}</span>
            </li>
            <li><hr class="dropdown-divider"></li>

            @if ($canSwitchRole)
              <li>
                <form method="POST" action="{{ route('switch-role', $currentActiveRole === 'wali kelas' ? 'guru' : 'wali-kelas') }}" id="switchRoleForm">
                  @csrf
                  <a class="dropdown-item d-flex align-items-center" href="#"
                     onclick="event.preventDefault(); document.getElementById('switchRoleForm').submit();">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>
                      @if ($currentActiveRole === 'wali kelas')
                        Beralih ke Peran Guru
                      @else
                        Beralih ke Peran Wali Kelas
                      @endif
                    </span>
                  </a>
                </form>
              </li>
              <li>
                <form method="POST" action="{{ route('switch-role', 'reset') }}" id="resetRoleForm">
                  @csrf
                  <a class="dropdown-item d-flex align-items-center text-secondary" href="#"
                     onclick="event.preventDefault(); document.getElementById('resetRoleForm').submit();">
                    <i class="bi bi-grid-fill"></i>
                    <span>Pilih Peran Login...</span>
                  </a>
                </form>
              </li>
              <li><hr class="dropdown-divider"></li>
            @endif

            <li>
              <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <a class="dropdown-item d-flex align-items-center" href="#"
                   onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                  <i class="bi bi-box-arrow-right"></i>
                  <span>Keluar</span>
                </a>
              </form>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->
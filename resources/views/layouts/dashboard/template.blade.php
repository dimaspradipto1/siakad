<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  @php
      $tmplFaviconUrl = ($schoolProfile && $schoolProfile->logo_sekolah) 
          ? asset($schoolProfile->logo_sekolah) 
          : asset('assets/img/favicon.png');
      $tmplSchoolName = $schoolProfile->nama_sekolah ?? 'SD Negeri 007 Sekupang';
  @endphp
  <title>@yield('title', 'Dashboard') - SIAKAD {{ $tmplSchoolName }}</title>
  <meta content="Sistem Informasi Akademik {{ $tmplSchoolName }}" name="description">
  <meta content="SIAKAD, akademik, sekolah, {{ $tmplSchoolName }}" name="keywords">

  <!-- Favicons -->
  <link href="{{ $tmplFaviconUrl }}" rel="icon">
  <link href="{{ $tmplFaviconUrl }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  {{-- DataTables CSS --}}
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">

  {{-- SweetAlert2 CSS --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  {{-- Select2 CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

  <!-- Template Main CSS File -->
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

  <style>
    /* Override DataTables default alignments */
    table.dataTable th,
    table.dataTable td {
        text-align: left !important;
        vertical-align: middle !important;
    }

    /* Wrap table responsive container without breaking header borders */
    .table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        margin-bottom: 1rem;
    }

    .table-responsive table.dataTable {
        width: 100% !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    /* Custom scrollbar styling */
    .table-responsive::-webkit-scrollbar,
    .dataTables_wrapper::-webkit-scrollbar {
        height: 8px !important;
        display: block !important;
    }

    .table-responsive::-webkit-scrollbar-track,
    .dataTables_wrapper::-webkit-scrollbar-track {
        background: #f1f1f1 !important;
        border-radius: 4px !important;
    }

    .table-responsive::-webkit-scrollbar-thumb,
    .dataTables_wrapper::-webkit-scrollbar-thumb {
        background: #888888 !important;
        border-radius: 4px !important;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover,
    .dataTables_wrapper::-webkit-scrollbar-thumb:hover {
        background: #555555 !important;
    }
  </style>
</head>

<body>

  @unless ($hideNav ?? false)
    @include('layouts.dashboard.header')
    @include('layouts.dashboard.sidebar')
  @endunless

  <main id="main" class="main" @if ($hideNav ?? false) style="margin-left: 0 !important; padding: 30px 15px !important; min-height: 100vh; background-color: #f6f9ff;" @endif>
    @yield('content')
  </main><!-- End #main -->

  @unless ($hideNav ?? false)
    @include('layouts.dashboard.footer')
  @endunless


  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/chart.js/chart.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('assets/js/main.js') }}"></script>

  {{-- jQuery & DataTables --}}
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>

  <script>
      if (typeof $.fn.dataTable !== 'undefined') {
          $.extend(true, $.fn.dataTable.defaults, {
              autoWidth: false,
              responsive: false
          });
      }
  </script>

  {{-- SweetAlert2 JS --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  {{-- SweetAlert Flash Messages via realrashid/sweet-alert --}}
  @include('sweetalert::alert')

  {{-- Select2 JS --}}
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
      $(document).ready(function() {
          $('.select2').select2({
              theme: 'bootstrap-5'
          });
      });
  </script>

  @stack('script')
  

</body>

</html>

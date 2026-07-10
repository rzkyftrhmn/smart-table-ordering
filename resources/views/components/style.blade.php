<!-- FAVICON -->
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/brand/logoMeja1.png') }}" />

<!-- TITLE -->
<title>Admin - Caffee</title>

<!-- BOOTSTRAP CSS -->
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

<!-- STYLE CSS -->
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet"/>
<link href="{{ asset('assets/css/dark-style.css') }}" rel="stylesheet"/>
<link href="{{ asset('assets/css/skin-modes.css') }}" rel="stylesheet" />

<!-- SIDE-MENU CSS -->
<link href="{{ asset('assets/css/sidemenu.css') }}" rel="stylesheet" id="sidemenu-theme">

<!--C3 CHARTS CSS -->
<link href="{{ asset('assets/plugins/charts-c3/c3-chart.css') }}" rel="stylesheet"/>

<!-- P-scroll bar css-->
<link href="{{ asset('assets/plugins/p-scroll/perfect-scrollbar.css') }}" rel="stylesheet" />

<!--- FONT-ICONS CSS -->
<link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet"/>

<!-- SIDEBAR CSS -->
<link href="{{ asset('assets/plugins/sidebar/sidebar.css') }}" rel="stylesheet">

<!-- SELECT2 CSS -->
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet"/>

<!-- INTERNAL Data table css -->
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datatable/responsive.bootstrap5.css') }}" rel="stylesheet" />

<!-- COLOR SKIN CSS -->
<link id="theme" rel="stylesheet" type="text/css" media="all" href="{{ asset('assets/colors/color1.css') }}" />

<style>
/* style map location */
#map {
    position: relative;
    z-index: 0; 
}

#map .leaflet-pane,
#map .leaflet-top,
#map .leaflet-bottom {
    z-index: 1 !important;
}

#map .leaflet-control {
    z-index: 2 !important;
}

.tm-admin-page-shell,
.tm-admin-page-shell .row,
.tm-admin-page-shell [class*="col-"],
.tm-admin-page-shell .card,
.tm-admin-page-shell .card-body {
    min-width: 0;
    max-width: 100%;
}
.tm-page { padding: 2rem 1.5rem; max-width: 100%; overflow-x: hidden; }

/* App header alignment */
.app-header.header .container-fluid,
.app-header.header .d-flex {
    min-width: 0;
}
.app-header.header > .container-fluid > .d-flex {
    align-items: center;
    min-height: 64px;
}
.app-sidebar__toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
}
.header-right-icons {
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    min-height: 44px;
}
.header-right-icons .sto-notification {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.header-right-icons .sto-notification-toggle,
.header-right-icons .nav-link.icon,
.header-right-icons .navbar-toggler {
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    margin: 0;
    padding: 0;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: #fff;
    color: #334155;
    box-shadow: 0 6px 14px rgba(15, 23, 42, .06);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.header-right-icons .sto-notification-toggle:hover,
.header-right-icons .nav-link.icon:hover,
.header-right-icons .navbar-toggler:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
}
.header-right-icons .header-settings {
    align-items: center;
}
.header-right-icons .nav-link.icon i,
.header-right-icons .navbar-toggler-icon {
    line-height: 1;
}

/* Topbar */
.tm-topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; }
.tm-topbar-title { display: flex; align-items: center; gap: 10px; font-size: 1.1rem; font-weight: 600; color: #111827; }
.tm-icon-wrap { width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; display: flex; align-items: center; justify-content: center; }
.tm-icon-wrap i { font-size: 18px; color: #2563eb; }
.tm-topbar-sub { font-size: 13px; color: #6b7280; margin-top: 4px; margin-left: 46px; }
.tm-admin-badge { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; color: #374151; padding: 6px 14px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; }
.tm-admin-badge i { color: #2563eb; font-size: 14px; }

/* Stat row */
.tm-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 1.75rem; }
.tm-stat-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; }
.tm-stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 6px; }
.tm-stat-value { font-size: 22px; font-weight: 700; color: #111827; }
.tm-stat-value.tm-success { color: #16a34a; }
.tm-stat-sub { font-size: 12px; color: #9ca3af; margin-top: 2px; }

/* Layout */
.tm-layout { display: grid; grid-template-columns: minmax(240px, 280px) minmax(0, 1fr); gap: 1.5rem; align-items: start; }

/* Left panel */
.tm-panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; }
.tm-panel-title { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 1.25rem; }
.tm-panel-title i { color: #2563eb; }

.tm-field-wrap { margin-bottom: 1.25rem; }
.tm-field-label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: 6px; }
.tm-input { width: 100%; height: 38px; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px; font-size: 14px; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s; }
.tm-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.tm-input::placeholder { color: #9ca3af; }
.tm-input-error { border-color: #dc2626 !important; }
.tm-error-msg { font-size: 12px; color: #dc2626; margin-top: 6px; display: flex; align-items: center; gap: 4px; }

.sto-child-nav { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
.sto-breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: #6b7280; }
.sto-breadcrumb li { display: inline-flex; align-items: center; gap: 8px; min-width: 0; }
.sto-breadcrumb li + li::before { content: "/"; color: #cbd5e1; font-weight: 700; }
.sto-breadcrumb a { color: #2563eb; font-weight: 700; text-decoration: none; }
.sto-breadcrumb a:hover { color: #1d4ed8; text-decoration: none; }
.sto-breadcrumb .active { color: #111827; font-weight: 700; }
.sto-page-back { min-height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1e40af !important; border-radius: 8px; padding: 0 14px; font-size: 13px; font-weight: 700; box-shadow: 0 6px 14px rgba(37,99,235,.08); }
.sto-page-back:hover { background: #dbeafe; border-color: #93c5fd; color: #1d4ed8 !important; text-decoration: none; }
.sto-header-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }

.tm-btn-primary { width: 100%; height: 38px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .15s; }
.tm-btn-primary:hover { background: #1d4ed8; }
.tm-btn-primary:active { transform: scale(.98); }

.tm-info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: .75rem 1rem; margin-top: 1.25rem; font-size: 12px; color: #1e40af; line-height: 1.5; display: flex; gap: 6px; align-items: flex-start; }
.tm-info-box i { flex-shrink: 0; margin-top: 1px; }

/* Right col */
.tm-right-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.tm-right-title { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #111827; }
.tm-right-title i { color: #6b7280; }
.tm-search-wrap { position: relative; }
.tm-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #9ca3af; }
.tm-search-input { height: 34px; width: 200px; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px 0 32px; font-size: 13px; color: #111827; outline: none; }
.tm-search-input:focus { border-color: #2563eb; }

/* Cards grid */
.tm-right-col { min-width: 0; }
.tm-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; min-width: 0; }

.tm-table-card { min-width: 0; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; gap: 12px; transition: border-color .15s, box-shadow .15s; }
.tm-table-card:hover { border-color: #93c5fd; box-shadow: 0 4px 12px rgba(37,99,235,.08); }

.tm-card-header { display: flex; justify-content: space-between; align-items: center; }
.tm-card-name { min-width: 0; font-size: 14px; font-weight: 600; color: #111827; overflow-wrap: anywhere; }
.tm-status-dot { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 500; color: #16a34a; }
.tm-dot { width: 6px; height: 6px; border-radius: 50%; background: #16a34a; }

.tm-qr-area { width: 100%; max-width: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.tm-qr-area svg { display: block; max-width: 100%; height: auto; }

.tm-card-footer { display: flex; justify-content: space-around; align-items: center; gap: 6px; padding-top: 10px; border-top: 1px solid #f3f4f6; }
.tm-action-btn { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 500; color: #374151; background: none; border: none; cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: background .12s, color .12s; }
.tm-action-btn:hover { background: #f3f4f6; color: #111827; }
.tm-action-btn.tm-action-danger { color: #991b1b; }
.tm-action-btn.tm-action-danger:hover { background: #fee2e2; color: #991b1b; }
.tm-vr { width: 1px; height: 14px; background: #e5e7eb; }

@media (max-width: 991.98px) {
    .tm-page { padding: 1.25rem 1rem; }
    .tm-layout { grid-template-columns: 1fr; }
    .tm-panel { width: 100%; }
}

@media (max-width: 575.98px) {
    .tm-admin-page-shell { padding-left: 8px !important; padding-right: 8px !important; }
    .tm-admin-page-shell > .row { margin-left: 0; margin-right: 0; }
    .tm-admin-page-shell > .row > [class*="col-"] { padding-left: 0; padding-right: 0; }
    .tm-admin-page-shell .card { margin-top: 1rem !important; }
    .tm-admin-page-shell .card-header { padding-left: 1rem; padding-right: 1rem; }
    .tm-admin-page-shell .card-body { padding: 1rem; overflow: hidden; }
    .tm-page { padding: .75rem 0; }
    .tm-stats-row { grid-template-columns: 1fr; gap: 10px; margin-bottom: 1rem; }
    .tm-stat-card { padding: .85rem 1rem; }
    .tm-layout { gap: 1rem; }
    .tm-panel { padding: 1rem; border-radius: 10px; }
    .tm-right-header { align-items: flex-start; }
    .tm-cards-grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }
    .tm-table-card { padding: 1rem; border-radius: 10px; }
    .tm-card-header { gap: 8px; flex-wrap: wrap; }
    .tm-status-dot { flex-shrink: 0; margin-left: auto; white-space: nowrap; }
    .tm-card-footer { justify-content: stretch; }
    .tm-action-btn { flex: 1 1 0; justify-content: center; min-height: 34px; }
    .sto-child-nav { align-items: flex-start; flex-direction: column; }
    .sto-page-back { width: 100%; }
    .sto-header-actions { width: 100%; margin-left: 0; }
    .sto-header-actions .sto-page-back { width: 100%; }
}
</style>

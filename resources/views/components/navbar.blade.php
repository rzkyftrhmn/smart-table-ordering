@php
    $currentUser = auth()->user();
    $sidebarUsername = $currentUser?->username ?: ($currentUser?->name ?: 'Staf');
@endphp

<!-- navbar -->
<div class="app-header header">
    <div class="container-fluid">
        <div class="d-flex">
            <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="#"></a><!-- sidebar-toggle-->
            <a class="header-brand1 d-flex d-md-none" href="index.html">
                <img src="{{ asset('assets/images/brand/logoMeja1.png') }}" class="header-brand-img light-logo" alt="logo">
                <img src="{{ asset('assets/images/brand/logoMejaTerakhir1.png') }}" class="header-brand-img light-logo1" alt="logo">
            </a>
            <div class="d-flex order-lg-2 ms-auto header-right-icons">
                @include('components.notification-bell')
                
                <button class="navbar-toggler navresponsive-toggler d-md-none ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon fe fe-more-vertical text-dark"></span>
                </button>
                
                <!-- SIDE-MENU -->
                <div class="dropdown d-none d-md-flex header-settings">
                    <a href="#" class="nav-link icon " data-bs-toggle="sidebar-right" data-target=".sidebar-right">
                        <i class="fe fe-menu"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end navbar -->

<!-- mobile -->
<div class="mb-1 navbar navbar-expand-lg  responsive-navbar navbar-dark d-md-none bg-white">
    <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
        <div class="d-flex order-lg-2 ms-auto">

            <!-- SIDE-MENU -->
            <div class="dropdown d-md-flex header-settings">
                <a href="#" class="nav-link icon " data-bs-toggle="sidebar-right" data-target=".sidebar-right">
                    <i class="fe fe-menu"></i>
                </a>
            </div>

        </div>
    </div>
</div>
<!-- end mobile -->

<!-- Sidebar-right -->
<div class="sidebar sidebar-right sidebar-animate">
    <div class="panel panel-primary card mb-0 shadow-none border-0">
        <div class="tab-menu-heading border-0 d-flex p-3">
            <div class="card-options ms-auto">
                <a href="#" class="sidebar-icon text-end float-end me-1" data-bs-toggle="sidebar-right" data-target=".sidebar-right"><i class="fe fe-x text-white"></i></a>
            </div>
        </div>
        <div class="panel-body tabs-menu-body latest-tasks p-0 border-0">
            <div class="tab-content">
                <div class="tab-pane active" id="side1">
                    <div class="card-body text-center">
                        <div class="dropdown user-pro-body">
                            <div class="">
                                <div class="avatar avatar-xl brround mx-auto text-center bg-primary text-white d-flex align-items-center justify-content-center">
                                    <i class="fe fe-user tx-30"></i>
                                </div>
                                <span class="avatar-status profile-status bg-green"></span>
                            </div>
                            <div class="user-info mg-t-20">
                                <h6 class="fw-semibold  mt-2 mb-0">{{ $sidebarUsername }}</h6>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mb-0">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex border-bottom bg-transparent border-0 w-100 text-start">
                            <div class="d-flex"><i class="fe fe-power me-3 tx-20 text-muted"></i>
                                <div class="pt-1">
                                    <h6 class="mb-0">Logout</h6>
                                    <p class="tx-12 mb-0 text-muted">Keluar dari akun</p>
                                </div>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end Sidebar-right-->

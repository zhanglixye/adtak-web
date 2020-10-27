<header class="main-header">
    <a href="/" class="logo">
        <span class="logo-mini"><b>A</b>M</span>
        <span class="logo-lg"><b>{{ config('app.name') }}</span>
    </a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!--
                <li class="dropdown companies-menu">
                    {{-- 1企業の場合と複数で表示分岐 --}}
                    <a class="">
                        <span></span>
                    </a>
                    <a class="display-company-wrap">
                        <select id="display-company" class="form-control">
                            <option selected>全ての企業</option>
                            <option></option>
                        </select>
                    </a>
                </li>
                -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{!! isset(Auth::user()->icon) ?  Auth::user()->icon : '/images/dummy_icon.png' !!}" class="user-image" alt="{{ Auth::user()->name ?? '' }}">
                        <span class="hidden-xs">{{ Auth::user()->name ?? '' }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <ul class="menu">
                                <li>
                                    <a href="#">プロフィール</a>
                                </li>
                                <li>
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">ログアウト</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>

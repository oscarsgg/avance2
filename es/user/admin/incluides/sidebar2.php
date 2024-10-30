<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Inter", sans-serif;
            box-sizing: border-box;
        }
        
        body {
            background-color: #efeff6;
        }
        
        .container {
            width: 100%;
            grid-template-columns: 1fr;
        }
        
        .right {
            background-color: #4CAF50;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
        }
        
        .left {
            background-color: white;
            display: flex;
            justify-content: center;
            width: 100%;
            align-items: center;
            color: white;
            font-size: 24px;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px; /* Ajustado para dejar espacio para el header */
            width: 330px;
            height: calc(100vh - 60px); /* Ajustado para considerar el header */
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: #000;
            padding: 24px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar .head {
            display: flex;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f6f6f6;
        }
        
        .user-img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .user-img img {
            width: 100%;
            object-fit: cover;
        }
        
        .user-details .title,
        .menu .title {
            font-size: 10px;
            font-weight: 500;
            color: white;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .user-details .name {
            font-size: 14px;
            font-weight: 500;
            color: white;
        }
        
        .nav {
            flex: 1;
        }
        
        .menu ul li {
            position: relative;
            list-style: none;
            margin-bottom: 5px;
        }
        
        .menu ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: white;
            text-decoration: none;
            padding: 12px 8px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .menu ul li > a:hover,
        .menu ul li.active > a {
            color: #000;
            background-color: #f6f6f6;
        }
        
        .menu ul li .icon {
            font-size: 20px;
        }
        
        .menu ul li .text {
            flex: 1;
        }
        
        .menu ul li .arrow {
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .menu ul li.active .arrow {
            transform: rotate(180deg);
        }
        
        .menu .sub-menu {
            display: none;
            margin-left: 20px;
            padding-left: 20px;
            padding-top: 5px;
            border-left: 1px solid #f6f6f6;
        }
        
        .menu .sub-menu li a {
            padding: 10px 8px;
            font-size: 12px;
        }
        
        .menu:not(:last-child) {
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #fff;
        }
        
        .sidebar.active {
            width: 92px;
        }
        
        .sidebar.active .user-details {
            display: none;
        }
        
        .sidebar.active .menu .title {
            text-align: center;
        }
        
        .sidebar.active .menu ul li .arrow {
            display: none;
        }
        
        .sidebar.active .menu > ul > li > a {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar.active .menu > ul > li > a .text {
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px;
            border-radius: 4px;
            color: white;
            background-color: black;
            opacity: 0;
            visibility: hidden;
        }
        
        .sidebar.active .menu > ul > li > a .text::after {
            content: "";
            position: absolute;
            left: -5px;
            top: 20%;
            width: 20px;
            height: 20px;
            border-radius: 2px;
            background-color: black;
            transform: rotate(45deg);
            z-index: -1;
        }
        
        .sidebar.active .menu > ul > li > a:hover .text {
            left: 50px;
            opacity: 1;
            visibility: visible;
        }
        
        .sidebar.active .menu .sub-menu {
            position: absolute;
            top: 0;
            left: 50px;
            border-radius: 20px;
            padding: 10px 20px;
            border: 1px solid #000;
            background-color: black;
            box-shadow: 0px 10px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* RIGHT SIDE */
        .containeres {
            height: 100vh;
            width: 100%;
            background-color: #f6f6f6;
            display: flex;
            flex-direction: column;
        }
        
        .content {
            margin-left: 350px;
            margin-top: 60px; /* Ajustado para dejar espacio para el header */
        }
        
        .squarecontent {
            background-color: white;
            width: 90%;
            height: 430px;
            margin-top: 40px;
            border-radius: 40px;
        }
        
        /* Header styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #000;
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 1001;
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin-left: 20px;
            text-decoration: none;
        }
        
        .menu-btn {
            width: 30px;
            height: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            cursor: pointer;
        }
        
        .menu-btn span {
            width: 100%;
            height: 2px;
            background-color: #fff;
            transition: all 0.3s;
        }
        
        .sidebar.active + .header .menu-btn span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .sidebar.active + .header .menu-btn span:nth-child(2) {
            opacity: 0;
        }
        
        .sidebar.active + .header .menu-btn span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }
    </style>
</head>
<body>
    <section class="sidebarfool">
        <div class="container">
            <!-- Header -->
            <header class="header">
                <div class="menu-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <a href="#" class="header-title">Nombre de la Empresa</a>
            </header>
            
            <div class="sidebar">
                <div class="head">
                    <div class="user-img">
                        <img src="img/user.jpg" alt="No photo">
                    </div>
                    <div class="user-details">
                        <p class="title">Experto en almejas</p>
                        <p class="name">Almejandro</p>
                    </div>
                </div>
                <div class="nav">
                    <div class="menu">
                        <p class="title">Main</p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-house-simple"></i>
                                    <span class="text">Almejaboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-user"></i>
                                    <span class="text">Almejas</span>
                                    <i class="arrow ph-bold ph-caret-down"></i>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">
                                            <span class="text">Ver almejas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="text">Administrar almejas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-chart-bar"></i>
                                    <span class="text">Admin</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-user"></i>
                                    <span class="text">Almejalans</span>
                                    <i class="arrow ph-bold ph-caret-down"></i>
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">
                                            <span class="text">Ver alemjas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="text">Administrar almejas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="menu">
                        <p class="title">Ajustes</p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Almefiguración</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu">
                        <p class="title"></p>
                        <ul>
                            <li class="">
                                <a href="#">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Cerrar sesión</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Your main content goes here -->
            <div class="content">
                <!-- Add your main content here -->
            </div>
        </div>
    </section>

    <script>
        $(".menu > ul > li").click(function(e) {
            $(this).siblings().removeClass("active");
            $(this).toggleClass("active");
            $(this).find("ul").slideToggle();
            $(this).siblings().find("ul").slideUp();
        });

        $(".menu-btn").click(function() {
            $(".sidebar").toggleClass("active");
            $(".content").toggleClass("expanded");
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('.sidebar, .menu > ul > li, .menu-btn').length) {
                $(".menu > ul > li").removeClass("active");
                $(".menu > ul > li ul").slideUp();
            }
        });
    </script>
</body>
</html>
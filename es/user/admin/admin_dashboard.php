<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vacantes</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Barra lateral -->
    <section class="sidebarfool">
        <div class="container">
            <div class="sidebar active">
                <div class="menu-btn">
                    <i class="ph-bold ph-caret-left"></i>
                </div>
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
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-house-simple"></i>
                                    <span class="text">Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-user"></i>
                                    <span class="text">Admin</span>
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
                        </ul>
                    </div>
                    <div class="menu">
                        <p class="title">Ajustes</p>
                        <ul>
                            <li>
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
                            <li>
                                <a href="#">
                                    <i class="icon ph-bold ph-gear"></i>
                                    <span class="text">Cerrar sesión</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido principal -->
    <div class="main-content">
        <h1>Gestión de Vacantes</h1>
        <button id="addVacancyBtn" class="btn-add">Agregar Vacante</button>
        <div id="addVacancyModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Agregar Nueva Vacante</h2>
                
                <form id="vacancyForm">
                    <div class="form-group">
                        <label for="vacancyTitle">Título de la Vacante:</label>
                        <input type="text" id="vacancyTitle" name="vacancyTitle" class="input-form" required>
                    </div>
                    <div class="form-group">
                        <label for="vacancyStatus">Estado:</label>
                        <select id="vacancyStatus" name="vacancyStatus" class="input-form">
                            <option value="Abierta">Abierta</option>
                            <option value="Cerrada">Cerrada</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-add" id="saveButton">Guardar Vacante</button>
                </form>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>TITULO</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody id="vacantesTableBody">
                <!-- Aquí se agregarán dinámicamente las filas -->
            </tbody>
        </table>

        <div class="pagination" id="paginationControls">
            <button class="pagination-btn" id="prevPageBtn"><< Prev</button>
            <!-- Aquí se agregarán los botones de página dinámicamente -->
            <button class="pagination-btn" id="nextPageBtn">Next >></button>
        </div>
    </div>

    <script src="scripted.js"></script> <!-- Enlace al archivo JavaScript -->
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="css/prospectProfile.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Enlaza jQuery -->
    <title>Document</title>


</head>

<main>
    <div class="containeres">
        <main>
            <div class="profile-header">
                <img src="img/soto.jpeg" alt="Foto de perfil" class="profile-image" id="profile-image">
                <div class="profile-info">
                    <div>
                        <h1><span class="editable" id="name">Oscar Gael Soto García</span></h1>
                        <p class="editable" id="job-title">Contador Publico</p>
                        <p><span class="editable" id="work-hours">01/01/2005</span> · <span class="editable"
                                id="email">gael.garcia@gmail.com</span></p>
                    </div>
                </div>
            </div>

            <section class="tasks-section">
                <h2>Información</h2>
                <div class="tabs">
                    <div class="tab active">Habilidades</div>
                </div>
                <div class="task-list" id="task-list">
                    <div class="task-">
                        <section>
                            <p class="editable multi-line" id="about">Normalmente trabajo en turno matutino, cualquier
                                duda no dudes en contactarme!</p>
                        </section>
                    </div>
                </div>
            </section>
        </main>

        <aside class="sbar">
            <section>
                <h2>Acerca de mí</h2>
                <p class="editable multi-line" id="about-sidebar">Normalmente trabajo en turno matutino, cualquier duda
                    no dudes en contactarme!</p>
            </section>
            <section>
                <h2>Grados</h2>
                <div class="teams-list" id="teams-list">
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Licenciatura en contaduría</span>
                    </div>
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Maestría en finanzas</span>
                    </div>
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Congresista en Palacio NF</span>
                    </div>
                    <div class="team-item">
                        <div class="team-icon"></div>
                        <span class="editable">Doctorado en finanzas de valores</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <button id="edit-all" class="edit-button" style="position: fixed; bottom: 20px; right: 20px;">Editar Todo</button>
</main>

</html>
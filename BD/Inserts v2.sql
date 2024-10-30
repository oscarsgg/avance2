create database Outsourcing;

CREATE TABLE Rol (
  codigo VARCHAR(3) PRIMARY KEY,
  nombre VARCHAR(25) NOT NULL
);

CREATE TABLE Usuario (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  correo VARCHAR(25) NOT NULL,
  contrasenia VARCHAR(25) NOT NULL,
  rol VARCHAR(3) NOT NULL,
  FOREIGN KEY (rol) REFERENCES Rol(codigo)
);

CREATE TABLE Prospecto (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  primerApellido VARCHAR(30) NOT NULL,
  segundoApellido VARCHAR(30),
  resumen VARCHAR(300),
  fechaNacimiento DATE NOT NULL,
  numTel VARCHAR(15) NOT NULL,
  usuario INT NOT NULL,
  FOREIGN KEY (usuario) REFERENCES Usuario(numero)
);

CREATE TABLE Empresa (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  ciudad VARCHAR(30) NOT NULL,
  calle VARCHAR(30) NOT NULL,
  numeroCalle INT NOT NULL,
  colonia VARCHAR(30) NOT NULL,
  codigoPostal INT NOT NULL,
  nombreCont VARCHAR(30) NOT NULL,
  primerApellidoCont VARCHAR(30) NOT NULL,
  segundoApellidoCont VARCHAR(30),
  numTel VARCHAR(15) NOT NULL,
  usuario INT NOT NULL,
  FOREIGN KEY (usuario) REFERENCES Usuario(numero)
);

CREATE TABLE Tipo_Contrato (
  codigo VARCHAR(5) PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL,
  descripcion VARCHAR(150) NOT NULL
);

CREATE TABLE Vacante (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  titulo VARCHAR(30) NOT NULL,
  descripcion VARCHAR(250) NOT NULL,
  salario INT,
  es_directo BOOLEAN NOT NULL,
  cantEmpleados INT NOT NULL,
  fechaInicio DATE NOT NULL,
  fechaCierre DATE NOT NULL,
  diasRestantes INT NOT NULL,
  estado BOOLEAN NOT NULL,
  tipo_contrato VARCHAR(5) NOT NULL,
  empresa INT NOT NULL,
  FOREIGN KEY (tipo_contrato) REFERENCES Tipo_Contrato(codigo),
  FOREIGN KEY (empresa) REFERENCES Empresa(numero)
);

CREATE TABLE Estatus_Solicitud (
    codigo VARCHAR(5) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE Requerimiento (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  descripcion VARCHAR(50) NOT NULL,
  vacante INT NOT NULL,
  FOREIGN KEY (vacante) REFERENCES Vacante(numero)
);

CREATE TABLE Contrato (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  fechaInicio DATE NOT NULL,
  fechaCierre DATE NOT NULL,
  prospecto INT NOT NULL,
  vacante INT NOT NULL,
  tipo_contrato VARCHAR(5) NOT NULL,
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (vacante) REFERENCES Vacante(numero),
  FOREIGN KEY (tipo_contrato) REFERENCES Tipo_Contrato(codigo)
);

CREATE TABLE Carrera (
  codigo VARCHAR(5) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Experiencia (
    numero INT(8) PRIMARY KEY AUTO_INCREMENT,
    puesto VARCHAR(30) NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    nombreEmpresa VARCHAR(40),
    fechaInicio DATE NOT NULL,
    fechaFin DATE NOT NULL,
    duracionMeses INT(8) NOT NULL,
    prospecto INT(8) NOT NULL,
    FOREIGN KEY (prospecto) REFERENCES Prospecto(numero)
);

CREATE TABLE Responsabilidades (
    numero INT(8) PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(150) NOT NULL,
    experiencia INT(8) NOT NULL,
    FOREIGN KEY (experiencia) REFERENCES Experiencia(numero)
);

CREATE TABLE Plan_suscripcion (
  codigo VARCHAR(5) PRIMARY KEY,
  duracion INT NOT NULL,
  precio DECIMAL(5, 2) NOT NULL,
  precioMensual DECIMAL(5, 2) NOT NULL
);

CREATE TABLE Membresia (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  fechaVencimiento DATE NOT NULL,
  estatus BOOLEAN NOT NULL,
  empresa INT NOT NULL,
  plan_suscripcion VARCHAR(5) NOT NULL,
  FOREIGN KEY (empresa) REFERENCES Empresa(numero),
  FOREIGN KEY (plan_suscripcion) REFERENCES Plan_suscripcion(codigo)
);

CREATE TABLE Renovacion (
    numero INT(8) PRIMARY KEY AUTO_INCREMENT,
    fechaRenovacion DATE NOT NULL,
    membresia INT(8) NOT NULL,
    FOREIGN KEY (membresia) REFERENCES Membresia(numero)
);

CREATE TABLE Solicitud (
    prospecto INT(8) NOT NULL,
    vacante INT(8) NOT NULL,
    estatus VARCHAR(5) NOT NULL,
    es_cancelada BOOLEAN NOT NULL,
    PRIMARY KEY (prospecto, vacante),
    FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
    FOREIGN KEY (vacante) REFERENCES Vacante(numero),
    FOREIGN KEY (estatus) REFERENCES Estatus_Solicitud(codigo)
);

CREATE TABLE Carreras_estudiadas (
  prospecto INT NOT NULL,
  carrera VARCHAR(30) NOT NULL,
  anioConcluido INT,
  PRIMARY KEY (prospecto, carrera),
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (carrera) REFERENCES Carrera(codigo)
);

CREATE TABLE Carreras_solicitadas (
  vacante INT NOT NULL,
  carrera VARCHAR(30) NOT NULL,
  PRIMARY KEY (vacante, carrera),
  FOREIGN KEY (vacante) REFERENCES Vacante(numero),
  FOREIGN KEY (carrera) REFERENCES Carrera(codigo)
);


INSERT INTO Rol (codigo, nombre) VALUES
('ADM', 'Administrador'),
('EMP', 'Empresa'),
('PRO', 'Prospecto');

INSERT INTO Usuario (correo, contrasenia, rol) VALUES
('admin@gmail.com', '12345', 'ADM'),
('empresa@gmail.com', '12345', 'EMP'),
('prospecto@gmail.com', '12345', 'PRO'),
('empresa2@gmail.com', '12345', 'EMP'),
('prospecto2@gmail.com', '12345', 'PRO');

INSERT INTO Prospecto (nombre, primerApellido, segundoApellido, fechaNacimiento, usuario) VALUES
('Juan', 'Pérez', 'González', '1990-01-01', 3),
('María', 'García', 'Rodríguez', '1992-02-02', 5);

INSERT INTO Empresa (nombre, ciudad, calle, numeroCalle, colonia, codigoPostal, nombreCont, primerApellidoCont, segundoApellidoCont, usuario) VALUES
('BitLab', 'Tijuana', 'Avenida Independencia', 123, 'Centro', 22000, 'Josue Isaac', 'Lara', 'Lopez', 2),
('Microsoft', 'Tijuana', 'Calle 5 de Mayo', 456, 'Zona Río', 22400, 'Ana', 'Martínez', 'López', 4);

INSERT INTO Tipo_Contrato (codigo, nombre, descripcion) VALUES
('TC01', 'Contrato indefinido', 'Contrato sin plazo fijo, puede ser renovado o terminado en cualquier momento'),
('TC02', 'Contrato definido (plazo fijo)', 'Contrato con un plazo fijo de duración, puede ser renovado o terminado al final del plazo'),
('TC03', 'Contrato por obra o servicio determinado', 'Contrato para realizar una obra o servicio específico, se termina cuando se completa la obra o servicio'),
('TC04', 'Contrato por proyecto', 'Contrato para realizar un proyecto específico, se termina cuando se completa el proyecto');

INSERT INTO Vacante (titulo, descripcion, salario, es_directo, cantEmpleados, fechaInicio, fechaCierre, diasRestantes, estado, tipo_contrato, empresa) VALUES
('Desarrollador de software', 'Se busca un desarrollador de software con experiencia en Java y Spring Boot para trabajar en un proyecto de desarrollo de software', 50000, TRUE, 1, '2024-01-01', '2024-01-31', 30, TRUE, 'TC04', 1),
('Ingeniero de redes', 'Se busca un ingeniero de redes con experiencia en configuración de routers y switches para trabajar en un proyecto de implementación de redes', 60000, FALSE, 2, '2024-02-01', '2024-02-28', 28, TRUE, 'TC03', 2),
('Analista de datos', 'Se busca un analista de datos con experiencia en SQL y Tableau para trabajar en un proyecto de análisis de datos', 40000, TRUE, 1, '2024-03-01', '2024-03-31', 31, TRUE, 'TC02', 1);

INSERT INTO Requerimiento (descripcion, vacante) VALUES
('Experiencia en desarrollo de software con Java y Spring Boot', 1),
('Conocimientos de bases de datos relacionales y no relacionales', 1),
('Experiencia en trabajo en equipo y colaboración con otros desarrolladores', 1),
('Experiencia en configuración de routers y switches', 2),
('Conocimientos de protocolos de red y seguridad', 2),
('Experiencia en trabajo en equipo y colaboración con otros ingenieros', 2),
('Experiencia en análisis de datos con SQL y Tableau', 3),
('Conocimientos de estadística y matemáticas', 3),
('Experiencia en trabajo en equipo y colaboración con otros analistas', 3);

INSERT INTO Contrato (fechaInicio, fechaCierre, prospecto, vacante, tipo_contrato) VALUES
('2024-04-01', '2024-06-30', 1, 1, 'TC04');

INSERT INTO Carrera (codigo, nombre) VALUES
('INGC', 'Ingeniería en Computación'),
('INGE', 'Ingeniería en Electrónica'),
('INGM', 'Ingeniería en Mecánica'),
('INGQ', 'Ingeniería en Química'),
('LICF', 'Licenciatura en Física'),
('LICB', 'Licenciatura en Biología'),
('LICP', 'Licenciatura en Psicología'),
('LICD', 'Licenciatura en Derecho'),
('INGA', 'Ingeniería en Administración'),
('INGF', 'Ingeniería en Finanzas'),
('INGR', 'Ingeniería en Recursos Humanos'),
('INGT', 'Ingeniería en Turismo'),
('LICL', 'Licenciatura en Lengua y Literatura'),
('LICN', 'Licenciatura en Nutrición'),
('LICM', 'Licenciatura en Medicina'),
('INGN', 'Ingeniería en Naval'),
('INGG', 'Ingeniería en Geología'),
('INDS', 'Ingeniería en Desarrollo de Software'),
('INGP', 'Ingeniería en Petróleo');

INSERT INTO Plan_suscripcion (codigo, duracion, precio, precioMensual) VALUES
('BAS01', 1, 999.99, 999.99),
('PRE01', 6, 5499.99, 916.66),
('PRO01', 12, 9999.99, 833.33);


INSERT INTO Membresia (fechaVencimiento, estatus, empresa, plan_suscripcion) VALUES
('2024-12-31', TRUE, 1, 'PRO01'),
('2025-06-30', TRUE, 2, 'PRE01');

INSERT INTO Carreras_estudiadas (prospecto, carrera, anioConcluido) VALUES
(1, 'INGC', 2020),
(2, 'LICM', 2019);

INSERT INTO Carreras_solicitadas (vacante, carrera) VALUES
(1, 'INGC'),
(1, 'LICM'),
(2, 'INGE'),
(3, 'LICF'),
(3, 'INGC');

INSERT INTO Estatus_Solicitud (codigo, nombre) VALUES
('PEND', 'Pendiente'),
('APRO', 'Aprobada'),
('RECH', 'Rechazada'),
('PFRM', 'Por firmar'),
('CERR', 'Cerrada');

-- Experiencias para Prospecto 1
INSERT INTO Experiencia (puesto, descripcion, nombreEmpresa, fechaInicio, fechaFin, duracionMeses, prospecto) VALUES
('Desarrollador', 'Desarrollo de aplicaciones web.', 'Tech Solutions', '2021-01-01', '2022-01-01', 12, 1),
('Analista de Sistemas', 'Análisis de requerimientos y diseño de sistemas.', 'Innovatech', '2020-01-01', '2020-12-31', 12, 1);

-- Responsabilidades para Prospecto 1
INSERT INTO Responsabilidades (descripcion, experiencia) VALUES
('Desarrollar código eficiente y limpio.', 1),
('Colaborar con el equipo de diseño.', 1),
('Realizar pruebas de calidad del software.', 1),
('Recopilar y analizar requisitos del cliente.', 2),
('Documentar procesos y sistemas.', 2);

-- Experiencias para Prospecto 2
INSERT INTO Experiencia (puesto, descripcion, nombreEmpresa, fechaInicio, fechaFin, duracionMeses, prospecto) VALUES
('Gerente de Proyectos', 'Gestión y supervisión de proyectos de IT.', 'Global Tech', '2019-05-01', '2021-05-01', 24, 2),
('Consultor IT', 'Asesoría en soluciones tecnológicas.', 'Tech Advisors', '2018-01-01', '2019-04-30', 16, 2);

-- Responsabilidades para Prospecto 2
INSERT INTO Responsabilidades (descripcion, experiencia) VALUES
('Planificar y ejecutar proyectos de IT.', 3),
('Coordinar equipos de trabajo.', 3),
('Presentar informes de avance a la dirección.', 3),
('Evaluar nuevas tecnologías para su implementación.', 4),
('Asesorar a clientes en la adopción de tecnologías.', 4);

INSERT INTO Renovacion (fechaRenovacion, membresia) VALUES
('2023-01-15', 1),
('2023-06-20', 2);

INSERT INTO Solicitud (prospecto, vacante, estatus, es_cancelada) VALUES
(1, 1, 'PEND', FALSE),
(1, 2, 'PEND', FALSE),
(2, 1, 'PEND', FALSE),
(2, 2, 'RECH', TRUE);

select * from empresa

-- Insertar automáticamente una membresía para la empresa recién registrada

DELIMITER $$
CREATE TRIGGER after_empresa_insert
AFTER INSERT ON Empresa
FOR EACH ROW
BEGIN
  INSERT INTO Membresia (fechaVencimiento, estatus, empresa, plan_suscripcion)
  VALUES (
    (CURRENT_DATE - INTERVAL 1 DAY), 
    FALSE,                            
    NEW.numero,                       
    'BAS01'                           
  );
END$$

DELIMITER ;

INSERT INTO Empresa (nombre, ciudad, calle, numeroCalle, colonia, codigoPostal, nombreCont, primerApellidoCont, segundoApellidoCont, usuario) VALUES
('Uber', 'Tijuana', 'Avenida Independencia', 123, 'Centro', 22000, 'Gabo', 'Guzman', 'Junior', 1),

DELIMITER $$

CREATE PROCEDURE obtenerDatosEmpresa(IN empresaId INT)
BEGIN
    -- Declaración de variables
    DECLARE vacantes_activas INTEGER;
    DECLARE total_candidatos INTEGER;
    DECLARE aplicaciones_por_revisar INTEGER;

    -- Asignar valores a las variables
    -- Contar las vacantes activas
    SET vacantes_activas = (
        SELECT COUNT(*)
        FROM Vacante
        WHERE empresa = empresaId AND fechaCierre > CURDATE()
    );

    -- Contar el total de candidatos únicos que han aplicado a las vacantes de la empresa
    SET total_candidatos = (
        SELECT COUNT(DISTINCT Solicitud.prospecto)
        FROM Solicitud
        INNER JOIN Vacante ON Solicitud.vacante = Vacante.numero
        WHERE Vacante.empresa = empresaId
    );

    -- Contar las aplicaciones por revisar (estatus 'PEND' y no canceladas)
    SET aplicaciones_por_revisar = (
        SELECT COUNT(*)
        FROM Solicitud
        INNER JOIN Vacante ON Solicitud.vacante = Vacante.numero
        WHERE Vacante.empresa = empresaId
          AND Solicitud.estatus = 'PEND'
          AND Solicitud.es_cancelada = FALSE
    );

    -- Mostrar los resultados
    SELECT vacantes_activas AS VacantesActivas,
           total_candidatos AS TotalCandidatos,
           aplicaciones_por_revisar AS AplicacionesPorRevisar;
END $$

DELIMITER ;

select * from solicitud

SELECT s.*, p.nombre, p.primerApellido, p.segundoApellido, v.titulo
                      FROM Solicitud s
                      JOIN Prospecto p ON s.prospecto = p.numero
                      JOIN Vacante v ON s.vacante = v.numero
                      WHERE v.empresa = 1
                      ORDER BY s.vacante DESC
                      LIMIT 5
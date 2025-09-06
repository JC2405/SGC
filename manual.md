# 🏥 Sistema de Gestión Clínica (SGC)

Este proyecto es una **API RESTful desarrollada en Laravel 12** para la gestión de una clínica u hospital.  
Permite administrar **usuarios, doctores, especialidades, EPS, cubículos y citas médicas**, con autenticación segura mediante **JWT**.

---

## 🚀 Funcionalidades principales

### 🔑 Autenticación (AuthController)
- Registro de usuarios.
- Inicio de sesión con credenciales.
- Generación y refresco de tokens JWT.
- Consulta de información del usuario autenticado.
- Cierre de sesión.

### 👤 Usuarios (UsuariosController)
- CRUD de pacientes/usuarios.
- Asociación con EPS.
- Validaciones de correo único y fecha de nacimiento.
- Consulta de usuarios con su EPS.

### 🏥 EPS (EpsController)
- CRUD de entidades prestadoras de salud.
- Activar/Inactivar EPS.
- Filtrar EPS activas o inactivas.
- Relación con usuarios afiliados.

### 👨‍⚕️ Doctores (DoctoresController)
- CRUD de doctores.
- Relación con su especialidad.
- Filtro por especialidad.
- Validación de disponibilidad y datos únicos.

### 📚 Especialidades (EspecialidadesController)
- CRUD de especialidades médicas.
- Validación de nombres únicos.
- Restricción: no se pueden eliminar especialidades con doctores asociados.

### 🏨 Cubículos (CubiculosController)
- CRUD de cubículos (consultas, procedimientos, emergencia).
- Estado: disponible, ocupado o en mantenimiento.
- Filtro por tipo y cubículos disponibles.
- Relación con citas y doctores.

### 📅 Citas Médicas (CitasController)
- CRUD de citas entre pacientes y doctores.
- Validación de disponibilidad del doctor.
- Cambio de estado (pendiente, confirmada, cancelada, atendida).
- Listado de citas por paciente o por doctor.
- Relación con cubículos, pacientes y especialidades.

---

## 🛠️ Tecnologías utilizadas
- **Backend:** Laravel 12, PHP 8.2
- **Autenticación:** JWT (tymon/jwt-auth)
- **Base de datos:** Eloquent ORM con migraciones
- **Estilos y build:** Vite + TailwindCSS
- **Testing:** PHPUnit
- **Colas y Jobs:** Laravel Queue
- **Frontend (opcional):** integración vía API REST

---

## 🛠️ Endpoint

## Autenticación

POST /api/register → Registrar usuario

POST /api/login → Iniciar sesión

POST /api/logout → Cerrar sesión

POST /api/refresh → Refrescar token

GET /api/me → Obtener usuario autenticado

## Usuarios

GET /api/usuarios → Listar usuarios

POST /api/usuarios → Crear usuario

GET /api/usuarios/{id} → Ver usuario

PUT /api/usuarios/{id} → Actualizar usuario

DELETE /api/usuarios/{id} → Eliminar usuario

## Doctores

GET /api/doctores → Listar doctores

POST /api/doctores → Crear doctor

GET /api/doctores/{id} → Ver doctor

PUT /api/doctores/{id} → Actualizar doctor

DELETE /api/doctores/{id} → Eliminar doctor

GET /api/doctores/especialidad/{id} → Listar doctores por especialidad

## Especialidades

GET /api/especialidades → Listar especialidades

POST /api/especialidades → Crear especialidad

GET /api/especialidades/{id} → Ver especialidad

PUT /api/especialidades/{id} → Actualizar especialidad

DELETE /api/especialidades/{id} → Eliminar especialidad

## EPS

GET /api/eps → Listar EPS activas

POST /api/eps → Crear EPS

GET /api/eps/{id} → Ver EPS

PUT /api/eps/{id} → Actualizar EPS

DELETE /api/eps/{id} → Eliminar EPS

PATCH /api/eps/{id}/estado → Cambiar estado

## Cubículos

GET /api/cubiculos → Listar cubículos

POST /api/cubiculos → Crear cubículo

GET /api/cubiculos/{id} → Ver cubículo

PUT /api/cubiculos/{id} → Actualizar cubículo

DELETE /api/cubiculos/{id} → Eliminar cubículo

GET /api/cubiculos/disponibles → Listar cubículos disponibles

GET /api/cubiculos/tipo/{tipo} → Listar cubículos por tipo

## Citas médicas

GET /api/citas → Listar citas

POST /api/citas → Crear cita

GET /api/citas/{id} → Ver cita

PUT /api/citas/{id} → Actualizar cita

DELETE /api/citas/{id} → Eliminar cita

GET /api/citas/paciente/{id} → Citas por paciente

GET /api/citas/doctor/{id} → Citas por doctor

PATCH /api/citas/{id}/estado → Cambiar estado de cita



## ⚙️ Instalación y uso

1. Clonar el repositorio:
   ```bash
   git clone <repo_url>
   cd jc2405-sgc

# Stack Tecnológico Completo: Node.js + React

## 🎯 Stack Principal (Core)

### Frontend
- **React** - Librería de UI
- **Node.js** - Runtime (para herramientas de desarrollo)
- **npm / yarn / pnpm** - Gestor de paquetes

### Backend
- **Node.js** - Runtime del servidor
- **Express** - Framework web minimalista
- **MySQL** - Base de datos (tu actual)

---

## 🔧 Tecnologías que Van de la Mano

### 1. **Gestión de Estado (Frontend)**

#### Opción A: Context API (Nativo React)
```javascript
// Para proyectos pequeños/medianos
import { createContext, useContext } from 'react';

const SorteosContext = createContext();
```
- ✅ **Sin dependencias extra**
- ✅ **Suficiente para la mayoría de casos**
- ⚠️ Puede volverse complejo en apps grandes

#### Opción B: Redux Toolkit (Recomendado para apps grandes)
```javascript
// Para proyectos grandes con estado complejo
import { configureStore } from '@reduxjs/toolkit';
```
- ✅ **Estado global predecible**
- ✅ **DevTools** excelentes
- ⚠️ Curva de aprendizaje

#### Opción C: Zustand (Ligero y moderno)
```javascript
// Alternativa moderna a Redux
import create from 'zustand';
```
- ✅ **Muy ligero** (1KB)
- ✅ **Fácil de usar**
- ✅ **Buena alternativa** a Redux

**Para tu proyecto:** Context API es suficiente inicialmente.

---

### 2. **Rutas (Frontend)**

#### React Router
```javascript
import { BrowserRouter, Routes, Route } from 'react-router-dom';

<BrowserRouter>
  <Routes>
    <Route path="/sorteos" element={<ListadoSorteos />} />
    <Route path="/sorteos/:id" element={<SorteoDetalles />} />
  </Routes>
</BrowserRouter>
```
- ✅ **Estándar de la industria**
- ✅ **Fácil de usar**
- ✅ **Soporte para rutas protegidas**

**Para tu proyecto:** React Router es esencial.

---

### 3. **HTTP Client (Comunicación Frontend-Backend)**

#### Axios (Recomendado)
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost/api',
  headers: { 'Content-Type': 'application/json' }
});

// Interceptores para autenticación
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```
- ✅ **Interceptores** (para tokens, errores)
- ✅ **Cancelación de requests**
- ✅ **Transformación automática** de datos

#### Fetch API (Nativo, pero más verboso)
```javascript
// Nativo del navegador, pero requiere más código
fetch('/api/sorteos', {
  headers: { 'Authorization': `Bearer ${token}` }
})
```
- ✅ **Sin dependencias**
- ⚠️ Más código manual

**Para tu proyecto:** Axios es más cómodo.

---

### 4. **ORM / Base de Datos (Backend)**

#### Opción A: Prisma (Recomendado - Moderno)
```javascript
// schema.prisma
model Sorteo {
  id_sorteo    Int      @id @default(autoincrement())
  titulo       String
  estado       String
  boletos      Boleto[]
}

// Uso
const sorteos = await prisma.sorteo.findMany({
  where: { estado: 'Activo' },
  include: { boletos: true }
});
```
- ✅ **Type-safe** (TypeScript)
- ✅ **Migraciones automáticas**
- ✅ **Excelente DX** (Developer Experience)
- ✅ **Prisma Studio** (GUI para DB)

#### Opción B: Sequelize (Tradicional)
```javascript
const Sorteo = sequelize.define('Sorteo', {
  id_sorteo: { type: DataTypes.INTEGER, primaryKey: true },
  titulo: DataTypes.STRING,
  estado: DataTypes.STRING
});

const sorteos = await Sorteo.findAll({
  where: { estado: 'Activo' }
});
```
- ✅ **Maduro y estable**
- ✅ **Mucha documentación**
- ⚠️ Más verboso que Prisma

#### Opción C: TypeORM (Si usas TypeScript)
```typescript
@Entity('sorteos')
export class Sorteo {
  @PrimaryGeneratedColumn()
  id_sorteo: number;
  
  @Column()
  titulo: string;
}
```
- ✅ **TypeScript nativo**
- ✅ **Decoradores** elegantes
- ⚠️ Curva de aprendizaje

**Para tu proyecto:** Prisma es la mejor opción (moderno y fácil).

---

### 5. **Validación (Backend)**

#### Joi (Recomendado)
```javascript
const Joi = require('joi');

const schema = Joi.object({
  titulo: Joi.string().min(3).max(200).required(),
  precio_boleto: Joi.number().positive().required(),
  total_boletos: Joi.number().integer().min(1).max(10000).required()
});

const { error, value } = schema.validate(req.body);
if (error) {
  return res.status(400).json({ error: error.details[0].message });
}
```
- ✅ **Muy popular**
- ✅ **Fácil de usar**
- ✅ **Mensajes de error claros**

#### Zod (Alternativa moderna, TypeScript-friendly)
```typescript
import { z } from 'zod';

const schema = z.object({
  titulo: z.string().min(3).max(200),
  precio_boleto: z.number().positive(),
  total_boletos: z.number().int().min(1).max(10000)
});
```
- ✅ **TypeScript-first**
- ✅ **Inferencia de tipos automática**

**Para tu proyecto:** Joi es suficiente y más simple.

---

### 6. **Autenticación (Backend)**

#### JWT (jsonwebtoken)
```javascript
const jwt = require('jsonwebtoken');

// Generar token
const token = jwt.sign(
  { userId: user.id, email: user.email },
  process.env.JWT_SECRET,
  { expiresIn: '24h' }
);

// Verificar token (middleware)
const authenticate = (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;
    next();
  } catch (error) {
    return res.status(401).json({ error: 'Token inválido' });
  }
};
```
- ✅ **Estándar de la industria**
- ✅ **Stateless** (no requiere sesiones en DB)
- ✅ **Escalable**

#### Passport.js (Si necesitas múltiples estrategias)
```javascript
const passport = require('passport');
const JwtStrategy = require('passport-jwt').Strategy;

passport.use(new JwtStrategy({
  jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
  secretOrKey: process.env.JWT_SECRET
}, (payload, done) => {
  // Verificar usuario...
}));
```
- ✅ **Múltiples estrategias** (JWT, OAuth, Local)
- ⚠️ Más configuración

**Para tu proyecto:** JWT directo es suficiente.

---

### 7. **Subida de Archivos (Backend)**

#### Multer
```javascript
const multer = require('multer');
const path = require('path');

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/comprobantes/');
  },
  filename: (req, file, cb) => {
    const uniqueName = `${Date.now()}-${Math.round(Math.random() * 1E9)}${path.extname(file.originalname)}`;
    cb(null, uniqueName);
  }
});

const upload = multer({
  storage: storage,
  limits: { fileSize: 2 * 1024 * 1024 }, // 2MB
  fileFilter: (req, file, cb) => {
    const allowedTypes = /jpeg|jpg|png|pdf/;
    const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = allowedTypes.test(file.mimetype);
    
    if (mimetype && extname) {
      return cb(null, true);
    }
    cb(new Error('Tipo de archivo no permitido'));
  }
});

app.post('/api/upload', upload.single('comprobante'), (req, res) => {
  // req.file contiene la información del archivo
});
```
- ✅ **Estándar para Node.js**
- ✅ **Flexible y configurable**

**Para tu proyecto:** Multer es perfecto.

---

### 8. **WebSockets (Tiempo Real)**

#### Socket.io
```javascript
// Backend
const io = require('socket.io')(server);

io.on('connection', (socket) => {
  socket.on('reserve-ticket', async (data) => {
    // Reservar boleto
    await reserveTicket(data.ticketId, data.userId);
    
    // Notificar a todos
    io.emit('ticket-reserved', {
      ticketId: data.ticketId,
      userId: data.userId
    });
  });
});

// Frontend
import io from 'socket.io-client';

const socket = io('http://localhost:3000');

socket.on('ticket-reserved', (data) => {
  // Actualizar UI en tiempo real
  updateTicketStatus(data.ticketId, 'reserved');
});
```
- ✅ **Bidirectional** (cliente ↔ servidor)
- ✅ **Reconexión automática**
- ✅ **Rooms y namespaces** para organizar

**Para tu proyecto:** Socket.io es esencial para reservas en tiempo real.

---

### 9. **Variables de Entorno**

#### dotenv
```javascript
// .env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=sorteo_schema
JWT_SECRET=tu_secreto_super_seguro
PORT=3000

// app.js
require('dotenv').config();

const dbConfig = {
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME
};
```
- ✅ **Seguridad** (no hardcodear credenciales)
- ✅ **Configuración por ambiente** (dev, prod)

**Para tu proyecto:** dotenv es esencial.

---

### 10. **Build Tools (Frontend)**

#### Vite (Recomendado - Moderno)
```json
// package.json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  }
}
```
- ✅ **Súper rápido** (HMR instantáneo)
- ✅ **Configuración mínima**
- ✅ **Mejor que Create React App**

#### Create React App (Tradicional)
```bash
npx create-react-app mi-app
```
- ✅ **Fácil de empezar**
- ⚠️ Más lento que Vite
- ⚠️ Configuración oculta

**Para tu proyecto:** Vite es mejor opción.

---

### 11. **CSS / Estilos (Frontend)**

#### Opción A: Tailwind CSS (Recomendado - Ya lo usas)
```jsx
<div className="bg-card-dark p-4 rounded-lg">
  <h2 className="text-white text-xl font-bold">Sorteo</h2>
</div>
```
- ✅ **Ya lo conoces**
- ✅ **Utility-first** (rápido de desarrollar)
- ✅ **PurgeCSS** automático (bundle pequeño)

#### Opción B: Styled Components
```jsx
const Card = styled.div`
  background: #1a1d24;
  padding: 1rem;
  border-radius: 0.5rem;
`;
```
- ✅ **CSS-in-JS**
- ✅ **Temático** fácil
- ⚠️ Bundle más grande

#### Opción C: CSS Modules
```css
/* Card.module.css */
.card {
  background: #1a1d24;
  padding: 1rem;
}
```
```jsx
import styles from './Card.module.css';
<div className={styles.card}>...</div>
```
- ✅ **Scoped CSS**
- ✅ **Sin dependencias**

**Para tu proyecto:** Mantén Tailwind CSS (ya lo usas).

---

### 12. **Formularios (Frontend)**

#### React Hook Form (Recomendado)
```jsx
import { useForm } from 'react-hook-form';

function MyForm() {
  const { register, handleSubmit, formState: { errors } } = useForm();
  
  const onSubmit = (data) => {
    console.log(data);
  };
  
  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <input {...register('titulo', { required: 'Título es requerido' })} />
      {errors.titulo && <span>{errors.titulo.message}</span>}
    </form>
  );
}
```
- ✅ **Muy performante** (menos re-renders)
- ✅ **Validación integrada**
- ✅ **Fácil de usar**

#### Formik (Alternativa)
```jsx
import { Formik, Form, Field } from 'formik';
```
- ✅ **Popular**
- ⚠️ Más verboso que React Hook Form

**Para tu proyecto:** React Hook Form es mejor.

---

### 13. **Testing**

#### Jest + React Testing Library
```javascript
// __tests__/Sorteo.test.js
import { render, screen } from '@testing-library/react';
import SorteoCard from './SorteoCard';

test('muestra el título del sorteo', () => {
  render(<SorteoCard titulo="iPhone 15" />);
  expect(screen.getByText('iPhone 15')).toBeInTheDocument();
});
```
- ✅ **Estándar de la industria**
- ✅ **Incluido en Create React App**

#### Supertest (Backend)
```javascript
const request = require('supertest');
const app = require('./app');

test('GET /api/sorteos', async () => {
  const response = await request(app)
    .get('/api/sorteos')
    .expect(200);
  
  expect(response.body.success).toBe(true);
});
```
- ✅ **Testing de APIs**
- ✅ **Fácil de usar**

**Para tu proyecto:** Jest + React Testing Library + Supertest.

---

### 14. **Linting y Formateo**

#### ESLint
```json
// .eslintrc.json
{
  "extends": ["react-app", "plugin:react/recommended"],
  "rules": {
    "no-console": "warn"
  }
}
```
- ✅ **Detecta errores**
- ✅ **Mantiene código consistente**

#### Prettier
```json
// .prettierrc
{
  "semi": true,
  "singleQuote": true,
  "tabWidth": 2
}
```
- ✅ **Formatea código automáticamente**
- ✅ **Integración con ESLint**

**Para tu proyecto:** ESLint + Prettier es estándar.

---

### 15. **TypeScript (Opcional pero Recomendado)**

```typescript
// types.ts
export interface Sorteo {
  id_sorteo: number;
  titulo: string;
  estado: 'Activo' | 'Finalizado' | 'Cancelado';
  precio_boleto: number;
  total_boletos: number;
}

// Component.tsx
function SorteoCard({ sorteo }: { sorteo: Sorteo }) {
  return <div>{sorteo.titulo}</div>;
}
```
- ✅ **Type safety**
- ✅ **Autocompletado mejorado**
- ✅ **Menos bugs**
- ⚠️ Curva de aprendizaje

**Para tu proyecto:** Opcional, pero recomendado a largo plazo.

---

## 📦 Stack Completo Recomendado para Tu Proyecto

### Frontend
```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.8.0",
    "axios": "^1.3.0",
    "socket.io-client": "^4.6.0",
    "react-hook-form": "^7.43.0",
    "zustand": "^4.3.0" // o Context API nativo
  },
  "devDependencies": {
    "vite": "^4.1.0",
    "@vitejs/plugin-react": "^3.1.0",
    "tailwindcss": "^3.2.0",
    "eslint": "^8.35.0",
    "prettier": "^2.8.0"
  }
}
```

### Backend
```json
{
  "dependencies": {
    "express": "^4.18.0",
    "mysql2": "^3.1.0",
    "prisma": "^4.11.0",
    "@prisma/client": "^4.11.0",
    "jsonwebtoken": "^9.0.0",
    "bcrypt": "^5.1.0",
    "joi": "^17.9.0",
    "multer": "^1.4.5",
    "socket.io": "^4.6.0",
    "dotenv": "^16.0.0",
    "cors": "^2.8.5"
  },
  "devDependencies": {
    "nodemon": "^2.0.20",
    "supertest": "^6.3.0"
  }
}
```

---

## 🏗️ Arquitectura Recomendada

```
proyecto/
├── frontend/
│   ├── src/
│   │   ├── components/      # Componentes reutilizables
│   │   ├── pages/           # Páginas (ListadoSorteos, etc.)
│   │   ├── hooks/           # Custom hooks
│   │   ├── services/        # API calls (axios)
│   │   ├── context/         # Context API o Zustand
│   │   ├── utils/           # Utilidades
│   │   └── App.jsx
│   ├── public/
│   └── package.json
│
├── backend/
│   ├── src/
│   │   ├── routes/          # api_sorteos.js, api_boletos.js
│   │   ├── controllers/     # Lógica de negocio
│   │   ├── models/          # Prisma models
│   │   ├── middleware/      # auth.js, validation.js
│   │   ├── utils/           # Helpers
│   │   └── server.js
│   ├── prisma/
│   │   └── schema.prisma
│   └── package.json
│
└── shared/                   # Código compartido (opcional)
    └── types/                # TypeScript types
```

---

## 🚀 Comandos Iniciales

### Frontend
```bash
# Crear proyecto
npm create vite@latest frontend -- --template react

# Instalar dependencias
cd frontend
npm install react-router-dom axios socket.io-client react-hook-form
npm install -D tailwindcss postcss autoprefixer

# Iniciar desarrollo
npm run dev
```

### Backend
```bash
# Inicializar proyecto
mkdir backend && cd backend
npm init -y

# Instalar dependencias
npm install express mysql2 prisma @prisma/client jsonwebtoken bcrypt joi multer socket.io dotenv cors
npm install -D nodemon

# Inicializar Prisma
npx prisma init

# Iniciar desarrollo
npm run dev  # con nodemon
```

---

## 📚 Recursos de Aprendizaje

1. **React Router**: https://reactrouter.com/
2. **Axios**: https://axios-http.com/
3. **Prisma**: https://www.prisma.io/docs
4. **Socket.io**: https://socket.io/docs/v4/
5. **React Hook Form**: https://react-hook-form.com/
6. **Vite**: https://vitejs.dev/

---

## ✅ Resumen

**Stack mínimo esencial:**
- React + React Router + Axios (Frontend)
- Node.js + Express + Prisma + Socket.io (Backend)
- MySQL (Base de datos)
- Tailwind CSS (Estilos - ya lo usas)

**Stack completo recomendado:**
- Todo lo anterior +
- React Hook Form (Formularios)
- Zustand o Context API (Estado)
- JWT (Autenticación)
- Multer (Subida archivos)
- Joi (Validación)
- dotenv (Variables de entorno)
- ESLint + Prettier (Calidad de código)

Este stack te dará una base sólida y escalable para tu proyecto de sorteos.

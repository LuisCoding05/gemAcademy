import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Layout } from './components/Layout'
import { Logs } from './components/admin/Logs'
import UserManagement from './components/admin/UserManagement'
import { MainContent } from './components/index/MainContent'
import { Aside } from './components/index/Aside'
import { Navbar } from './components/Navbar'
import { Copy } from './components/Copy'
import { ThemeProvider } from './context/ThemeContext';
import MainRegister from './components/register/MainRegister'
import MainLogin from './components/register/MainLogin'
import VerificationResetPassword from './components/register/VerificationResetPassword'
import Dashboard from './components/dashboard/Dashboard'
import ProtectedRoute from './components/auth/ProtectedRoute'
import { AuthProvider } from './context/AuthContext'
import { SearchCourse } from './components/course/SearchCourse'
import { CourseDetail } from './components/course/CourseDetail'
import { CreateCourse } from './components/course/CreateCourse'
import ItemDetails from './components/course/ItemDetails'
import EntregaDetalle from './components/item/EntregaDetalle'
import { EditCourse } from './components/course/EditCourse'
import NotificationsPage from './components/notifications/NotificationsPage'
import UserProfile from './components/profile/UserProfile'

function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<Layout />}>
              <Route index element={
                <div className="container">
                  <div className="row">
                    <MainContent />
                    <Aside />
                  </div>
                </div>
              } />
            </Route>
            
            <Route path="/logs" element={
              <div className="wrapper">
                <ProtectedRoute requiredRole="ROLE_ADMIN">
                  <Navbar />
                  <Logs />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />
            <Route path="/register" element={
              <div className="wrapper">
                <MainRegister />
              </div>
            } />
            <Route path="/login" element={
              <div className="wrapper">
                <MainLogin />
                <Copy />
              </div>
            } />
            <Route path="/verify" element={
              <div className="wrapper">
                <VerificationResetPassword />
                <Copy />
              </div>
            } />

            {/* Ruta protegida */}
            <Route path="/dashboard" element={
              <div className="wrapper">
                <ProtectedRoute>
                  <Navbar />
                  <Dashboard />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            <Route path="/cursos" element={
              <div className="wrapper">
                  <Navbar />
                  <SearchCourse />
                  <Copy />
              </div>
            } />

            <Route path="/cursos/crear" element={
              <div className="wrapper">
                <ProtectedRoute>
                  <Navbar />
                  <CreateCourse />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            <Route path="/cursos/:id" element={
              <div className="wrapper">
                  <Navbar />
                  <CourseDetail />
                  <Copy />
              </div>
            } />

            {/* Ruta para ver detalles de materiales, tareas y quizzes */}
            <Route path="/cursos/:courseId/:itemType/:itemId" element={
              <div className="wrapper">
                <ProtectedRoute>
                  <Navbar />
                  <ItemDetails />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            <Route path="/cursos/:courseId/tarea/:tareaId/entrega/:entregaId" element={
              <div className="wrapper">
                <ProtectedRoute>
                  <Navbar />
                  <EntregaDetalle />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            <Route path="/notificaciones" element={
              <div className="wrapper">
                <ProtectedRoute>
                  <Navbar />
                  <NotificationsPage />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            <Route path="/cursos/:id/edit" element={
              <div className="wrapper">
                <ProtectedRoute>
                  <Navbar />
                  <EditCourse />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            <Route path="/admin/users" element={
              <div className="wrapper">
                <ProtectedRoute requiredRole="ROLE_ADMIN">
                  <Navbar />
                  <UserManagement />
                  <Copy />
                </ProtectedRoute>
              </div>
            } />

            {/* Ruta para ver perfil público de usuario */}
            <Route path="/profile/:id" element={
              <div className="wrapper">
                <Navbar />
                <UserProfile />
                <Copy />
              </div>
            } />

          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </ThemeProvider>
  )
}

export default App